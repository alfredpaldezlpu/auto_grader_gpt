<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT 103 Grading System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --surface: #1e1e2e;
            --surface-2: #282840;
            --surface-3: #313150;
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --border: #3f3f5f;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: var(--text);
        }

        .navbar {
            background: rgba(30, 30, 46, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 4px 30px rgba(0,0,0,0.3);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            background: linear-gradient(135deg, var(--primary-light), #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 12px 40px rgba(99,102,241,0.1);
        }

        .card-header {
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
            border-radius: 16px 16px 0 0 !important;
            padding: 1rem 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-light), #8b5cf6);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.3);
        }

        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary-light);
            border-radius: 10px;
            font-weight: 500;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        .form-control, .form-select {
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 0.7rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            background: var(--surface-3);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
            color: var(--text);
        }

        .table {
            color: var(--text);
        }

        .table thead th {
            background: var(--surface-2);
            color: var(--primary-light);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--primary);
            padding: 0.8rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border);
        }

        .table tbody tr:hover {
            background: rgba(99,102,241,0.08);
        }

        .table td { 
            padding: 0.7rem 0.8rem; 
            vertical-align: middle; 
        }

        .badge-outstanding { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .badge-excellent { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .badge-very-good { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .badge-passed { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .badge-needs-improvement { background: linear-gradient(135deg, #f97316, #ea580c); }
        .badge-failed { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .progress-ring {
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }

        .stat-card {
            background: var(--surface-2);
            border-radius: 12px;
            padding: 1.2rem;
            text-align: center;
            border: 1px solid var(--border);
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-light), #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.3rem;
        }

        .grading-progress {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(30, 30, 46, 0.98);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border);
            padding: 1.2rem 2rem;
            z-index: 1050;
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .grading-progress.active { transform: translateY(0); }

        .grading-progress .progress {
            height: 8px;
            border-radius: 10px;
            background: var(--surface-3);
        }

        .grading-progress .progress-bar {
            background: linear-gradient(90deg, var(--primary), #a78bfa);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .score-cell {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .score-high { color: #22c55e; }
        .score-mid { color: #f59e0b; }
        .score-low { color: #ef4444; }

        .venv-badge {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.3);
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .feedback-modal .modal-content {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
        }

        .feedback-modal .modal-header {
            border-bottom: 1px solid var(--border);
        }

        /* Feedback highlighting */
        .feedback-block {
            margin-bottom: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border-left: 4px solid var(--border);
            background: var(--surface-3);
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .feedback-block.fb-overall {
            border-left-color: #a78bfa;
            background: rgba(99, 102, 241, 0.08);
        }

        .feedback-block .fb-label {
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .feedback-block.fb-overall .fb-label { color: #c4b5fd; }

        .feedback-block .fb-score {
            font-weight: 700;
            font-size: 0.85rem;
            float: right;
            padding: 1px 8px;
            border-radius: 6px;
            background: rgba(255,255,255,0.06);
        }

        .feedback-block .fb-text {
            color: var(--text-muted);
        }

        /* Criteria preview cards */
        .criteria-card {
            background: var(--surface-3);
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            border-left: 3px solid var(--primary);
            font-size: 0.82rem;
        }
        .criteria-card .criteria-label {
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .criteria-card .criteria-pts {
            font-weight: 700;
            font-size: 0.85rem;
            float: right;
            color: var(--primary);
        }
        .criteria-card .criteria-desc {
            color: var(--text-muted);
            font-size: 0.78rem;
            margin-top: 0.15rem;
        }
        .criteria-bonus { border-left-color: #ec4899 !important; }
        .criteria-bonus .criteria-pts { color: #ec4899; }

        .animate-in {
            animation: fadeSlideIn 0.5s ease forwards;
        }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .hero-section {
            padding: 4rem 0 2rem;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--primary-light), #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .hero-section p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .editable-score {
            cursor: pointer;
            padding: 2px 8px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .editable-score:hover {
            background: var(--surface-3);
        }

        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
        }

        /* Folder Browser Styles */
        .browse-folder-item {
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.15s ease;
            margin-bottom: 2px;
            border: 1px solid transparent;
        }

        .browse-folder-item:hover {
            background: var(--surface-2);
        }

        .browse-folder-item.active {
            background: rgba(99, 102, 241, 0.15);
            border-color: var(--primary);
        }

        #folderBrowserList::-webkit-scrollbar { width: 6px; }
        #folderBrowserList::-webkit-scrollbar-track { background: var(--surface); }
        #folderBrowserList::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        .table-responsive {
            max-height: calc(100vh - 300px);
            overflow-y: auto;
        }

        .table-responsive::-webkit-scrollbar { width: 6px; }
        .table-responsive::-webkit-scrollbar-track { background: var(--surface); }
        .table-responsive::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        .session-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .session-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#" onclick="showPage('home')">
            <i class="bi bi-mortarboard-fill me-2"></i>IT 103 Grading System
        </a>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-primary" onclick="showPage('home')">
                <i class="bi bi-house me-1"></i> Home
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="showPage('sessions')">
                <i class="bi bi-clock-history me-1"></i> History
            </button>
        </div>
    </div>
</nav>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Main Content -->
<div class="container-fluid px-4" style="margin-top: 70px; padding-bottom: 100px;">

    <!-- HOME PAGE -->
    <div id="page-home">
        <div class="hero-section animate-in">
            <h1><i class="bi bi-cpu me-2"></i>AI-Powered Grading</h1>
            <p>Grade Python practical exam submissions using GPT. Fast, fair, and consistent.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card animate-in">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-folder-plus me-2 text-primary"></i>
                        <h5 class="mb-0">New Grading Session</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-tag me-1"></i> Session Name
                            </label>
                            <input type="text" class="form-control" id="sessionName" 
                                   placeholder="e.g. IT103 - Prelim Practical Exam 2025-2026">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-folder2-open me-1"></i> Submissions Folder Path
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="folderPath" 
                                       placeholder="e.g. C:\Users\...\Practical Exam Submission">
                                <button class="btn btn-outline-primary" type="button" onclick="openFolderBrowser()">
                                    <i class="bi bi-folder-symlink me-1"></i> Browse
                                </button>
                            </div>
                            <div class="form-text text-muted">
                                Enter the full path or click Browse to select the parent folder containing all student submission folders.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Exam Instructions (PDF) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="examPdfFile" accept=".pdf" onchange="handlePdfUpload(this)">
                                <button class="btn btn-outline-primary" type="button" onclick="document.getElementById('examPdfFile').click()">
                                    <i class="bi bi-upload me-1"></i> Upload
                                </button>
                            </div>
                            <div class="form-text text-muted">
                                Upload the PDF file containing the exam instructions / grading criteria. <strong>Required.</strong>
                            </div>
                            <!-- PDF Upload Status -->
                            <div id="pdfUploadStatus" class="d-none mt-2">
                                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background: var(--surface-2); border: 1px solid var(--border);">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 1.3rem;"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold" style="font-size: 0.9rem;" id="pdfFileName"></div>
                                        <div class="text-muted" style="font-size: 0.75rem;" id="pdfExtractInfo"></div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="togglePdfPreview()" title="Preview extracted text">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="removePdf()" title="Remove">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- PDF Extracted Text Preview (hidden by default) -->
                            <div id="pdfPreview" class="d-none mt-2">
                                <textarea class="form-control" id="examInstructions" rows="6" readonly
                                          style="font-size: 0.8rem; font-family: monospace; background: var(--surface-2); resize: vertical;"></textarea>
                            </div>
                            <!-- Upload Progress -->
                            <div id="pdfUploadProgress" class="d-none mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <span class="text-muted" style="font-size: 0.85rem;">Uploading & extracting text from PDF...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Detected Criteria Preview -->
                        <div id="criteriaPreview" class="mb-3 d-none">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-list-check me-1"></i> Detected Grading Criteria
                                <span class="badge bg-success ms-1" id="criteriaTotalBadge"></span>
                            </label>
                            <div id="criteriaParseProgress" class="d-none mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="spinner-border spinner-border-sm text-info"></div>
                                    <span class="text-muted" style="font-size: 0.85rem;">Analyzing exam instructions to detect rubric...</span>
                                </div>
                            </div>
                            <div id="criteriaList" class="d-flex flex-column gap-2"></div>
                            <div class="form-text text-muted mt-1">
                                <i class="bi bi-info-circle me-1"></i> Criteria auto-detected from your PDF. You can re-upload to re-parse.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="scanFolder()">
                                <i class="bi bi-search me-1"></i> Scan Folder
                            </button>
                            <button class="btn btn-primary" onclick="startGrading()" id="btnStartGrading" disabled>
                                <i class="bi bi-play-fill me-1"></i> Start Grading
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Scan Results Preview -->
                <div class="card mt-4 d-none" id="scanResults">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-people me-2 text-success"></i>
                            <span class="fw-semibold">Students Found</span>
                        </div>
                        <span class="badge bg-primary" id="studentCount">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Student Name</th>
                                        <th>Folder</th>
                                    </tr>
                                </thead>
                                <tbody id="scanResultsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SESSIONS PAGE -->
    <div id="page-sessions" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <h3 class="fw-bold"><i class="bi bi-clock-history me-2"></i>Grading History</h3>
            <button class="btn btn-primary" onclick="showPage('home')">
                <i class="bi bi-plus-lg me-1"></i> New Session
            </button>
        </div>
        <div class="row g-3" id="sessionsList">
            <div class="empty-state">
                <i class="bi bi-inbox d-block"></i>
                <h5>No grading sessions yet</h5>
                <p>Start a new grading session from the home page.</p>
            </div>
        </div>
    </div>

    <!-- RESULTS PAGE -->
    <div id="page-results" class="d-none">
        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
            <div>
                <h3 class="fw-bold mb-1" id="resultsTitle">Grading Results</h3>
                <p class="text-muted mb-0" id="resultsSubtitle"></p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" onclick="exportCSV()">
                    <i class="bi bi-download me-1"></i> Export CSV
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="showPage('sessions')">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </button>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-3 mb-4" id="statsRow"></div>

        <!-- Grades Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="gradesTable">
                        <thead>
                            <tr id="gradesHead">
                                <th width="40">#</th>
                                <th>Student Name</th>
                                <th class="text-center" width="65">A<br><small class="fw-normal">/15</small></th>
                                <th class="text-center" width="65">B<br><small class="fw-normal">/15</small></th>
                                <th class="text-center" width="65">C<br><small class="fw-normal">/30</small></th>
                                <th class="text-center" width="65">D<br><small class="fw-normal">/10</small></th>
                                <th class="text-center" width="65">E<br><small class="fw-normal">/30</small></th>
                                <th class="text-center" width="65">Bonus<br><small class="fw-normal">/10</small></th>
                                <th class="text-center" width="75">Total<br><small class="fw-normal">/100</small></th>
                                <th class="text-center" width="90">Remarks</th>
                                <th class="text-center" width="90">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="gradesBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grading Progress Bar -->
<div class="grading-progress" id="gradingProgress">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <i class="bi bi-robot me-2 text-primary pulse"></i>
            <strong id="gradingStatus">Grading in progress...</strong>
            <span class="text-muted ms-2" id="gradingCurrentStudent"></span>
        </div>
        <div>
            <span class="fw-semibold" id="gradingCount">0/0</span>
        </div>
    </div>
    <div class="progress">
        <div class="progress-bar" id="gradingBar" style="width: 0%"></div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade feedback-modal" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle me-2"></i>
                    <span id="feedbackStudentName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Score summary -->
                <div class="row g-2 mb-3" id="feedbackScores"></div>
                <hr class="border-secondary">
                <!-- Detailed feedback -->
                <h6 class="fw-semibold mb-2"><i class="bi bi-chat-text me-1"></i> Feedback</h6>
                <div class="p-3 rounded" style="background:var(--surface-2); max-height:400px; overflow-y:auto;" id="feedbackText"></div>
                <!-- Venv info -->
                <div id="feedbackVenv" class="d-none mt-2">
                    <span class="venv-badge"><i class="bi bi-exclamation-triangle me-1"></i> venv included: <span id="feedbackVenvLoc"></span></span>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button class="btn btn-sm btn-outline-primary" onclick="regradeStudent()" id="btnRegrade">
                    <i class="bi bi-arrow-repeat me-1"></i> Re-grade
                </button>
                <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Folder Browser Modal -->
<div class="modal fade" id="folderBrowserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: var(--surface); border: 1px solid var(--border); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title"><i class="bi bi-folder2-open me-2 text-primary"></i> Browse Folders</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Breadcrumb / Current Path -->
                <div class="px-3 py-2" style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="browseFolder('')" title="Go to root">
                            <i class="bi bi-pc-display"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="btnBrowseUp" onclick="browseParent()" title="Go up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <div class="flex-grow-1">
                            <input type="text" class="form-control form-control-sm" id="browseCurrentPath" 
                                   placeholder="Current directory..." readonly
                                   style="background: var(--surface-3); border-color: var(--border); color: var(--text); font-family: monospace; font-size: 0.85rem;">
                        </div>
                    </div>
                </div>
                <!-- Folder List -->
                <div id="folderBrowserList" style="height: 400px; overflow-y: auto; padding: 0.5rem;">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-arrow-up-circle" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mt-2">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border);">
                <span class="me-auto text-muted" style="font-size: 0.8rem;" id="browseStatus"></span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnSelectFolder" onclick="selectBrowsedFolder()">
                    <i class="bi bi-check-lg me-1"></i> Select This Folder
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentSessionId = null;
    let currentStudentId = null;
    let scannedStudents = [];
    let browseCurrentDir = '';
    let browseParentDir = '';

    // ===== Folder Browser =====
    function openFolderBrowser() {
        const modal = new bootstrap.Modal(document.getElementById('folderBrowserModal'));
        modal.show();
        // Start from existing path if available, otherwise root
        const existing = document.getElementById('folderPath').value.trim();
        browseFolder(existing || '');
    }

    async function browseFolder(path) {
        const listEl = document.getElementById('folderBrowserList');
        listEl.innerHTML = `<div class="text-center text-muted py-5"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading...</div>`;

        try {
            const res = await fetch('api.php?action=browse_folder&path=' + encodeURIComponent(path));
            const data = await res.json();

            if (!data.success) {
                listEl.innerHTML = `<div class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle me-2"></i>${data.error}</div>`;
                return;
            }

            browseCurrentDir = data.current;
            browseParentDir = data.parent;
            document.getElementById('browseCurrentPath').value = data.current || 'My Computer';
            document.getElementById('btnBrowseUp').disabled = !data.parent && data.current !== '';
            document.getElementById('browseStatus').textContent = data.folders.length + ' folder(s)';

            if (data.folders.length === 0) {
                listEl.innerHTML = `<div class="text-center text-muted py-4"><i class="bi bi-folder-x" style="font-size:2rem; opacity:0.3;"></i><p class="mt-2">No subfolders found</p></div>`;
                return;
            }

            listEl.innerHTML = data.folders.map(f => `
                <div class="browse-folder-item d-flex align-items-center px-3 py-2" 
                     ondblclick="browseFolder('${f.path.replace(/\\/g, '\\\\')}')" 
                     onclick="highlightFolder(this, '${f.path.replace(/\\/g, '\\\\')}')">
                    <i class="bi ${f.type === 'drive' ? 'bi-hdd text-warning' : 'bi-folder-fill text-primary'} me-3" style="font-size: 1.2rem;"></i>
                    <span class="fw-medium">${f.name}</span>
                    <i class="bi bi-chevron-right ms-auto text-muted" style="font-size: 0.75rem;"></i>
                </div>
            `).join('');
        } catch (err) {
            listEl.innerHTML = `<div class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle me-2"></i>Error: ${err.message}</div>`;
        }
    }

    function highlightFolder(el, path) {
        document.querySelectorAll('.browse-folder-item').forEach(e => e.classList.remove('active'));
        el.classList.add('active');
        browseCurrentDir = path;
        document.getElementById('browseCurrentPath').value = path;
    }

    function browseParent() {
        if (browseParentDir !== undefined) {
            browseFolder(browseParentDir);
        }
    }

    function selectBrowsedFolder() {
        if (browseCurrentDir) {
            document.getElementById('folderPath').value = browseCurrentDir;
            bootstrap.Modal.getInstance(document.getElementById('folderBrowserModal')).hide();
            showToast('Folder selected: ' + browseCurrentDir, 'success');
        } else {
            showToast('Please navigate to a folder first', 'warning');
        }
    }

    // ===== PDF Upload & Extraction =====
    let extractedExamInstructions = '';
    let pdfUploaded = false;
    let parsedCriteria = []; // Dynamic criteria parsed from PDF
    let sessionCriteria = null; // Criteria for the currently viewed session

    async function handlePdfUpload(input) {
        const file = input.files[0];
        if (!file) return;

        if (!file.name.toLowerCase().endsWith('.pdf')) {
            showToast('Please select a PDF file', 'warning');
            input.value = '';
            return;
        }

        // Show progress
        document.getElementById('pdfUploadProgress').classList.remove('d-none');
        document.getElementById('pdfUploadStatus').classList.add('d-none');
        document.getElementById('pdfPreview').classList.add('d-none');

        const fd = new FormData();
        fd.append('action', 'upload_pdf');
        fd.append('pdf_file', file);

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();

            document.getElementById('pdfUploadProgress').classList.add('d-none');

            if (!data.success) {
                showToast('PDF upload failed: ' + data.error, 'error');
                input.value = '';
                return;
            }

            extractedExamInstructions = data.text;
            pdfUploaded = true;

            // Show status
            document.getElementById('pdfFileName').textContent = data.filename;
            const charCount = data.text.length;
            const wordCount = data.text.split(/\s+/).filter(w => w).length;
            document.getElementById('pdfExtractInfo').textContent = `Extracted ${wordCount.toLocaleString()} words (${charCount.toLocaleString()} chars)`;
            document.getElementById('examInstructions').value = data.text;
            document.getElementById('pdfUploadStatus').classList.remove('d-none');

            showToast(`PDF uploaded & extracted: ${data.filename}`, 'success');

            // Auto-parse criteria from extracted text
            parseCriteria(data.text);
        } catch (err) {
            document.getElementById('pdfUploadProgress').classList.add('d-none');
            showToast('Error uploading PDF: ' + err.message, 'error');
            input.value = '';
        }
    }

    function togglePdfPreview() {
        const preview = document.getElementById('pdfPreview');
        preview.classList.toggle('d-none');
    }

    function removePdf() {
        extractedExamInstructions = '';
        pdfUploaded = false;
        parsedCriteria = [];
        document.getElementById('examPdfFile').value = '';
        document.getElementById('pdfUploadStatus').classList.add('d-none');
        document.getElementById('pdfPreview').classList.add('d-none');
        document.getElementById('examInstructions').value = '';
        document.getElementById('criteriaPreview').classList.add('d-none');
        document.getElementById('criteriaList').innerHTML = '';
        showToast('PDF removed', 'info');
    }

    // ===== Dynamic Criteria Parsing =====
    async function parseCriteria(examText) {
        document.getElementById('criteriaPreview').classList.remove('d-none');
        document.getElementById('criteriaParseProgress').classList.remove('d-none');
        document.getElementById('criteriaList').innerHTML = '';

        const fd = new FormData();
        fd.append('action', 'parse_criteria');
        fd.append('exam_text', examText);

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();
            document.getElementById('criteriaParseProgress').classList.add('d-none');

            if (!data.success) {
                showToast('Failed to parse criteria: ' + data.error, 'error');
                document.getElementById('criteriaPreview').classList.add('d-none');
                return;
            }

            parsedCriteria = data.criteria;
            renderCriteriaPreview(data.criteria, data.total_base, data.total_bonus);
            showToast(`Detected ${data.criteria.length} grading criteria (${data.total_base} base pts` + (data.total_bonus > 0 ? ` + ${data.total_bonus} bonus` : '') + ')', 'success');
        } catch (err) {
            document.getElementById('criteriaParseProgress').classList.add('d-none');
            showToast('Error parsing criteria: ' + err.message, 'error');
        }
    }

    // Color palette for dynamic criteria blocks
    const CRITERIA_COLORS = [
        { border: '#6366f1', label: '#818cf8' },
        { border: '#3b82f6', label: '#60a5fa' },
        { border: '#8b5cf6', label: '#a78bfa' },
        { border: '#f59e0b', label: '#fbbf24' },
        { border: '#22c55e', label: '#4ade80' },
        { border: '#06b6d4', label: '#22d3ee' },
        { border: '#f97316', label: '#fb923c' },
        { border: '#14b8a6', label: '#2dd4bf' },
        { border: '#e11d48', label: '#fb7185' },
        { border: '#8b5cf6', label: '#c084fc' },
        { border: '#84cc16', label: '#a3e635' },
        { border: '#0ea5e9', label: '#38bdf8' },
    ];
    const BONUS_COLOR = { border: '#ec4899', label: '#f472b6' };

    function getCriteriaColor(index, isBonus) {
        if (isBonus) return BONUS_COLOR;
        return CRITERIA_COLORS[index % CRITERIA_COLORS.length];
    }

    function renderCriteriaPreview(criteria, totalBase, totalBonus) {
        const container = document.getElementById('criteriaList');
        container.innerHTML = '';

        const badge = document.getElementById('criteriaTotalBadge');
        badge.textContent = `${totalBase} pts` + (totalBonus > 0 ? ` + ${totalBonus} bonus` : '');

        let nonBonusIdx = 0;
        criteria.forEach((c, i) => {
            const color = getCriteriaColor(c.is_bonus ? 0 : nonBonusIdx, c.is_bonus);
            if (!c.is_bonus) nonBonusIdx++;

            const div = document.createElement('div');
            div.className = 'criteria-card' + (c.is_bonus ? ' criteria-bonus' : '');
            div.style.borderLeftColor = color.border;
            div.innerHTML = `
                <div class="criteria-label" style="color: ${color.label}">
                    ${escapeHtml(c.label)}
                    <span class="criteria-pts" style="color: ${color.label}">${c.max_points} pts${c.is_bonus ? ' (Bonus)' : ''}</span>
                </div>
                ${c.description ? `<div class="criteria-desc">${escapeHtml(c.description)}</div>` : ''}
            `;
            container.appendChild(div);
        });
    }

    // Pages
    function showPage(page) {
        document.querySelectorAll('[id^="page-"]').forEach(el => el.classList.add('d-none'));
        document.getElementById('page-' + page).classList.remove('d-none');
        if (page === 'sessions') loadSessions();
    }

    // Toast
    function showToast(message, type = 'info') {
        const colors = { success: 'bg-success', error: 'bg-danger', info: 'bg-primary', warning: 'bg-warning' };
        const icons = { success: 'check-circle', error: 'x-circle', info: 'info-circle', warning: 'exclamation-triangle' };
        const html = `
            <div class="toast align-items-center text-white ${colors[type]} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${icons[type]} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;
        const container = document.getElementById('toastContainer');
        container.insertAdjacentHTML('beforeend', html);
        const toast = new bootstrap.Toast(container.lastElementChild, { delay: 4000 });
        toast.show();
    }

    // Scan folder
    async function scanFolder() {
        const folderPath = document.getElementById('folderPath').value.trim();
        if (!folderPath) { showToast('Please enter a folder path', 'warning'); return; }

        const fd = new FormData();
        fd.append('action', 'scan_folder');
        fd.append('folder_path', folderPath);

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (!data.success) { showToast(data.error, 'error'); return; }

            scannedStudents = data.students;
            document.getElementById('studentCount').textContent = data.count;
            const tbody = document.getElementById('scanResultsBody');
            tbody.innerHTML = data.students.map((s, i) => `
                <tr>
                    <td class="text-muted">${i + 1}</td>
                    <td class="fw-semibold">${s.name}</td>
                    <td class="text-muted" style="font-size:0.8rem">${s.folder_name.substring(0, 60)}...</td>
                </tr>
            `).join('');

            document.getElementById('scanResults').classList.remove('d-none');
            document.getElementById('btnStartGrading').disabled = false;
            showToast(`Found ${data.count} student submissions`, 'success');
        } catch (err) {
            showToast('Error scanning folder: ' + err.message, 'error');
        }
    }

    // Start grading
    async function startGrading() {
        const folderPath = document.getElementById('folderPath').value.trim();
        const sessionName = document.getElementById('sessionName').value.trim() || 'Grading Session ' + new Date().toLocaleDateString();

        if (!pdfUploaded || !extractedExamInstructions) {
            showToast('Please upload the Exam Instructions PDF before starting', 'warning');
            return;
        }

        if (!parsedCriteria || parsedCriteria.length === 0) {
            showToast('Grading criteria not detected. Please re-upload the PDF.', 'warning');
            return;
        }

        const examInstructions = extractedExamInstructions;

        const fd = new FormData();
        fd.append('action', 'create_session');
        fd.append('folder_path', folderPath);
        fd.append('session_name', sessionName);
        fd.append('exam_instructions', examInstructions);
        fd.append('criteria_json', JSON.stringify(parsedCriteria));

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (!data.success) { showToast(data.error, 'error'); return; }

            currentSessionId = data.session_id;
            document.getElementById('btnStartGrading').disabled = true;
            showToast('Session created! Starting GPT grading...', 'info');

            // Show progress bar
            const progressEl = document.getElementById('gradingProgress');
            progressEl.classList.add('active');

            await gradeNextStudent(data.total_students);
        } catch (err) {
            showToast('Error: ' + err.message, 'error');
        }
    }

    // Grade students one by one
    async function gradeNextStudent(totalStudents) {
        const fd = new FormData();
        fd.append('action', 'grade_next');
        fd.append('session_id', currentSessionId);

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.done) {
                document.getElementById('gradingStatus').innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> Grading Complete!';
                document.getElementById('gradingCurrentStudent').textContent = '';
                document.getElementById('gradingBar').style.width = '100%';
                showToast('All students graded successfully!', 'success');

                setTimeout(() => {
                    document.getElementById('gradingProgress').classList.remove('active');
                    loadResults(currentSessionId);
                }, 2000);
                return;
            }

            // Update progress
            const pct = Math.round((data.graded / totalStudents) * 100);
            document.getElementById('gradingBar').style.width = pct + '%';
            document.getElementById('gradingCount').textContent = `${data.graded}/${totalStudents}`;
            document.getElementById('gradingCurrentStudent').textContent = data.student_name;

            if (data.success) {
                document.getElementById('gradingStatus').textContent = `Graded: ${data.student_name}`;
            } else {
                document.getElementById('gradingStatus').textContent = `Error grading ${data.student_name}: ${data.error}`;
            }

            // Small delay to avoid rate limiting
            setTimeout(() => gradeNextStudent(totalStudents), 1500);
        } catch (err) {
            showToast('Grading error: ' + err.message, 'error');
            setTimeout(() => gradeNextStudent(totalStudents), 3000);
        }
    }

    // Load sessions
    async function loadSessions() {
        try {
            const res = await fetch('api.php?action=get_sessions');
            const data = await res.json();

            const container = document.getElementById('sessionsList');
            if (!data.sessions || data.sessions.length === 0) {
                container.innerHTML = `
                    <div class="col-12 empty-state">
                        <i class="bi bi-inbox d-block"></i>
                        <h5>No grading sessions yet</h5>
                        <p>Start a new grading session from the home page.</p>
                    </div>`;
                return;
            }

            container.innerHTML = data.sessions.map(s => {
                const statusColors = { pending: 'warning', processing: 'info', completed: 'success', error: 'danger' };
                const statusIcons = { pending: 'clock', processing: 'gear-wide-connected', completed: 'check-circle', error: 'x-circle' };
                return `
                <div class="col-md-6 col-lg-4">
                    <div class="card session-card" onclick="loadResults(${s.id})">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0">${s.session_name}</h6>
                                <span class="badge bg-${statusColors[s.status]}">
                                    <i class="bi bi-${statusIcons[s.status]} me-1"></i>${s.status}
                                </span>
                            </div>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-people me-1"></i> ${s.graded_count}/${s.total_students} students graded
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>${new Date(s.created_at).toLocaleDateString()}
                                </small>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteSession(${s.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }).join('');
        } catch (err) {
            showToast('Error loading sessions: ' + err.message, 'error');
        }
    }

    // Load results
    async function loadResults(sessionId) {
        currentSessionId = sessionId;
        try {
            const res = await fetch(`api.php?action=get_session&session_id=${sessionId}`);
            const data = await res.json();

            if (!data.success) { showToast(data.error || 'Error', 'error'); return; }

            const session = data.session;
            const subs = data.submissions;
            const criteria = JSON.parse(session.criteria_json || '[]');
            sessionCriteria = criteria;

            document.getElementById('resultsTitle').textContent = session.session_name;
            document.getElementById('resultsSubtitle').textContent = `${subs.length} students | Created: ${new Date(session.created_at).toLocaleString()}`;

            // Stats
            const graded = subs.filter(s => s.status === 'graded');
            const scores = graded.map(s => parseFloat(s.percentage || s.total_score));
            const avg = scores.length ? (scores.reduce((a,b) => a+b, 0) / scores.length).toFixed(1) : '0';
            const highest = scores.length ? Math.max(...scores).toFixed(1) : '0';
            const lowest = scores.length ? Math.min(...scores).toFixed(1) : '0';
            const passing = scores.filter(s => s >= 75).length;
            const venvCount = graded.filter(s => parseInt(s.has_venv) === 1).length;

            document.getElementById('statsRow').innerHTML = `
                <div class="col-6 col-md-2"><div class="stat-card"><div class="stat-value">${graded.length}</div><div class="stat-label">Graded</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-card"><div class="stat-value">${avg}%</div><div class="stat-label">Average</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-card"><div class="stat-value">${highest}%</div><div class="stat-label">Highest</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-card"><div class="stat-value">${lowest}%</div><div class="stat-label">Lowest</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-card"><div class="stat-value">${passing}/${graded.length}</div><div class="stat-label">Passing</div></div></div>
                <div class="col-6 col-md-2"><div class="stat-card"><div class="stat-value" style="-webkit-text-fill-color:${venvCount > 0 ? '#ef4444':'#22c55e'}">${venvCount}</div><div class="stat-label">With venv</div></div></div>
            `;

            // Dynamic table header
            const thead = document.getElementById('gradesHead');
            let headerHtml = '<th width="40">#</th><th>Student Name</th>';
            if (criteria.length > 0) {
                criteria.forEach(c => {
                    // Short header label: take first word or abbreviation
                    const shortHeader = c.label.length > 12 ? c.label.substring(0, 10) + '..' : c.label;
                    const bonusTag = c.is_bonus ? ' 🌟' : '';
                    headerHtml += `<th class="text-center" width="60" title="${escapeHtml(c.label)}">${escapeHtml(shortHeader)}${bonusTag}<br><small class="fw-normal">/${c.max_points}</small></th>`;
                });
            } else {
                headerHtml += `
                    <th class="text-center" width="65">A<br><small class="fw-normal">/15</small></th>
                    <th class="text-center" width="65">B<br><small class="fw-normal">/15</small></th>
                    <th class="text-center" width="65">C<br><small class="fw-normal">/30</small></th>
                    <th class="text-center" width="65">D<br><small class="fw-normal">/10</small></th>
                    <th class="text-center" width="65">E<br><small class="fw-normal">/30</small></th>
                    <th class="text-center" width="65">Bonus<br><small class="fw-normal">/10</small></th>`;
            }
            // Compute max total from criteria
            const maxBase = criteria.length > 0
                ? criteria.filter(c => !c.is_bonus).reduce((sum, c) => sum + parseFloat(c.max_points), 0)
                : 100;
            headerHtml += `<th class="text-center" width="75">Total<br><small class="fw-normal">/${maxBase}</small></th>`;
            headerHtml += '<th class="text-center" width="90">Remarks</th>';
            headerHtml += '<th class="text-center" width="90">Actions</th>';
            thead.innerHTML = headerHtml;

            // Table body
            const tbody = document.getElementById('gradesBody');
            tbody.innerHTML = subs.map((s, i) => {
                const total = parseFloat(s.total_score);
                const pct = parseFloat(s.percentage || 0);
                const scoreClass = pct >= 90 ? 'score-high' : (pct >= 75 ? 'score-mid' : 'score-low');
                const remarksBadge = s.remarks ? `badge-${s.remarks.toLowerCase().replace(/\s+/g, '-')}` : '';
                const venv = parseInt(s.has_venv) === 1 ? '<span class="venv-badge ms-1" title="venv included"><i class="bi bi-exclamation-triangle-fill"></i></span>' : '';
                const statusIcon = s.status === 'graded' ? '' : (s.status === 'error' ? '<i class="bi bi-x-circle text-danger ms-1" title="Error"></i>' : '<i class="bi bi-clock text-warning ms-1"></i>');

                let scoreCells = '';
                if (criteria.length > 0) {
                    const scoresObj = JSON.parse(s.scores_json || '{}');
                    criteria.forEach(c => {
                        const score = parseFloat(scoresObj[c.key] || 0);
                        if (c.is_bonus) {
                            scoreCells += `<td class="text-center score-cell">${score > 0 ? '<span class="text-info">+' + score.toFixed(1) + '</span>' : '<span class="text-muted">0</span>'}</td>`;
                        } else {
                            scoreCells += `<td class="text-center score-cell">${fmtScore(score, c.max_points)}</td>`;
                        }
                    });
                } else {
                    scoreCells = `
                        <td class="text-center score-cell">${fmtScore(s.part_a, 15)}</td>
                        <td class="text-center score-cell">${fmtScore(s.part_b, 15)}</td>
                        <td class="text-center score-cell">${fmtScore(s.part_c, 30)}</td>
                        <td class="text-center score-cell">${fmtScore(s.part_d, 10)}</td>
                        <td class="text-center score-cell">${fmtScore(s.part_e, 30)}</td>
                        <td class="text-center score-cell">${parseFloat(s.bonus) > 0 ? '<span class="text-info">+' + parseFloat(s.bonus).toFixed(1) + '</span>' : '<span class="text-muted">0</span>'}</td>`;
                }

                return `<tr>
                    <td class="text-muted">${i + 1}</td>
                    <td class="fw-semibold">${s.student_name}${venv}${statusIcon}</td>
                    ${scoreCells}
                    <td class="text-center fw-bold ${scoreClass}" style="font-size:1.05rem">${total.toFixed(1)}</td>
                    <td class="text-center"><span class="badge ${remarksBadge}" style="font-size:0.7rem">${s.remarks || '-'}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="showFeedback(${s.id})" title="View details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');

            showPage('results');
        } catch (err) {
            showToast('Error loading results: ' + err.message, 'error');
        }
    }

    function fmtScore(val, max) {
        const v = parseFloat(val);
        const pct = v / max;
        const cls = pct >= 0.9 ? 'score-high' : (pct >= 0.7 ? 'score-mid' : 'score-low');
        return `<span class="${cls}">${v.toFixed(1)}</span>`;
    }

    // Format feedback with dynamic syntax highlighting
    function formatFeedback(text, criteria) {
        if (!text || text === 'No feedback available.') {
            return '<div class="text-muted text-center py-3"><i class="bi bi-chat-left-dots me-2"></i>No feedback available.</div>';
        }

        const lines = text.split('\n');
        let html = '';
        let currentBlock = null;
        let currentText = [];

        // Build dynamic matchers from criteria
        // Feedback lines have format: "Label (score/max): feedback text"
        const sectionMatchers = [];

        if (criteria && criteria.length > 0) {
            let nonBonusIdx = 0;
            criteria.forEach((c, i) => {
                // Escape label for regex use
                const escapedLabel = c.label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                // Match format: "Label (score/max): text"
                const regex = new RegExp('^' + escapedLabel + '\\s*\\(([^)]+)\\):\\s*(.*)$', 'i');
                const color = getCriteriaColor(c.is_bonus ? 0 : nonBonusIdx, c.is_bonus);
                if (!c.is_bonus) nonBonusIdx++;
                sectionMatchers.push({ regex, label: c.label, color, isBonus: c.is_bonus });
            });
        } else {
            // Legacy fallback matchers
            const legacyParts = [
                { regex: /^Part A\s*\(([^)]+)\):\s*(.*)$/i, label: 'Part A' },
                { regex: /^Part B\s*\(([^)]+)\):\s*(.*)$/i, label: 'Part B' },
                { regex: /^Part C\s*\(([^)]+)\):\s*(.*)$/i, label: 'Part C' },
                { regex: /^Part D\s*\(([^)]+)\):\s*(.*)$/i, label: 'Part D' },
                { regex: /^Part E\s*\(([^)]+)\):\s*(.*)$/i, label: 'Part E' },
                { regex: /^Bonus\s*\(([^)]+)\):\s*(.*)$/i, label: 'Bonus' },
            ];
            legacyParts.forEach((p, i) => {
                const isBonus = p.label === 'Bonus';
                const color = getCriteriaColor(isBonus ? 0 : i, isBonus);
                sectionMatchers.push({ regex: p.regex, label: p.label, color, isBonus });
            });
        }

        // Also try a generic pattern: any line with "(score/max):" that doesn't match known labels
        const genericScoreRegex = /^(.+?)\s*\(([^)]+)\):\s*(.*)$/;

        function flushBlock() {
            if (currentBlock) {
                html += `<div class="feedback-block" style="border-left-color: ${currentBlock.borderColor}">
                    <div class="fb-label" style="color: ${currentBlock.labelColor}">${escapeHtml(currentBlock.label)} <span class="fb-score">${escapeHtml(currentBlock.score)}</span></div>
                    <div class="fb-text">${escapeHtml(currentText.join('\n').trim())}</div>
                </div>`;
                currentBlock = null;
                currentText = [];
            }
        }

        for (const line of lines) {
            const trimmed = line.trim();
            if (!trimmed) continue;

            // Check for "Overall:" section
            if (/^Overall:\s*(.*)$/i.test(trimmed)) {
                flushBlock();
                const overallText = trimmed.replace(/^Overall:\s*/i, '');
                currentBlock = { borderColor: '#a78bfa', labelColor: '#c4b5fd', label: 'Overall Assessment', score: '' };
                currentText = [overallText];
                continue;
            }

            // Check against known section matchers
            let matched = false;
            for (const matcher of sectionMatchers) {
                const m = trimmed.match(matcher.regex);
                if (m) {
                    flushBlock();
                    currentBlock = {
                        borderColor: matcher.color.border,
                        labelColor: matcher.color.label,
                        label: matcher.label,
                        score: m[1]
                    };
                    currentText = [m[2] || ''];
                    matched = true;
                    break;
                }
            }

            // If no known matcher hit, try generic score pattern for unknown sections
            if (!matched) {
                const gm = trimmed.match(genericScoreRegex);
                if (gm && !currentBlock) {
                    flushBlock();
                    // Assign a color based on how many blocks we've seen
                    const blockIdx = html.split('feedback-block').length - 1;
                    const color = getCriteriaColor(blockIdx, false);
                    currentBlock = {
                        borderColor: color.border,
                        labelColor: color.label,
                        label: gm[1].trim(),
                        score: gm[2]
                    };
                    currentText = [gm[3] || ''];
                    matched = true;
                }
            }

            if (!matched && currentBlock) {
                currentText.push(trimmed);
            } else if (!matched) {
                html += `<div class="feedback-block"><div class="fb-text">${escapeHtml(trimmed)}</div></div>`;
            }
        }
        flushBlock();

        return html || `<div class="text-muted text-center py-3">${escapeHtml(text)}</div>`;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Show feedback modal
    async function showFeedback(submissionId) {
        currentStudentId = submissionId;
        try {
            const res = await fetch(`api.php?action=get_student&id=${submissionId}`);
            const data = await res.json();
            const s = data.student;

            document.getElementById('feedbackStudentName').textContent = s.student_name;

            // Build dynamic score cards
            const criteria = JSON.parse(s.criteria_json || '[]');
            const scores = JSON.parse(s.scores_json || '{}');
            sessionCriteria = criteria;

            let scoresHtml = '';
            if (criteria.length > 0) {
                // Dynamic criteria-based score cards
                const colSize = criteria.length <= 4 ? 3 : (criteria.length <= 6 ? 2 : 2);
                let nonBonusIdx = 0;
                criteria.forEach((c, i) => {
                    const score = parseFloat(scores[c.key] || 0).toFixed(1);
                    const max = parseFloat(c.max_points);
                    const pct = max > 0 ? parseFloat(scores[c.key] || 0) / max : 0;
                    const cls = pct >= 0.9 ? 'score-high' : (pct >= 0.7 ? 'score-mid' : 'score-low');
                    const color = getCriteriaColor(c.is_bonus ? 0 : nonBonusIdx, c.is_bonus);
                    if (!c.is_bonus) nonBonusIdx++;

                    const bonusStyle = c.is_bonus ? `-webkit-text-fill-color:${color.label}` : '';
                    const prefix = c.is_bonus ? '+' : '';
                    // Short label: use first part before ' - ' or truncate
                    const shortLabel = c.label.length > 25 ? c.label.substring(0, 22) + '...' : c.label;
                    scoresHtml += `
                        <div class="col-4 col-md-${colSize}">
                            <div class="stat-card" title="${escapeHtml(c.label)}">
                                <div class="stat-value" style="font-size:1.3rem;${bonusStyle}">${prefix}${score}</div>
                                <div class="stat-label" style="font-size:0.7rem">${escapeHtml(shortLabel)} /${max}</div>
                            </div>
                        </div>`;
                });
            } else {
                // Legacy fallback
                scoresHtml = `
                    <div class="col-4 col-md-2"><div class="stat-card"><div class="stat-value" style="font-size:1.3rem">${parseFloat(s.part_a).toFixed(1)}</div><div class="stat-label">Part A /15</div></div></div>
                    <div class="col-4 col-md-2"><div class="stat-card"><div class="stat-value" style="font-size:1.3rem">${parseFloat(s.part_b).toFixed(1)}</div><div class="stat-label">Part B /15</div></div></div>
                    <div class="col-4 col-md-2"><div class="stat-card"><div class="stat-value" style="font-size:1.3rem">${parseFloat(s.part_c).toFixed(1)}</div><div class="stat-label">Part C /30</div></div></div>
                    <div class="col-4 col-md-2"><div class="stat-card"><div class="stat-value" style="font-size:1.3rem">${parseFloat(s.part_d).toFixed(1)}</div><div class="stat-label">Part D /10</div></div></div>
                    <div class="col-4 col-md-2"><div class="stat-card"><div class="stat-value" style="font-size:1.3rem">${parseFloat(s.part_e).toFixed(1)}</div><div class="stat-label">Part E /30</div></div></div>
                    <div class="col-4 col-md-2"><div class="stat-card"><div class="stat-value" style="font-size:1.3rem;-webkit-text-fill-color:#22c55e">+${parseFloat(s.bonus).toFixed(1)}</div><div class="stat-label">Bonus /10</div></div></div>
                `;
            }

            document.getElementById('feedbackScores').innerHTML = scoresHtml;
            document.getElementById('feedbackText').innerHTML = formatFeedback(s.feedback || 'No feedback available.', criteria);

            if (parseInt(s.has_venv) === 1) {
                document.getElementById('feedbackVenv').classList.remove('d-none');
                document.getElementById('feedbackVenvLoc').textContent = s.venv_location;
            } else {
                document.getElementById('feedbackVenv').classList.add('d-none');
            }

            new bootstrap.Modal(document.getElementById('feedbackModal')).show();
        } catch (err) {
            showToast('Error loading feedback: ' + err.message, 'error');
        }
    }

    // Re-grade student
    async function regradeStudent() {
        if (!currentStudentId) return;
        const btn = document.getElementById('btnRegrade');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Re-grading...';

        const fd = new FormData();
        fd.append('action', 'regrade_student');
        fd.append('submission_id', currentStudentId);

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                showToast('Student re-graded successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
                loadResults(currentSessionId);
            } else {
                showToast('Re-grade failed: ' + (data.error || 'Unknown error'), 'error');
            }
        } catch (err) {
            showToast('Error: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Re-grade';
        }
    }

    // Delete session
    async function deleteSession(sessionId) {
        if (!confirm('Delete this grading session? This cannot be undone.')) return;

        const fd = new FormData();
        fd.append('action', 'delete_session');
        fd.append('session_id', sessionId);

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showToast('Session deleted', 'success');
                loadSessions();
            }
        } catch (err) {
            showToast('Error: ' + err.message, 'error');
        }
    }

    // Export CSV
    function exportCSV() {
        if (currentSessionId) {
            window.location.href = `api.php?action=export_csv&session_id=${currentSessionId}`;
        }
    }

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        showPage('home');
    });
</script>
</body>
</html>
