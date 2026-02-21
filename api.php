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

    case 'create_session':
        $folderPath = $_POST['folder_path'] ?? '';
        $sessionName = $_POST['session_name'] ?? 'Grading Session ' . date('Y-m-d H:i');
        $examInstructions = $_POST['exam_instructions'] ?? '';

        if (empty(trim($examInstructions))) {
            echo json_encode(['success' => false, 'error' => 'Exam instructions are required. Please upload the exam instructions PDF.']);
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
            $stmt = $db->prepare("INSERT INTO grading_sessions (session_name, folder_path, exam_instructions, total_students, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$sessionName, $folderPath, $examInstructions, count($students)]);
            $sessionId = $db->lastInsertId();

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
        $stmt = $db->prepare("SELECT * FROM student_submissions WHERE id=?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        echo json_encode(['success' => true, 'student' => $student]);
        break;

    case 'update_grade':
        $id = intval($_POST['id'] ?? 0);
        $field = $_POST['field'] ?? '';
        $value = floatval($_POST['value'] ?? 0);
        $allowed = ['part_a','part_b','part_c','part_d','part_e','bonus'];

        if (!in_array($field, $allowed)) {
            echo json_encode(['success' => false, 'error' => 'Invalid field']);
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare("UPDATE student_submissions SET `{$field}` = ? WHERE id = ?");
        $stmt->execute([$value, $id]);

        // Recalculate totals
        $stmt = $db->prepare("SELECT part_a, part_b, part_c, part_d, part_e, bonus FROM student_submissions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $total = min($row['part_a'] + $row['part_b'] + $row['part_c'] + $row['part_d'] + $row['part_e'], 100);
        $final = min($total + $row['bonus'], 110);

        if ($total >= 96) $remarks = 'Outstanding';
        elseif ($total >= 90) $remarks = 'Excellent';
        elseif ($total >= 80) $remarks = 'Very Good';
        elseif ($total >= 75) $remarks = 'Passed';
        elseif ($total >= 60) $remarks = 'Needs Improvement';
        elseif ($total > 0) $remarks = 'Failed';
        else $remarks = 'No Submission';

        $db->prepare("UPDATE student_submissions SET total_score=?, final_score=?, percentage=?, remarks=? WHERE id=?")
           ->execute([$total, $final, $total, $remarks, $id]);

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

        $stmt = $db->prepare("SELECT student_name, part_a, part_b, part_c, part_d, part_e, bonus, total_score, final_score, percentage, remarks, has_venv, feedback FROM student_submissions WHERE session_id=? ORDER BY student_name");
        $stmt->execute([$sessionId]);
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="grades_session_' . $sessionId . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Student', 'Part A (15)', 'Part B (15)', 'Part C (30)', 'Part D (10)', 'Part E (30)', 'Bonus (10)', 'Total (100)', 'Final (110)', 'Percentage', 'Remarks', 'Has venv', 'Feedback']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['student_name'], $row['part_a'], $row['part_b'], $row['part_c'],
                $row['part_d'], $row['part_e'], $row['bonus'], $row['total_score'],
                $row['final_score'], $row['percentage'] . '%', $row['remarks'],
                $row['has_venv'] ? 'YES' : 'NO', str_replace("\n", " | ", $row['feedback'])
            ]);
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
