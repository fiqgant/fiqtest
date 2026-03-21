# fiqtest — Online Exam Platform

A self-hosted online exam platform for academic institutions. Supports 6 question types (including coding with real code execution), automated proctoring, auto-grading, and real-time monitoring.

> 🇮🇩 Also available in Bahasa Indonesia: [README.id.md](README.id.md)

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Installation & Setup](#installation--setup)
- [Admin Access](#admin-access)
- [Admin Features](#admin-features)
  - [1. Academic Management](#1-academic-management)
  - [2. Student Management](#2-student-management)
  - [3. Question Bank](#3-question-bank)
  - [4. Exam Management](#4-exam-management)
  - [5. Exam Settings](#5-exam-settings)
  - [6. Live Monitor](#6-live-monitor)
  - [7. Essay Grading](#7-essay-grading)
  - [8. Reset Attempt](#8-reset-attempt)
  - [9. Export Grades](#9-export-grades)
  - [10. Reports & Analytics](#10-reports--analytics)
  - [11. System Settings](#11-system-settings)
- [Student Features](#student-features)
  - [1. Home Page](#1-home-page)
  - [2. Starting an Exam](#2-starting-an-exam)
  - [3. Exam Workspace](#3-exam-workspace)
  - [4. Question Types](#4-question-types)
  - [5. Code Execution](#5-code-execution)
  - [6. Hints](#6-hints)
  - [7. Exam Results](#7-exam-results)
- [Proctoring & Academic Integrity](#proctoring--academic-integrity)
- [Grading & Scoring](#grading--scoring)
- [Deployment (VPS + Docker)](#deployment-vps--docker)
- [URL Reference](#url-reference)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.4 |
| Frontend | Tailwind CSS v4, Alpine.js, Monaco Editor |
| Database | MySQL 8 |
| Code Execution | Judge0 CE |
| PDF | Barryvdh/DomPDF |
| Excel | PhpOffice/PhpSpreadsheet |
| Device Detection | Jenssegers/Agent |
| Welcome Animation | Three.js |
| Math Rendering | KaTeX |
| Containerization | Docker + Docker Compose |

---

## Installation & Setup

### Requirements
- Docker & Docker Compose
- Git

### Steps

```bash
# Clone the repo
git clone https://github.com/fiqgant/fiqtest.git
cd fiqtest

# Copy environment file
cp .env.example .env

# Fill in required variables in .env:
# APP_KEY=
# DB_PASSWORD=
# JUDGE0_URL= (optional, for code execution)

# Build and start all containers
docker compose up -d --build
```

The entrypoint automatically runs:
- `php artisan migrate`
- `php artisan config:cache`
- `php artisan storage:link`

Access the app at `http://localhost` or your configured domain.

### Creating the First Admin

```bash
docker compose exec app php artisan tinker
```

```php
\App\Models\Admin::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('password'),
]);
```

---

## Admin Access

| URL | Description |
|---|---|
| `/admin/login` | Admin login page |
| `/admin/dashboard` | Main dashboard |

---

## Admin Features

### 1. Academic Management

Data hierarchy: **Academic Period → Course → Course Offering → Enrollment**

#### Academic Periods
**URL:** `/admin/academic-periods`

- Create, edit, delete academic periods (e.g. Even Semester 2025/2026)
- Mark one period as **active**
- Active period is used as the default filter across the admin panel

#### Courses
**URL:** `/admin/courses`

- Create, edit, delete courses
- Each course has a name and code (e.g. `PWEB`, `ALGO`)

#### Course Offerings (Classes)
**URL:** `/admin/course-offerings`

- Combine a course + period + class name (e.g. PWEB — Even Semester 2025 — Class A)
- Each offering has its own question bank and exams
- Manage student enrollment per offering

#### Student Enrollment
**URL:** `/admin/course-offerings/{id}/enrollment`

- Add or remove students from a class
- Only enrolled students can take exams under that offering

---

### 2. Student Management

**URL:** `/admin/students`

#### Add Student Manually
- Fill in NIM (student ID), name, and email (optional)
- NIM must be unique across the system

#### Bulk Import
**URL:** `/admin/students/bulk/import`

Two methods:

1. **Upload Excel (.xlsx)** — columns: `name`, `nim`, `email` (optional). Header row is ignored.
2. **Copy-paste CSV** — format: `name,nim` or `name,nim,email` — one student per line

Process:
1. Upload / paste data → click Preview
2. Review the parsed results
3. Confirm → data is saved
4. Duplicate NIMs are automatically skipped

#### Bulk Delete
Check multiple students in the table → **Bulk Delete** button.

---

### 3. Question Bank

**URL:** `/admin/questions`

#### Filters & Search
- Filter by: Course Offering, Question Type, Difficulty
- Free-text search by question title

#### Creating a Question
**URL:** `/admin/questions/create`

Available question types:

| Type | Auto-Graded | Description |
|---|---|---|
| **Coding** | Yes | Student code is executed against test cases |
| **Multiple Choice** | Yes | One correct answer |
| **Multiple Select** | Yes | Multiple correct answers |
| **True / False** | Yes | True or False |
| **Fill in Blank** | Yes | Fill in the missing text |
| **Essay** | No | Manually graded by admin |

Available fields per question:
- **Title** and **Description** (supports Markdown + KaTeX for math formulas)
- **Difficulty** — Easy / Medium / Hard
- **Default Weight** (points)
- **Tags** — used to filter the question pool when creating an exam
- **Hint** — optional hint for students
- For **Coding**: programming language, starter code, reference solution, test cases (visible/hidden, input/output)
- For **MC/MS**: list of answer options, mark correct ones
- For **TF**: select the correct answer (True/False)
- For **Fill Blank**: enter the correct answer

#### Preview Question
**URL:** `/admin/questions/{id}/preview`

See the question from a student's perspective. For coding questions, you can run code directly.

#### Duplicate Question
**Duplicate** button on the question list or detail page → creates a new question with the same data, ready to edit.

#### Question Statistics
**URL:** `/admin/questions/{id}/stats`

- How many times the question has been used in exams
- Average student score
- Pass rate per test case (for coding questions)

#### Bulk Import Questions
**URL:** `/admin/questions/bulk/import`

1. Download the Excel template at `/admin/questions/bulk/template`
2. Fill in questions following the template format
3. Upload → Preview → Confirm import

#### Bulk Delete Questions
Check multiple questions → **Bulk Delete** button.

#### Question Tags
**URL:** `/admin/question-tags`

- Create tags to categorize questions (e.g. `array`, `recursion`, `OOP`)
- Tags are used as pool filters when setting up an exam

---

### 4. Exam Management

**URL:** `/admin/exams`

#### Creating a New Exam
**URL:** `/admin/exams/create`

1. Fill in all exam settings (see [Exam Settings](#5-exam-settings))
2. Save as **Draft**
3. Check the question pool at `/admin/exams/{id}/question-pool` — make sure there are enough questions
4. Once ready, click **Publish**

#### Exam Status

| Status | Description |
|---|---|
| **Draft** | Not visible to students, can be freely edited |
| **Published** | Active — students can access it within the open/close window |
| **Closed** | No longer accessible, all data is preserved |

#### Publishing an Exam
The system automatically validates:
- Enough easy/medium/hard questions are available in the bank based on the configured distribution
- If insufficient, an error message shows exactly how many are missing per difficulty

#### Question Pool
**URL:** `/admin/exams/{id}/question-pool`

Preview all questions eligible for this exam based on the configured tag filter and difficulty distribution.

---

### 5. Exam Settings

All settings are available in the create/edit exam form.

#### Schedule & Duration

| Setting | Description |
|---|---|
| **Opens At** | Date & time students can start accessing the exam |
| **Closes At** | Deadline — no new attempts allowed after this |
| **Duration (minutes)** | Time limit per student (1–600 minutes) |
| **Show Score Immediately** | Show results right after the student submits |

#### Question Distribution & Weights

| Setting | Description |
|---|---|
| **Easy Count** | Number of easy questions randomly drawn from the bank |
| **Medium Count** | Number of medium questions |
| **Hard Count** | Number of hard questions |
| **Easy Weight** | Points per easy question |
| **Medium Weight** | Points per medium question |
| **Hard Weight** | Points per hard question |
| **Question Pool Filter (Tags)** | Filter the pool by tags (OR logic). Leave empty = all questions |

Questions are drawn **randomly** from the bank at the moment a student starts — each student may receive a different set.

#### Hint System

| Setting | Description |
|---|---|
| **Enable Hints** | Show the Hint button in the exam workspace |
| **Max Hints per Question** | 0 = unlimited. Applies to all questions in this exam |

#### Proctoring & Security

| Setting | Description |
|---|---|
| **Max Tab Switches** | Auto-disqualify after N tab/app switches. 0 = disabled |
| **Warn at Switch #** | Show a warning on the Nth switch |
| **Inactivity Limit (seconds)** | Auto-disqualify after N seconds of no activity. 0 = disabled |
| **Inactivity Warning (seconds)** | Show a warning N seconds before the inactivity limit |
| **Disable DevTools & Right-Click** | Block F12, Ctrl+Shift+I, right-click, and view-source during the exam |
| **Detect Copy-Paste Activity** | Log all copy/cut/paste events including the text content |
| **Shuffle MC/MS Options** | Randomize answer option order — different for each student |

---

### 6. Live Monitor

**URL:** `/admin/exams/{id}/monitor`

Real-time view of students currently taking the exam. Auto-refreshes every 10 seconds.

#### Stats Bar

| Card | Description |
|---|---|
| **Active Now** | Number of students currently in progress |
| **Submitted** | Number who have submitted |
| **Total Enrolled** | Total enrolled in the class |
| **Last Updated** | Timestamp of the last refresh |

#### Active Students Table

| Column | Description |
|---|---|
| Student | Name and NIM |
| Started | Time the attempt started |
| Time Remaining | Countdown (turns red and pulses if < 5 minutes) |
| Progress | Questions answered / total + progress bar |
| Tab Switches | Switch count (amber ≥1, red & bold ≥3) |
| Last Activity | Timestamp of last mouse/keyboard activity |
| IP | Student's IP address |
| Device | Device type, browser + version, OS + version |
| Status | In Progress / Disqualified |

---

### 7. Essay Grading

**URL:** `/admin/exams/{id}/attempts/{attemptId}`

#### How to Grade an Essay
1. Open the student's attempt detail page
2. Scroll to the essay question
3. Enter a **Manual Score** (number, 0 up to the question's weight)
4. Enter **Feedback** (optional — shown to the student)
5. Click **Save Grade**

The attempt's total score is automatically recalculated after saving.

#### What's Visible on the Attempt Detail Page
- **Proctoring Log** — tab switches, disqualification status + reason, IP, device info
- **Copy-Paste Log** — full clipboard activity log (if the feature is enabled on the exam)
- **Per question** — student's answer; for coding: submitted code + output + test case pass/fail

---

### 8. Reset Attempt

**URL:** `/admin/exams/{id}/attempts`

If a student experiences a technical issue (connection loss, laptop crash, etc.), admin can reset their attempt.

**Steps:**
1. Open the exam's Attempts page
2. Find the student
3. Click the **Reset** button (red, in the Actions column)
4. Confirm the dialog

The old attempt and all its answers are permanently deleted. The student can start the exam fresh.

> **Warning:** This is irreversible — all previous attempt data is lost.

---

### 9. Export Grades

**URL:** `/admin/exams/{id}/export` — or the **Export** button on the Attempts page.

Downloads an Excel file containing:
- NIM and student name
- Total score and percentage
- Tab switch count
- Disqualification status + reason

---

### 10. Reports & Analytics

**URL:** `/admin/reports`

#### Report by Course Offering
**URL:** `/admin/reports/offering/{id}`

- Table of all exams in the class with statistics
- Per-student scores for each exam
- Average scores, participant counts
- Excel export button

#### Report by Academic Period
**URL:** `/admin/reports/period/{id}`

- Overview of all classes in a semester
- Cross-class statistical comparison

#### Report by Student
**URL:** `/admin/reports/student/{id}`

- Full attempt history for a student
- Score details per exam

---

### 11. System Settings

#### Judge0 API
**URL:** `/admin/settings/judge0`

Configure the code execution engine:
- **Judge0 URL** — URL of your Judge0 instance (self-hosted or cloud)
- **API Host** / **API Key** — If using RapidAPI
- **Timeout** — Execution time limit (seconds)
- **Test Connection** button — Verify the Judge0 connection

#### Admin Profile
**URL:** `/admin/profile`

- Update name and email
- Change password

#### Dark Mode
Toggle in the admin sidebar. Preference is saved in `localStorage` — persists across sessions.

---

## Student Features

### 1. Home Page

**URL:** `/`

- Lists all currently active exams (Published status, within open/close window)
- Each exam card shows: exam name, course, duration, closing time
- Three.js animated background

---

### 2. Starting an Exam

**URL:** `/exam/{slug}`

**Steps:**
1. The instructions page shows: exam name, duration, number of questions, active proctoring rules
2. Enter **NIM** (student ID) to verify identity
3. The system checks:
   - NIM is enrolled in the exam's class
   - No active attempt exists (or previous attempt was reset by admin)
   - Exam is within its open/close window
4. Click **Start Exam** → enters the workspace

---

### 3. Exam Workspace

The interface is split into two panels:
- **Left Panel:** Question navigator — lists all questions with answered/unanswered indicators and difficulty color-coding
- **Right/Main Panel:** Answer area + timer at the top

#### Timer
- Real-time countdown in the top toolbar
- Turns red and pulses when less than 5 minutes remain
- Auto-submits when time runs out

#### Autosave
- Answers are saved automatically at regular intervals
- "Saving..." and "Saved ✓" indicators in the UI

#### Submitting
- **Submit** button in the toolbar — confirmation dialog appears before submitting
- After submission, redirected to a confirmation page or directly to results (depends on settings)

---

### 4. Question Types

#### Coding
- Monaco Editor with full syntax highlighting
- Starter code loaded automatically from the question settings
- Programming language set per question
- **Run** button to test code against visible test cases
- Shows output, error messages, and execution time

#### Multiple Choice
- Select one answer from a list of options
- Option order can be shuffled (if enabled by admin)

#### Multiple Select
- Check all correct answers
- Option order can be shuffled

#### True / False
- Two buttons: **True** and **False**

#### Fill in Blank
- Free-text input

#### Essay
- Large textarea for long-form answers
- No character limit

---

### 5. Code Execution

1. Write code in the Monaco Editor
2. Click **Run**
3. Code is sent to Judge0 for execution
4. Results appear: output, errors, execution time
5. Pass/fail status is shown per visible test case

> **Note:** Only *visible test cases* can be run by students. *Hidden test cases* are used only for final grading on submission.

---

### 6. Hints

If the admin has enabled hints on the exam:
1. A **Hint** button appears on each question that has a hint configured
2. Click it → the hint text is revealed
3. Hint usage is recorded (does not affect the score)
4. If a limit is set (e.g. max 2 hints per question), the button locks after the limit is reached

---

### 7. Exam Results

If the admin enabled **Show Score Immediately**:

**URL:** `/attempt/{id}/result`

Displays:
- Total score and percentage
- Breakdown per question: score earned vs maximum
- For coding: detailed test case pass/fail
- For essay: admin feedback (if already graded)
- **Download PDF** button to save the result

---

## Proctoring & Academic Integrity

### Tab / App Switch Detection
- Detects every time the browser window loses focus
- A warning popup appears when the configured warning threshold is reached
- Auto-disqualifies when the maximum switch count is exceeded
- Disqualification reason and timestamp are recorded
- Visible in: live monitor, attempts list, attempt detail

### Inactivity Detection
- Monitors mouse and keyboard activity in real-time
- Warning appears N seconds before the inactivity limit
- Auto-disqualifies if no activity occurs before the limit expires

### Copy-Paste Detection
When enabled on an exam (**Detect Copy-Paste Activity** toggle):
- Detects: `Ctrl+C`, `Ctrl+X`, `Ctrl+V`, right-click copy/paste
- Records: timestamp, event type (copy/cut/paste), **the actual text content** (up to 2,000 characters)
- Logs are stored and visible to admins on the attempt detail page
- Students receive no notification that this is being monitored

### DevTools / Inspect Block
When enabled (**Disable DevTools & Right-Click** toggle):

| Blocked | Shortcut |
|---|---|
| DevTools | F12, Ctrl+Shift+I |
| Console | Ctrl+Shift+J |
| Inspect Element | Ctrl+Shift+C |
| Right-click | Context menu |
| View Source | Ctrl+U |
| Print | Ctrl+P, Ctrl+Shift+P |
| Save | Ctrl+S |

### Device & IP Tracking
Automatically recorded when an attempt is created (no configuration needed):
- Student's IP address
- Full User Agent string
- Browser + version (parsed)
- OS + version (parsed)
- Device type: Desktop / Mobile / Tablet

Visible in: attempt detail (Proctoring Log section) and live monitor (Device column).

### Shuffle Options
When enabled, MC/MS answer option order is randomized per student — each student sees a different order for the same question.

### Enrollment Verification
Before starting an exam, the system verifies the student's NIM is enrolled in the course offering the exam belongs to.

---

## Grading & Scoring

### Auto-Grading (Runs Automatically on Submit)

| Question Type | How It's Graded |
|---|---|
| **Coding** | Code executed against all test cases (including hidden). Score = (passed ÷ total) × weight |
| **Multiple Choice** | Matched against the single correct answer. Full points or 0 |
| **Multiple Select** | All correct options must be selected, no incorrect ones |
| **True / False** | Matched against the correct answer set in the question |
| **Fill in Blank** | String comparison against the correct answer |
| **Essay** | Not auto-graded — awaits manual scoring by admin |

### Manual Grading (Essay)
- Admin can assign any score (0 up to the question's weight)
- Optional feedback text is shown to the student
- Attempt total score is automatically recalculated after saving

### Score Calculation

```
Per-question score = (earned ÷ max) × difficulty weight

Total Score  = Σ (per-question scores)
Percentage   = (Total Score ÷ Max Score) × 100
```

### Difficulty Weights
Configured per exam:
- Easy questions → **easy_weight** points
- Medium questions → **medium_weight** points
- Hard questions → **hard_weight** points

---

## Deployment (VPS + Docker)

### Production Stack

| Container | Role |
|---|---|
| **app** | Laravel (PHP-FPM 8.4) |
| **nginx** | Web server (port 80) |
| **mysql** | Main database |
| **judge0** | Code execution engine |
| **judge0-worker** | Execution queue worker |
| **judge0-redis** | Queue backend for Judge0 |
| **judge0-postgres** | Judge0's own database |

### Deploying Updates

```bash
# On the VPS
cd /root/fiqtest
git pull

# Rebuild image and force recreate the container
docker compose up -d --build --force-recreate app

# Migrations run automatically via entrypoint.sh on container start
```

### Key Environment Variables (`.env`)

```env
APP_KEY=base64:...
APP_URL=https://yourdomain.com

DB_DATABASE=coding_exam_platform
DB_USERNAME=appuser
DB_PASSWORD=your_db_password
DB_ROOT_PASSWORD=rootpassword

# Optional: Judge0 via RapidAPI
JUDGE0_URL=https://judge0-ce.p.rapidapi.com
```

---

## URL Reference

### Admin Panel

| URL | Feature |
|---|---|
| `/admin/login` | Admin login |
| `/admin/dashboard` | Dashboard + live exam feed |
| `/admin/academic-periods` | Manage academic periods / semesters |
| `/admin/courses` | Manage courses |
| `/admin/course-offerings` | Manage class offerings |
| `/admin/course-offerings/{id}/enrollment` | Manage student enrollment per class |
| `/admin/students` | Manage students |
| `/admin/students/bulk/import` | Bulk import students (Excel/CSV) |
| `/admin/question-tags` | Manage question tags |
| `/admin/questions` | Question bank |
| `/admin/questions/create` | Create a new question |
| `/admin/questions/bulk/import` | Bulk import questions from Excel |
| `/admin/questions/bulk/template` | Download question Excel template |
| `/admin/questions/{id}` | Edit question |
| `/admin/questions/{id}/preview` | Preview question as a student |
| `/admin/questions/{id}/stats` | Question performance statistics |
| `/admin/exams` | List all exams |
| `/admin/exams/create` | Create a new exam |
| `/admin/exams/{id}/edit` | Edit exam |
| `/admin/exams/{id}/question-pool` | View exam question pool |
| `/admin/exams/{id}/monitor` | Real-time exam monitor |
| `/admin/exams/{id}/attempts` | Attempt list + score distribution histogram |
| `/admin/exams/{id}/attempts/{attemptId}` | Attempt detail + essay grading |
| `/admin/exams/{id}/export` | Export grades to Excel |
| `/admin/reports` | Reports home |
| `/admin/reports/offering/{id}` | Report by class offering |
| `/admin/reports/period/{id}` | Report by academic period |
| `/admin/reports/student/{id}` | Report by student |
| `/admin/settings/judge0` | Judge0 API configuration |
| `/admin/profile` | Admin profile & password change |

### Student

| URL | Feature |
|---|---|
| `/` | Home — list of active exams |
| `/exam/{slug}` | Exam instructions & NIM verification |
| `/attempt/{id}/workspace` | Exam workspace |
| `/attempt/{id}/submitted` | Post-submission confirmation |
| `/attempt/{id}/result` | Exam results (if Show Score Immediately is on) |
| `/attempt/{id}/result/pdf` | Download result as PDF |
