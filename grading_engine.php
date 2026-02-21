<?php
/**
 * Grading Engine - Collects student code and sends to GPT for grading
 */
require_once 'config.php';

class GradingEngine {

    /**
     * Scan submission folder and find all student directories
     */
    public static function scanSubmissions($folderPath) {
        $students = [];
        if (!is_dir($folderPath)) return $students;

        $items = scandir($folderPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $folderPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath) && strpos($item, 'assignsubmission_file') !== false) {
                $name = self::extractStudentName($item);
                $students[] = [
                    'name' => $name,
                    'folder_name' => $item,
                    'folder_path' => $fullPath
                ];
            }
        }
        usort($students, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        return $students;
    }

    /**
     * Extract student name from folder name
     */
    private static function extractStudentName($folderName) {
        $parts = explode('_', $folderName);
        $nameParts = [];
        foreach ($parts as $part) {
            $trimmed = trim($part);
            if (is_numeric($trimmed)) break;
            $nameParts[] = $trimmed;
        }
        $name = implode(' ', $nameParts);
        $name = str_replace(['assignsubmission', 'file'], '', $name);
        return trim($name);
    }

    /**
     * Find the project root (folder containing app.py)
     */
    public static function findProjectRoot($path) {
        if (file_exists($path . '/app.py')) return $path;

        $items = @scandir($path);
        if (!$items) return null;

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || strtolower($item) === 'venv' || $item === '__pycache__') continue;
            $sub = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($sub)) {
                if (file_exists($sub . '/app.py')) return $sub;
                // Go one more level
                $items2 = @scandir($sub);
                if ($items2) {
                    foreach ($items2 as $item2) {
                        if ($item2 === '.' || $item2 === '..' || strtolower($item2) === 'venv' || $item2 === '__pycache__') continue;
                        $sub2 = $sub . DIRECTORY_SEPARATOR . $item2;
                        if (is_dir($sub2) && file_exists($sub2 . '/app.py')) return $sub2;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Check if venv folder exists anywhere in submission
     */
    public static function checkVenv($submissionPath) {
        $result = ['has_venv' => false, 'location' => ''];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($submissionPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $dirName = strtolower($file->getFilename());
                if (in_array($dirName, ['venv', '.venv', 'env', '.env'])) {
                    $fullPath = $file->getPathname();
                    if (file_exists($fullPath . '/pyvenv.cfg') ||
                        is_dir($fullPath . '/Scripts') ||
                        is_dir($fullPath . '/Lib')) {
                        $result['has_venv'] = true;
                        // Get path relative to submission folder
                        $result['location'] = str_replace($submissionPath . DIRECTORY_SEPARATOR, '', $fullPath);
                        return $result;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Collect all relevant code files from a student's submission
     */
    public static function collectCode($root, $submissionPath) {
        $code = [];

        // app.py
        $appPath = $root . '/app.py';
        $code['app.py'] = file_exists($appPath) ? self::safeRead($appPath) : '[MISSING]';

        // text_tools.py (in utils/ or flat)
        $ttPath = $root . '/utils/text_tools.py';
        if (!file_exists($ttPath)) $ttPath = $root . '/text_tools.py';
        $code['text_tools.py'] = file_exists($ttPath) ? self::safeRead($ttPath) : '[MISSING]';

        // validators.py (in utils/ or flat)
        $valPath = $root . '/utils/validators.py';
        if (!file_exists($valPath)) $valPath = $root . '/validators.py';
        $code['validators.py'] = file_exists($valPath) ? self::safeRead($valPath) : '[MISSING]';

        // __init__.py
        $initPath = $root . '/utils/__init__.py';
        $code['__init__.py'] = file_exists($initPath) ? self::safeRead($initPath) : '[MISSING]';

        // README.md
        foreach (['README.md', 'README.md.txt'] as $fn) {
            $fp = $root . '/' . $fn;
            if (file_exists($fp)) {
                $code['README.md'] = self::safeRead($fp);
                break;
            }
        }
        if (!isset($code['README.md'])) $code['README.md'] = '[MISSING]';

        // results.txt
        $resPath = $root . '/outputs/results.txt';
        $code['results.txt'] = file_exists($resPath) ? self::safeRead($resPath) : '[MISSING]';

        // Structure info
        $structure = [];
        $structure['has_utils_dir'] = is_dir($root . '/utils');
        $structure['has_outputs_dir'] = is_dir($root . '/outputs');
        $structure['has_init'] = file_exists($root . '/utils/__init__.py');
        $structure['project_folder_name'] = basename($root);

        $venv = self::checkVenv($submissionPath);
        $structure['has_venv'] = $venv['has_venv'];
        $structure['venv_location'] = $venv['location'];

        $code['_structure'] = json_encode($structure);

        return $code;
    }

    private static function safeRead($path) {
        $content = @file_get_contents($path);
        if ($content === false) return '[UNREADABLE]';
        // Limit to 5000 chars per file to stay within token limits
        if (strlen($content) > 5000) {
            $content = substr($content, 0, 5000) . "\n... [TRUNCATED - file too long]";
        }
        return $content;
    }

    /**
     * Build the GPT prompt for grading a student
     */
    public static function buildGradingPrompt($studentName, $codeFiles, $examInstructions = '') {
        $prompt = "You are a strict but fair programming instructor grading a Python Practical Exam submission.\n\n";

        if ($examInstructions) {
            $prompt .= "=== EXAM INSTRUCTIONS ===\n{$examInstructions}\n\n";
        } else {
            $prompt .= "=== EXAM: Text Processing Pipeline ===\n";
            $prompt .= "Part A - Virtual Environment Setup (15 pts): Create venv, install packages. DEDUCT 5 pts if venv folder is included in submission.\n";
            $prompt .= "Part B - External Package & README (15 pts): Install external package (rich/colorama/slugify etc), README.md with package name, install command, rationale (5pts each).\n";
            $prompt .= "Part C - String Utilities Module (30 pts): utils/text_tools.py with 4 functions (7.5 pts each):\n";
            $prompt .= "  - clean_text(text): strip, collapse whitespace, remove punctuation\n";
            $prompt .= "  - word_stats(text): return dict with char_count, char_no_spaces, word_count, sentence_count, longest_word\n";
            $prompt .= "  - mask_sensitive(text): mask emails/phones with ***\n";
            $prompt .= "  - make_slug(text): lowercase, replace spaces with hyphens, remove special chars, collapse hyphens\n";
            $prompt .= "Part D - Input Validators Module (10 pts): utils/validators.py with 2 functions (5pts each):\n";
            $prompt .= "  - require_non_empty(prompt_msg): loop until non-empty input\n";
            $prompt .= "  - require_menu_choice(prompt_msg, choices): loop until valid choice\n";
            $prompt .= "Part E - Main Application (30 pts): app.py with looping menu (8pts), process inputs via modules (8pts), log to results.txt with timestamps (7pts), integrate external package (7pts)\n";
            $prompt .= "Bonus - File Processing (+10 pts): Option 7 to load text from file and process it\n\n";
        }

        $prompt .= "=== STUDENT: {$studentName} ===\n\n";

        $structure = json_decode($codeFiles['_structure'], true);
        $prompt .= "PROJECT STRUCTURE:\n";
        $prompt .= "- Project folder: " . ($structure['project_folder_name'] ?? 'unknown') . "\n";
        $prompt .= "- Has utils/ directory: " . ($structure['has_utils_dir'] ? 'YES' : 'NO') . "\n";
        $prompt .= "- Has __init__.py: " . ($structure['has_init'] ? 'YES' : 'NO') . "\n";
        $prompt .= "- Has outputs/ directory: " . ($structure['has_outputs_dir'] ? 'YES' : 'NO') . "\n";
        $prompt .= "- venv included in submission: " . ($structure['has_venv'] ? "YES at '{$structure['venv_location']}'" : 'NO') . "\n\n";

        foreach ($codeFiles as $filename => $content) {
            if ($filename === '_structure') continue;
            $prompt .= "=== FILE: {$filename} ===\n";
            $prompt .= $content . "\n\n";
        }

        $prompt .= "=== GRADING INSTRUCTIONS ===\n";
        $prompt .= "Grade this student's submission. You MUST respond in EXACTLY this JSON format (no markdown, no code fences):\n";
        $prompt .= "{\n";
        $prompt .= '  "part_a": <0-15 number>,'. "\n";
        $prompt .= '  "part_a_feedback": "<brief feedback for Part A>",'. "\n";
        $prompt .= '  "part_b": <0-15 number>,'. "\n";
        $prompt .= '  "part_b_feedback": "<brief feedback for Part B>",'. "\n";
        $prompt .= '  "part_c": <0-30 number>,'. "\n";
        $prompt .= '  "part_c_feedback": "<brief feedback for Part C>",'. "\n";
        $prompt .= '  "part_d": <0-10 number>,'. "\n";
        $prompt .= '  "part_d_feedback": "<brief feedback for Part D>",'. "\n";
        $prompt .= '  "part_e": <0-30 number>,'. "\n";
        $prompt .= '  "part_e_feedback": "<brief feedback for Part E>",'. "\n";
        $prompt .= '  "bonus": <0-10 number>,'. "\n";
        $prompt .= '  "bonus_feedback": "<brief feedback for bonus>",'. "\n";
        $prompt .= '  "overall_feedback": "<2-3 sentence overall assessment>"'. "\n";
        $prompt .= "}\n\n";
        $prompt .= "IMPORTANT RULES:\n";
        $prompt .= "- If venv folder is included, deduct 5 points from Part A\n";
        $prompt .= "- If text_tools.py or validators.py are NOT inside utils/ folder, apply small deductions\n";
        $prompt .= "- Grade based on code quality, correctness, and completeness\n";
        $prompt .= "- Be fair but strict. Only give full marks if implementation is correct\n";
        $prompt .= "- The total (parts A-E) should not exceed 100. Bonus is separate (+10 max)\n";
        $prompt .= "- Respond with ONLY the JSON object, nothing else\n";

        return $prompt;
    }

    /**
     * Call OpenAI API
     */
    public static function callGPT($prompt) {
        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => OPENAI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert programming instructor. You grade Python exam submissions fairly and strictly. Always respond with valid JSON only.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.3,
            'max_completion_tokens' => 1500
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENAI_API_KEY
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "cURL Error: {$error}"];
        }

        if ($httpCode !== 200) {
            $decoded = json_decode($response, true);
            $errMsg = $decoded['error']['message'] ?? "HTTP {$httpCode}";
            return ['success' => false, 'error' => "API Error: {$errMsg}"];
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? '';

        // Clean markdown fences if present
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);

        $grades = json_decode($content, true);
        if (!$grades) {
            return ['success' => false, 'error' => "Failed to parse GPT response: {$content}"];
        }

        return ['success' => true, 'grades' => $grades, 'raw' => $content];
    }

    /**
     * Grade a single student submission
     */
    public static function gradeStudent($sessionId, $submissionId) {
        $db = getDB();

        // Get submission info
        $stmt = $db->prepare("SELECT * FROM student_submissions WHERE id = ?");
        $stmt->execute([$submissionId]);
        $submission = $stmt->fetch();
        if (!$submission) return ['success' => false, 'error' => 'Submission not found'];

        // Get session info
        $stmt = $db->prepare("SELECT * FROM grading_sessions WHERE id = ?");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();

        // Update status
        $db->prepare("UPDATE student_submissions SET status = 'grading' WHERE id = ?")->execute([$submissionId]);

        // Find project root
        $root = self::findProjectRoot($submission['folder_path']);
        if (!$root) {
            $db->prepare("UPDATE student_submissions SET status='error', error_message='Could not find app.py in submission' WHERE id = ?")
               ->execute([$submissionId]);
            return ['success' => false, 'error' => 'No app.py found'];
        }

        // Collect code
        $codeFiles = self::collectCode($root, $submission['folder_path']);

        // Build prompt & call GPT
        $prompt = self::buildGradingPrompt(
            $submission['student_name'],
            $codeFiles,
            $session['exam_instructions'] ?? ''
        );

        $result = self::callGPT($prompt);

        if (!$result['success']) {
            $db->prepare("UPDATE student_submissions SET status='error', error_message=? WHERE id = ?")
               ->execute([$result['error'], $submissionId]);
            return $result;
        }

        $g = $result['grades'];
        $partA = floatval($g['part_a'] ?? 0);
        $partB = floatval($g['part_b'] ?? 0);
        $partC = floatval($g['part_c'] ?? 0);
        $partD = floatval($g['part_d'] ?? 0);
        $partE = floatval($g['part_e'] ?? 0);
        $bonus = floatval($g['bonus'] ?? 0);
        $total = $partA + $partB + $partC + $partD + $partE;
        $total = min($total, 100);
        $final = min($total + $bonus, 110);
        $pct = $total;

        if ($total >= 96) $remarks = 'Outstanding';
        elseif ($total >= 90) $remarks = 'Excellent';
        elseif ($total >= 80) $remarks = 'Very Good';
        elseif ($total >= 75) $remarks = 'Passed';
        elseif ($total >= 60) $remarks = 'Needs Improvement';
        elseif ($total > 0) $remarks = 'Failed';
        else $remarks = 'No Submission';

        // Build feedback text
        $feedback = "Part A ({$partA}/15): " . ($g['part_a_feedback'] ?? '') . "\n";
        $feedback .= "Part B ({$partB}/15): " . ($g['part_b_feedback'] ?? '') . "\n";
        $feedback .= "Part C ({$partC}/30): " . ($g['part_c_feedback'] ?? '') . "\n";
        $feedback .= "Part D ({$partD}/10): " . ($g['part_d_feedback'] ?? '') . "\n";
        $feedback .= "Part E ({$partE}/30): " . ($g['part_e_feedback'] ?? '') . "\n";
        if ($bonus > 0) $feedback .= "Bonus ({$bonus}/10): " . ($g['bonus_feedback'] ?? '') . "\n";
        $feedback .= "\nOverall: " . ($g['overall_feedback'] ?? '');

        $structure = json_decode($codeFiles['_structure'], true);
        $hasVenv = $structure['has_venv'] ? 1 : 0;
        $venvLoc = $structure['venv_location'] ?? '';

        $stmt = $db->prepare("UPDATE student_submissions SET 
            part_a=?, part_b=?, part_c=?, part_d=?, part_e=?, bonus=?,
            total_score=?, final_score=?, percentage=?, remarks=?,
            has_venv=?, venv_location=?, feedback=?, 
            code_files=?, gpt_response=?,
            status='graded', graded_at=NOW()
            WHERE id = ?");
        $stmt->execute([
            $partA, $partB, $partC, $partD, $partE, $bonus,
            $total, $final, $pct, $remarks,
            $hasVenv, $venvLoc, $feedback,
            json_encode($codeFiles), $result['raw'],
            $submissionId
        ]);

        // Update session graded count
        $db->prepare("UPDATE grading_sessions SET graded_count = (
            SELECT COUNT(*) FROM student_submissions WHERE session_id = ? AND status = 'graded'
        ) WHERE id = ?")->execute([$sessionId, $sessionId]);

        return ['success' => true, 'grades' => $g, 'total' => $total];
    }
}
