# 📝 IT 103 Grading System

An automated grading system for **IT 103 Python Practical Exam** submissions. It combines a PHP/MySQL web application with an AI-powered grading engine (OpenAI GPT) and a standalone Python grading script to evaluate student code submissions against a detailed rubric.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.x-3776AB?logo=python&logoColor=white)
![OpenAI](https://img.shields.io/badge/OpenAI-GPT--5.2-412991?logo=openai&logoColor=white)

---

## 🚀 Features

- **Batch Grading** — Scan a folder of student submissions and grade them all in one session.
- **AI-Powered Evaluation** — Uses OpenAI GPT-5.2 to analyze student Python code and assign scores per rubric.
- **PDF-Based Grading Criteria** — Upload the exam instructions PDF; text is automatically extracted and used as the grading rubric.
- **Server-Side Folder Browser** — Browse and select the submissions folder directly from the web UI (no manual path typing needed).
- **Standalone Python Grader** — Offline deterministic grading script (`grade_submissions.py`) that produces an Excel report.
- **Detailed Rubric** — Grades across 5 parts (A–E) plus a bonus, totaling up to 110 points.
- **Editable Scores** — Manually adjust any individual score after AI grading via the web UI.
- **Color-Coded Feedback** — Per-part feedback displayed with syntax highlighting and color-coded labels for easy review.
- **CSV & Excel Export** — Export grading results to CSV (web app) or styled `.xlsx` (Python script).
- **Session Management** — Create, view, re-grade, and delete grading sessions.
- **venv Detection** — Automatically detects if a student mistakenly included their virtual environment folder.
- **Real-Time Progress** — Live progress bar and per-student status updates during batch grading.
- **Modern Dark UI** — Sleek dark-themed interface built with Bootstrap 5 and custom CSS.

---

## 📋 Exam: Python Practical Exam — Text Processing Pipeline

**Level:** Intermediate | **Time Limit:** 90–120 minutes | **Total Points:** 100 (110 with Bonus)

Students build a modular, menu-driven **Text Processing Pipeline** using virtual environments, external packages, and modular programming techniques. The program is strictly terminal-based and must follow a prescribed folder structure.

### Required Project Structure (Student Submission)

```
python_exam_strings/
├── app.py
├── utils/
│   ├── __init__.py
│   ├── text_tools.py
│   └── validators.py
├── outputs/
│   └── results.txt
└── README.md
```

### Grading Rubric

| Part | Component | Points |
|------|-----------|:------:|
| **A** | Virtual Environment Setup | **15** |
| **B** | Package Requirement & README | **15** |
| **C** | String Utilities Module | **30** |
| **D** | Validators Module | **10** |
| **E** | Main App Menu Program | **30** |
| | **Total Standard Points** | **100** |
| **Bonus** | File Processing | **+10** |
| | **Maximum Possible Score** | **110** |

---

### Part A: Virtual Environment Setup (15 Points)

Establish an isolated environment for project dependencies.

1. **Create the Environment** — Run `python -m venv venv` inside the project folder.
2. **Activate the Environment** — Run `venv\Scripts\activate`.
3. **Verify the Environment** — Confirm correct Python/pip versions are active.

> **Grading Basis:** The instructor must be able to successfully run the application while the venv is active.
>
> ⚠️ **Deduction:** −5 points if the `venv/` folder is included in the submission zip.

---

### Part B: Package Requirement & README (15 Points)

Install at least one external package (e.g., `rich`, `regex`, `python-slugify`, `colorama`, `pyfiglet`, `tabulate`).

**README.md must document:**
1. The **name** of the package installed.
2. The **specific command** used to install it (e.g., `pip install rich`).
3. The **rationale** for its usage in the application.

---

### Part C: String Utilities Module (30 Points)

Create `utils/text_tools.py` with the following four functions (≈7.5 pts each):

| Function | Description |
|----------|-------------|
| `clean_text(text: str) -> str` | Strip leading/trailing whitespace, consolidate multiple spaces into one, remove spaces before punctuation (`, . ! ? : ;`). Example: `"Hello , world ! "` → `"Hello, world!"` |
| `word_stats(text: str) -> dict` | Return a dict with: `char_count`, `char_count_no_spaces`, `word_count`, `sentence_count` (based on `. ! ?`), `longest_word` (ignoring punctuation). |
| `mask_sensitive(text: str) -> str` | Mask email addresses to `***@***`. Mask phone numbers (≥10 digits, allowing hyphens/spaces) to `**********`. Example: `"Email a@b.com or call 0917-123-4567"` → `"Email ***@*** or call **********"` |
| `make_slug(text: str) -> str` | Convert to lowercase, replace spaces with hyphens, remove all punctuation, collapse consecutive hyphens. Example: `"Hello, World Python!!"` → `"hello-world-python"` |

---

### Part D: Validators Module (10 Points)

Create `utils/validators.py` with defensive input handling functions:

| Function | Description |
|----------|-------------|
| `require_non_empty(prompt: str) -> str` | Prompt the user continuously until a non-empty string is provided. |
| `require_menu_choice(prompt: str, choices: list[str]) -> str` | Prompt the user continuously until they input a value that exists within the provided choices list (e.g., `["1", "2", "3", "4", "5", "6"]`). |

---

### Part E: Main App Menu Program (30 Points)

Create the primary execution file `app.py` that orchestrates the modules from Parts C and D.

1. **Display a Looping Menu** (8 pts):
   ```
   1. Clean Text
   2. Show Text Statistics
   3. Mask Sensitive Data
   4. Make Slug
   5. Process Full Pipeline (Executes 1 through 4)
   6. Exit
   ```

2. **Process Inputs** (8 pts): For each option, use `require_non_empty()` to gather user input and pass it through the appropriate function from `utils/text_tools.py`.

3. **Log to `outputs/results.txt`** (7 pts): Append the results of every operation with timestamps:
   ```
   [2026-02-21 06:15:22]
   Operation: Clean Text
   Input: "Hello   , world ! "
   Output: "Hello, world!"
   ----------------------------------------
   ```

4. **Integrate External Package** (7 pts): Actively use the installed package (e.g., `rich` for styled/colored menus or formatting statistics into a console table).

---

### Bonus: File Processing (+10 Points)

Add a menu option that allows the user to specify a path to a `.txt` file. Read the contents and pass them through the processing pipeline.

---

### Submission Guidelines

Students must verify before submitting:
- ✅ `python app.py` runs flawlessly from the terminal
- ✅ Project root folder is compressed into a `.zip` file
- ✅ `README.md` includes required package documentation
- ✅ `venv/` folder is **deleted** before zipping (−5 pts if included)
- ✅ `outputs/results.txt` exists with sample output logs
- ✅ Zip file named: `section_lastname_prelim_prac.zip` (e.g., `IT2A_Smith_prelim_prac.zip`)

---

## 📁 Project Structure

```
grading-system/
├── index.php                # Main web UI (single-page application)
├── api.php                  # REST API endpoints (scan, create session, grade, export, etc.)
├── config.php               # Database & OpenAI API configuration
├── grading_engine.php       # Core grading logic (scan submissions, build prompt, call API)
├── setup_db.php             # Database schema setup script
├── extract_pdf.py           # Python helper to extract text from PDF (uses PyMuPDF)
├── grade_submissions.py     # Standalone Python grading script (offline, produces Excel)
├── uploads/                 # Upload directory (stored exam instruction PDFs)
├── grading_output.txt       # Sample grading output log
├── IT103_Practical_Exam_Grades.xlsx  # Sample generated Excel report
├── Python Practical Exam Instructions.pdf  # Exam instructions document
└── README.md                # This file
```

---

## ⚙️ Prerequisites

- **XAMPP** (or any Apache + PHP 8.x + MySQL/MariaDB stack)
- **PHP Extensions**: `pdo_mysql`, `curl`, `json`
- **Python 3.x** (for the standalone grading script and PDF text extraction)
- **pip packages**: `openpyxl` (Excel export), `PyMuPDF` (PDF text extraction)
- **OpenAI API Key** (for the web-based AI grading)

---

## 🛠️ Installation & Setup

### 1. Clone / Copy the Project

Place the project folder in your web server's document root:

```
C:\xampp\htdocs\grading-system\
```

### 2. Configure the Application

Edit `config.php` and update the following values:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'grading_system');

