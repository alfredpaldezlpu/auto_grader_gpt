<?php
require_once 'config.php';
require_once 'grading_engine.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'scan_folder':
        $folderPath = $_POST['folder_path'] ?? '';
        $folderPath = str_replace('/', DIRECTORY_SEPARATOR, $folderPath);
        $folderPath = rtrim($folderPath, DIRECTORY_SEPARATOR);

        if (!is_dir($folderPath)) {
            echo json_encode(['success' => false, 'error' => 'Folder not found: ' . $folderPath]);
            exit;
        }

        $students = GradingEngine::scanSubmissions($folderPath);
        if (empty($students)) {
            echo json_encode(['success' => false, 'error' => 'No student submission folders found. Make sure the folder contains directories with "assignsubmission_file" in the name.']);
            exit;
        }

        echo json_encode(['success' => true, 'students' => $students, 'count' => count($students)]);
        break;

    case 'upload_pdf':
        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'No PDF file uploaded or upload error.']);
            exit;
        }

        $file = $_FILES['pdf_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed.']);
            exit;
        }

        // Save to uploads/
        $savedName = 'exam_instructions_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $savedPath = UPLOAD_DIR . $savedName;
        if (!move_uploaded_file($file['tmp_name'], $savedPath)) {
            echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
            exit;
        }

        // Extract text using Python + PyMuPDF
        $pythonScript = __DIR__ . DIRECTORY_SEPARATOR . 'extract_pdf.py';
        $cmd = 'python ' . escapeshellarg($pythonScript) . ' ' . escapeshellarg($savedPath) . ' 2>&1';
        $output = shell_exec($cmd);

        if ($output === null || trim($output) === '') {
            echo json_encode(['success' => false, 'error' => 'Failed to extract text from PDF. Make sure Python and PyMuPDF are installed.']);
            exit;
        }

        $text = trim($output);

        // Check if extraction had an error
        if (str_starts_with($text, 'ERROR:')) {
            echo json_encode(['success' => false, 'error' => 'PDF extraction failed: ' . $text]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'text' => $text,
            'filename' => $file['name'],
            'saved_path' => $savedPath
        ]);
        break;

    case 'parse_criteria':
        $examText = $_POST['exam_text'] ?? '';
        if (empty(trim($examText))) {
            echo json_encode(['success' => false, 'error' => 'No exam text provided.']);
            exit;
        }

        // Use GPT to extract rubric criteria from the exam instructions
        $criteriaPrompt = "You are analyzing exam instructions to extract the grading rubric/criteria.\n\n";
        $criteriaPrompt .= "=== EXAM INSTRUCTIONS ===\n{$examText}\n\n";
        $criteriaPrompt .= "=== TASK ===\n";
        $criteriaPrompt .= "Extract ALL graded sections/parts from these exam instructions. For each section, identify:\n";
        $criteriaPrompt .= "1. A short unique key (e.g., 'part_a', 'task_1', 'section_setup', 'bonus')\n";
        $criteriaPrompt .= "2. The label/title (e.g., 'Part A - Virtual Environment Setup')\n";
        $criteriaPrompt .= "3. Maximum points possible\n";
        $criteriaPrompt .= "4. Whether it is a bonus section (does not count toward the base total)\n";
        $criteriaPrompt .= "5. A brief description of what is being graded\n\n";
        $criteriaPrompt .= "Respond with ONLY a JSON array (no markdown fences). Each element:\n";
        $criteriaPrompt .= '{"key": "snake_case_key", "label": "Display Label", "max_points": number, "is_bonus": true/false, "description": "brief description"}' . "\n\n";
        $criteriaPrompt .= "RULES:\n";
        $criteriaPrompt .= "- Include ALL graded parts, including bonus sections\n";
        $criteriaPrompt .= "- Keys must be unique snake_case identifiers\n";
        $criteriaPrompt .= "- Order them as they appear in the exam\n";
        $criteriaPrompt .= "- Be precise with max_points - use the exact values from the instructions\n";
        $criteriaPrompt .= "- If no point values are specified, estimate reasonable distributions summing to 100\n";
        $criteriaPrompt .= "- Mark bonus sections with is_bonus: true\n";
        $criteriaPrompt .= "- Respond with ONLY the JSON array, nothing else\n";

        $url = 'https://api.openai.com/v1/chat/completions';
        $data = [
            'model' => OPENAI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => 'You extract structured rubric data from exam instructions. Always respond with valid JSON only.'],
                ['role' => 'user', 'content' => $criteriaPrompt]
            ],
            'temperature' => 0.2,
            'max_completion_tokens' => 2000
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
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo json_encode(['success' => false, 'error' => "cURL Error: {$curlError}"]);
            exit;
        }
        if ($httpCode !== 200) {
            $decoded = json_decode($response, true);
            $errMsg = $decoded['error']['message'] ?? "HTTP {$httpCode}";
            echo json_encode(['success' => false, 'error' => "API Error: {$errMsg}"]);
            exit;
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? '';
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);

        $criteria = json_decode($content, true);
        if (!$criteria || !is_array($criteria)) {
            echo json_encode(['success' => false, 'error' => 'Failed to parse criteria from GPT response.', 'raw' => $content]);
            exit;
        }

        // Validate and normalize
        $totalBase = 0;
        $totalBonus = 0;
        foreach ($criteria as &$c) {
            $c['key'] = preg_replace('/[^a-z0-9_]/', '', strtolower($c['key'] ?? 'unknown'));
            $c['label'] = $c['label'] ?? $c['key'];
            $c['max_points'] = floatval($c['max_points'] ?? 0);
            $c['is_bonus'] = !empty($c['is_bonus']);
            $c['description'] = $c['description'] ?? '';
            if ($c['is_bonus']) {
                $totalBonus += $c['max_points'];
            } else {
                $totalBase += $c['max_points'];
            }
        }
        unset($c);

        echo json_encode([
            'success' => true,
            'criteria' => $criteria,
            'total_base' => $totalBase,
            'total_bonus' => $totalBonus
        ]);
        break;

    case 'create_session':
        $folderPath = $_POST['folder_path'] ?? '';
        $sessionName = $_POST['session_name'] ?? 'Grading Session ' . date('Y-m-d H:i');
        $examInstructions = $_POST['exam_instructions'] ?? '';
        $criteriaJson = $_POST['criteria_json'] ?? '';

        if (empty(trim($examInstructions))) {
            echo json_encode(['success' => false, 'error' => 'Exam instructions are required. Please upload the exam instructions PDF.']);
            exit;
        }

        if (empty(trim($criteriaJson))) {
            echo json_encode(['success' => false, 'error' => 'Grading criteria are required. The system must parse criteria from your exam instructions.']);
            exit;
        }

        // Validate criteria JSON
        $criteria = json_decode($criteriaJson, true);
        if (!$criteria || !is_array($criteria)) {
            echo json_encode(['success' => false, 'error' => 'Invalid criteria format.']);
            exit;
        }

        $students = GradingEngine::scanSubmissions($folderPath);
        if (empty($students)) {
            echo json_encode(['success' => false, 'error' => 'No students found']);
            exit;
        }

        $db = getDB();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("INSERT INTO grading_sessions (session_name, folder_path, exam_instructions, criteria_json, total_students, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$sessionName, $folderPath, $examInstructions, $criteriaJson, count($students)]);
            $sessionId = $db->lastInsertId();

            // Store criteria in exam_criteria table
            $critStmt = $db->prepare("INSERT INTO exam_criteria (session_id, part_label, part_name, max_points, description) VALUES (?, ?, ?, ?, ?)");
            foreach ($criteria as $c) {
                $critStmt->execute([$sessionId, $c['key'], $c['label'], $c['max_points'], $c['description'] ?? '']);
            }

            $stmt = $db->prepare("INSERT INTO student_submissions (session_id, student_name, folder_name, folder_path, status) VALUES (?, ?, ?, ?, 'pending')");
            foreach ($students as $student) {
                $stmt->execute([$sessionId, $student['name'], $student['folder_name'], $student['folder_path']]);
            }

            $db->commit();
            echo json_encode(['success' => true, 'session_id' => $sessionId, 'total_students' => count($students)]);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'grade_next':
        $sessionId = intval($_POST['session_id'] ?? 0);
        if (!$sessionId) {
            echo json_encode(['success' => false, 'error' => 'No session ID']);
            exit;
        }

        $db = getDB();

        // Update session status
        $db->prepare("UPDATE grading_sessions SET status='processing' WHERE id=? AND status!='completed'")->execute([$sessionId]);

        // Find next ungraded student
        $stmt = $db->prepare("SELECT id, student_name FROM student_submissions WHERE session_id=? AND status='pending' ORDER BY student_name LIMIT 1");
        $stmt->execute([$sessionId]);
        $next = $stmt->fetch();

        if (!$next) {
            $db->prepare("UPDATE grading_sessions SET status='completed' WHERE id=?")->execute([$sessionId]);
            echo json_encode(['success' => true, 'done' => true, 'message' => 'All students graded!']);
            exit;
        }

        set_time_limit(MAX_EXECUTION_TIME);
        $result = GradingEngine::gradeStudent($sessionId, $next['id']);

        // Get progress
        $stmt = $db->prepare("SELECT COUNT(*) as graded FROM student_submissions WHERE session_id=? AND status='graded'");
        $stmt->execute([$sessionId]);
        $graded = $stmt->fetch()['graded'];

        $stmt = $db->prepare("SELECT total_students FROM grading_sessions WHERE id=?");
        $stmt->execute([$sessionId]);
        $total = $stmt->fetch()['total_students'];

        echo json_encode([
            'success' => $result['success'],
            'done' => false,
            'student_name' => $next['student_name'],
            'graded' => $graded,
            'total' => $total,
            'error' => $result['error'] ?? null,
            'grades' => $result['grades'] ?? null
        ]);
        break;

    case 'get_session':
        $sessionId = intval($_GET['session_id'] ?? 0);
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM grading_sessions WHERE id=?");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();

        $stmt = $db->prepare("SELECT * FROM student_submissions WHERE session_id=? ORDER BY student_name");
        $stmt->execute([$sessionId]);
        $submissions = $stmt->fetchAll();

        echo json_encode(['success' => true, 'session' => $session, 'submissions' => $submissions]);
        break;

    case 'get_sessions':
        $db = getDB();
        $stmt = $db->query("SELECT * FROM grading_sessions ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'sessions' => $stmt->fetchAll()]);
        break;

    case 'get_student':
        $id = intval($_GET['id'] ?? 0);
        $db = getDB();
        $stmt = $db->prepare("SELECT s.*, gs.criteria_json FROM student_submissions s LEFT JOIN grading_sessions gs ON s.session_id = gs.id WHERE s.id=?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        echo json_encode(['success' => true, 'student' => $student]);
        break;

    case 'update_grade':
        $id = intval($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? '';
        $value = floatval($_POST['value'] ?? 0);
        // Legacy fixed fields still supported for backward compatibility
        $legacyAllowed = ['part_a','part_b','part_c','part_d','part_e','bonus'];

        $db = getDB();

        if (in_array($field, $legacyAllowed)) {
            // Update legacy column
            $stmt = $db->prepare("UPDATE student_submissions SET `{$field}` = ? WHERE id = ?");
            $stmt->execute([$value, $id]);
        }

        // Always update scores_json
        $stmt = $db->prepare("SELECT scores_json, session_id FROM student_submissions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $scoresJson = json_decode($row['scores_json'] ?? '{}', true) ?: [];
        $scoresJson[$field] = $value;
        $db->prepare("UPDATE student_submissions SET scores_json = ? WHERE id = ?")->execute([json_encode($scoresJson), $id]);

        // Get criteria to recalculate totals
        $critStmt = $db->prepare("SELECT criteria_json FROM grading_sessions WHERE id = ?");
        $critStmt->execute([$row['session_id']]);
        $session = $critStmt->fetch();
        $criteria = json_decode($session['criteria_json'] ?? '[]', true) ?: [];

        $total = 0;
        $bonusTotal = 0;
        $maxBase = 0;

        if (!empty($criteria)) {
            // Dynamic calculation from criteria
            foreach ($criteria as $c) {
                $score = floatval($scoresJson[$c['key']] ?? 0);
                if (!empty($c['is_bonus'])) {
                    $bonusTotal += $score;
                } else {
                    $total += $score;
                    $maxBase += floatval($c['max_points']);
                }
            }
            $total = min($total, $maxBase);
            $final = $total + $bonusTotal;
            $pct = $maxBase > 0 ? round(($total / $maxBase) * 100, 1) : 0;
        } else {
            // Fallback to legacy columns
            $stmt = $db->prepare("SELECT part_a, part_b, part_c, part_d, part_e, bonus FROM student_submissions WHERE id = ?");
            $stmt->execute([$id]);
            $scores = $stmt->fetch();
            $total = min($scores['part_a'] + $scores['part_b'] + $scores['part_c'] + $scores['part_d'] + $scores['part_e'], 100);
            $bonusTotal = $scores['bonus'];
            $final = min($total + $bonusTotal, 110);
            $pct = $total;
        }

        if ($pct >= 96) $remarks = 'Outstanding';
        elseif ($pct >= 90) $remarks = 'Excellent';
        elseif ($pct >= 80) $remarks = 'Very Good';
        elseif ($pct >= 75) $remarks = 'Passed';
        elseif ($pct >= 60) $remarks = 'Needs Improvement';
        elseif ($pct > 0) $remarks = 'Failed';
        else $remarks = 'No Submission';

        $db->prepare("UPDATE student_submissions SET total_score=?, final_score=?, percentage=?, remarks=? WHERE id=?")
           ->execute([$total, $final, $pct, $remarks, $id]);

        echo json_encode(['success' => true, 'total' => $total, 'final' => $final, 'remarks' => $remarks]);
        break;

    case 'delete_session':
        $id = intval($_POST['session_id'] ?? 0);
        $db = getDB();
        $db->prepare("DELETE FROM grading_sessions WHERE id=?")->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'export_csv':
        $sessionId = intval($_GET['session_id'] ?? 0);
        $db = getDB();

        // Get criteria for dynamic columns
        $critStmt = $db->prepare("SELECT criteria_json FROM grading_sessions WHERE id = ?");
        $critStmt->execute([$sessionId]);
        $sessionRow = $critStmt->fetch();
        $criteria = json_decode($sessionRow['criteria_json'] ?? '[]', true) ?: [];

        $stmt = $db->prepare("SELECT * FROM student_submissions WHERE session_id=? ORDER BY student_name");
        $stmt->execute([$sessionId]);
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="grades_session_' . $sessionId . '.csv"');
        $out = fopen('php://output', 'w');

        // Build dynamic header
        $header = ['Student'];
        if (!empty($criteria)) {
            foreach ($criteria as $c) {
                $bonusTag = !empty($c['is_bonus']) ? ' [Bonus]' : '';
                $header[] = $c['label'] . ' (' . $c['max_points'] . ')' . $bonusTag;
            }
        } else {
            // Legacy fallback
            $header = array_merge($header, ['Part A (15)', 'Part B (15)', 'Part C (30)', 'Part D (10)', 'Part E (30)', 'Bonus (10)']);
        }
        $header = array_merge($header, ['Total', 'Final', 'Percentage', 'Remarks', 'Has venv', 'Feedback']);
        fputcsv($out, $header);

        foreach ($rows as $row) {
            $line = [$row['student_name']];
            if (!empty($criteria)) {
                $scores = json_decode($row['scores_json'] ?? '{}', true) ?: [];
                foreach ($criteria as $c) {
                    $line[] = floatval($scores[$c['key']] ?? 0);
                }
            } else {
                $line = array_merge($line, [$row['part_a'], $row['part_b'], $row['part_c'], $row['part_d'], $row['part_e'], $row['bonus']]);
            }
            $line = array_merge($line, [
                $row['total_score'], $row['final_score'],
                $row['percentage'] . '%', $row['remarks'],
                $row['has_venv'] ? 'YES' : 'NO',
                str_replace("\n", " | ", $row['feedback'])
            ]);
            fputcsv($out, $line);
        }
        fclose($out);
        exit;

    case 'regrade_student':
        $submissionId = intval($_POST['submission_id'] ?? 0);
        $db = getDB();
        $stmt = $db->prepare("SELECT session_id FROM student_submissions WHERE id=?");
        $stmt->execute([$submissionId]);
        $row = $stmt->fetch();
        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'Not found']);
            exit;
        }

        $db->prepare("UPDATE student_submissions SET status='pending' WHERE id=?")->execute([$submissionId]);
        set_time_limit(MAX_EXECUTION_TIME);
        $result = GradingEngine::gradeStudent($row['session_id'], $submissionId);
        echo json_encode($result);
        break;

    case 'browse_folder':
        $path = $_GET['path'] ?? '';

        // Default starting paths for Windows
        if (empty($path)) {
            $drives = [];
            foreach (range('A', 'Z') as $letter) {
                $drive = $letter . ':\\';
                if (is_dir($drive)) {
                    $drives[] = [
                        'name' => $drive,
                        'path' => $drive,
                        'type' => 'drive'
                    ];
                }
            }
            echo json_encode(['success' => true, 'folders' => $drives, 'current' => '', 'parent' => '']);
            exit;
        }

        $path = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_dir($path)) {
            echo json_encode(['success' => false, 'error' => 'Directory not found: ' . $path]);
            exit;
        }

        $folders = [];
        $items = @scandir($path);
        if ($items === false) {
            echo json_encode(['success' => false, 'error' => 'Cannot read directory: ' . $path]);
            exit;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $path . $item;
            if (is_dir($fullPath)) {
                $folders[] = [
                    'name' => $item,
                    'path' => $fullPath,
                    'type' => 'folder'
                ];
            }
        }

        usort($folders, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        $parent = dirname(rtrim($path, DIRECTORY_SEPARATOR));
        if ($parent === $path || $parent === '.') $parent = '';

        echo json_encode(['success' => true, 'folders' => $folders, 'current' => rtrim($path, DIRECTORY_SEPARATOR), 'parent' => $parent]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
}