define('OPENAI_API_KEY', 'your-openai-api-key');
define('OPENAI_MODEL', 'gpt-5.2-2025-12-11');
```

### 3. Set Up the Database

Start Apache and MySQL from the XAMPP Control Panel, then run:

```
http://localhost/grading-system/setup_db.php
```

This will create the `grading_system` database and the following tables:
- `grading_sessions` — Tracks grading sessions
- `student_submissions` — Stores per-student grades, feedback, and code
- `exam_criteria` — Stores exam part definitions

### 4. Install Python Dependencies (for PDF extraction & standalone script)

```bash
pip install openpyxl PyMuPDF
```

### 5. Access the Application

Open your browser and navigate to:

```
http://localhost/grading-system/
```

---

## 🎯 Usage

### Web Application (AI-Powered Grading)

1. **Select Folder** — Click **Browse** to navigate the server-side folder picker, or manually enter the path to the folder containing student submission directories.
2. **Upload Exam Instructions** — Upload the exam instructions PDF (required). The system extracts the text automatically and uses it as the grading criteria.
3. **Scan Submissions** — The system scans and lists all detected student folders.
4. **Create Session** — Optionally name the session, then start grading.
5. **Start Grading** — The system grades each student one by one using the AI, showing real-time progress.
6. **Review Results** — View the full results table with scores, remarks, and color-coded per-part feedback.
7. **Edit Scores** — Click any score cell to manually adjust grades.
8. **Export** — Download results as a CSV file.

### Standalone Python Script

1. Edit the `BASE` path in `grade_submissions.py` to point to your submissions folder.
2. Run the script:

```bash
python grade_submissions.py
```

3. The script will:
   - Scan all student submission folders
   - Analyze code structure, functions, and patterns
   - Assign deterministic scores based on the rubric
   - Generate a styled Excel file (`IT103_Practical_Exam_Grades.xlsx`)
   - Print a summary to the console

---

## 🔌 API Endpoints

All API calls go through `api.php` using the `action` parameter:

| Action | Method | Description |
|--------|--------|-------------|
| `scan_folder` | POST | Scan a directory for student submissions |
| `upload_pdf` | POST | Upload an exam instructions PDF and extract its text |
| `browse_folder` | GET | Browse server-side directories (folder picker) |
| `create_session` | POST | Create a new grading session |
| `grade_next` | POST | Grade the next pending student in a session |
| `get_sessions` | GET | List all grading sessions |
| `get_session` | GET | Get session details with all submissions |
| `get_student` | GET | Get a single student's full details |
| `update_grade` | POST | Manually update a specific score field |
| `regrade_student` | POST | Re-grade a specific student submission |
| `delete_session` | POST | Delete a grading session and all its data |
| `export_csv` | GET | Download session results as CSV |

---

## 📊 Grading Remarks Scale

| Score Range | Remarks |
|:-----------:|---------|
| 96–100 | Outstanding |
| 90–95 | Excellent |
| 80–89 | Very Good |
| 75–79 | Passed |
| 60–74 | Needs Improvement |
| 1–59 | Failed |
| 0 | No Submission |

---

## 🧰 Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3, JavaScript, Bootstrap 5.3, Bootstrap Icons, Google Fonts (Inter) |
| **Backend** | PHP 8.x, PDO (MySQL) |
| **Database** | MySQL / MariaDB |
| **AI Engine** | OpenAI Chat Completions API (GPT-5.2) |
| **PDF Extraction** | Python 3, PyMuPDF (fitz) |
| **Offline Grader** | Python 3, openpyxl |
| **Server** | Apache (XAMPP) |

---

## 📝 License

This project is intended for educational use within the IT 103 course.

---

## 👤 Author

Developed for automating the grading of IT 103 Python Practical Exam submissions.