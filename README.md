# fiqtest

A self-hosted online exam platform built so I don't have to manually grade my students' coding assignments anymore.

Supports multiple question types, automated grading via Judge0, real-time exam monitoring, and anti-cheat proctoring — all manageable from a single admin panel.

---

## Features

### Question Bank
- **6 Question Types** — Coding, Multiple Choice, Multiple Select, True/False, Fill in the Blank, Essay
- **Difficulty Levels** — Easy, Medium, Hard with configurable weights
- **Rich Description Editor** — Markdown with live preview (EasyMDE), LaTeX (KaTeX), and Mermaid.js diagrams
- **Image Upload** — Embed images directly in question descriptions
- **Tagging System** — Tag questions for filtering and exam pool management
- **Duplicate Question** — Clone any question as a starting point
- **Question Stats** — Per-question analytics: % correct, average score, per-student breakdown
- **Preview Mode** — Admin can preview exactly how a question looks to students
- **Bulk Import** — Upload questions via Excel (.xlsx) for all question types
- **Excel Template** — Downloadable template with example rows for every question type

### Exam Management
- **Question Pool** — Randomly assign questions per difficulty from the question bank
- **Tag-based Filtering** — Restrict the pool to questions with specific tags
- **Shuffle Options** — MC/MS answer options are shuffled differently per student (deterministic per session)
- **Flexible Scheduling** — Set open/close times and duration in minutes
- **Publish / Close Controls** — Validate question bank before publishing
- **Copy Exam Link** — One-click copy with toast feedback

### Automated Grading
- **Coding** — Runs test cases via Judge0, scores by pass rate
- **Multiple Choice / Multiple Select** — Exact match grading
- **True/False** — Boolean comparison
- **Fill in the Blank** — Case-insensitive string match
- **Essay** — Manual grading by admin with score + feedback

### Proctoring & Anti-Cheat
- **Tab Switch Detection** — Count and warn on tab switches; auto-disqualify above threshold
- **Inactivity Detection** — Warn and disqualify on prolonged inactivity
- **Fullscreen Enforcement** — Warn students who exit fullscreen
- **Disable DevTools / Inspect** — Optional per-exam setting
- **IP Address Logging** — Track IP per attempt
- **Disqualification Log** — Reason and timestamp recorded per attempt

### Real-Time Monitoring
- **Live Exam Monitor** — Per-exam view of all active students: progress, time remaining, tab switches, last activity, IP
- **Live Dashboard Feed** — Global real-time feed across all ongoing exams: current question, answered count, time remaining
- **Auto-refresh** — Polling every 5–10 seconds, pauses when tab is hidden

### Reporting & Analytics
- **Score Distribution Chart** — Bucketed bar chart per exam (0–49, 50–59, 60–69, 70–79, 80–89, 90–100)
- **Grade Reports** — Per-offering and per-period grade tables (student × exam matrix)
- **Student History** — Exam history across all courses per student
- **Export to Excel** — Download attempt results with score, duration, tab switches, disqualification status
- **Export to CSV** — Grade report export per course offering

### Academic Management
- **Academic Periods** — Manage semesters / academic years
- **Courses** — Course catalog
- **Course Offerings** — Bind courses to periods and class names
- **Student Enrollment** — Enroll students per offering
- **Bulk Student Import** — Upload students via Excel or CSV

### Student Experience
- **NIM-based Login** — No account needed; students enter their student ID to start
- **Workspace** — Split-panel view: question on the left, editor/answer on the right
- **Code Editor** — Syntax-highlighted editor with Run Code support
- **Auto-save** — Answers saved periodically and on change
- **Hint System** — Optional per-question hints with configurable per-exam limits
- **Result Page** — Score summary with per-question breakdown after submission
- **PDF Result** — Downloadable result PDF

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13, PHP 8.4 |
| Database | MySQL 8 |
| Frontend | Blade, Tailwind CSS (CDN), Alpine.js v3, Font Awesome 6 |
| Rich Text | EasyMDE (Markdown editor), KaTeX (LaTeX), Mermaid.js |
| Code Execution | Judge0 CE (self-hosted) |
| Web Server | Nginx |
| Containerization | Docker + Docker Compose |

---

## Local Development

### Requirements
- PHP 8.4+
- Composer
- Node.js 18+
- MySQL 8

### Setup

```bash
git clone https://github.com/fiqgant/fiqtest.git
cd fiqtest

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure `.env`:
```env
DB_HOST=127.0.0.1
DB_DATABASE=coding_exam_platform
DB_USERNAME=root
DB_PASSWORD=your_password
```

```bash
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

Admin panel: `http://localhost:8000/admin`

**Default credentials:**
- Email: `admin@example.com`
- Password: `password`

---

## VPS Deployment (Docker)

### Requirements
- Docker Engine 24+
- Docker Compose v2
- Port 80 open

### Steps

```bash
git clone https://github.com/fiqgant/fiqtest.git
cd fiqtest
cp .env.example .env
nano .env
```

Required `.env` values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-server-ip

DB_HOST=mysql
DB_DATABASE=coding_exam_platform
DB_USERNAME=appuser
DB_PASSWORD=use_a_strong_password
DB_ROOT_PASSWORD=use_a_strong_root_password

JUDGE0_URL=http://judge0:2358
JUDGE0_TIMEOUT=30
```

> `DB_USERNAME` must NOT be `root` when using Docker.

```bash
docker compose up -d --build
docker compose exec app php artisan db:seed --class=AdminSeeder --force
```

Visit `http://your-server-ip` — admin panel at `/admin`.

**Default credentials:**
- Email: `admin@example.com`
- Password: `password`

### Updating

```bash
git pull
docker compose up -d --build app
docker compose exec app php artisan migrate --force
```

---

## Judge0 Configuration

Configure Judge0 from **Admin Panel → Settings → Judge0** — not via `.env`.

| Field | Description |
|-------|-------------|
| Base URL | URL of Judge0 instance, e.g. `http://judge0:2358` |
| API Key | Leave blank for self-hosted; fill in for RapidAPI |
| API Host | Leave blank for self-hosted; fill in for RapidAPI |
| Timeout | Execution time limit in seconds (default: 30) |

Click **Test Connection** to verify the setup.

---

## Bulk Question Import

Download the Excel template from **Admin → Questions → Bulk Import → Download Template**.

The template contains one sheet (`Questions`) with 14 columns and example rows for every question type:

| Column | Description |
|--------|-------------|
| `title` | Short question title |
| `type` | `coding` / `multiple_choice` / `multiple_select` / `true_false` / `fill_in_blank` / `essay` |
| `difficulty` | `easy` / `medium` / `hard` |
| `description` | Full question text (Markdown supported) |
| `default_weight` | Numeric score weight |
| `starter_code` | Coding only — initial code shown to student |
| `hint` | Optional hint |
| `reference_solution` | Coding only — correct solution |
| `tags` | Comma-separated tags, e.g. `python,loops` |
| `test_cases` | Coding only — format: `input\|\|output\|\|is_hidden`, separated by `;` |
| `options` | MC/MS only — format: `*Correct\|Wrong\|Wrong`. Prefix correct with `*` |
| `fill_blank_answer` | Fill in the blank only |
| `true_false_answer` | `true` or `false` |
| `language` | Coding only — e.g. `python3`, `javascript`, `cpp` |

---

## Directory Structure

```
app/
├── Http/Controllers/Admin/
│   ├── DashboardController.php      # Dashboard + live feed
│   ├── ExamController.php           # Exam CRUD, monitor, export, attempts
│   ├── QuestionController.php       # Question CRUD, preview, duplicate, stats
│   ├── BulkQuestionController.php   # Bulk import via Excel
│   ├── BulkStudentController.php    # Bulk import via Excel/CSV
│   ├── CourseOfferingController.php # Offerings + enrollment
│   ├── ReportController.php         # Grade reports + CSV export
│   └── SystemSettingController.php  # Judge0 configuration
├── Models/
│   ├── Exam.php, Attempt.php, AttemptQuestion.php
│   ├── Question.php, QuestionOption.php, QuestionTag.php
│   ├── Student.php, Course.php, CourseOffering.php, AcademicPeriod.php
│   └── Submission.php, SystemSetting.php
└── Services/
    ├── Judge0Service.php       # Code execution via Judge0
    ├── GradingService.php      # Auto-grade all question types
    ├── GradeReportService.php  # Grade aggregation + CSV export
    ├── ExamAccessService.php   # Attempt creation + access validation
    └── QuestionAssigner.php    # Random question selection by difficulty

resources/views/
├── admin/
│   ├── dashboard.blade.php
│   ├── exams/          # index, form, attempts, attempt-detail, monitor, question-pool
│   ├── questions/      # index, form, preview, stats, bulk-import, bulk-preview
│   ├── reports/        # offering, period, student
│   └── settings/       # judge0
└── exam/
    ├── instructions.blade.php
    ├── workspace.blade.php
    ├── result.blade.php
    └── result-pdf.blade.php
```

---

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_ENV` | `local` | Set to `production` on VPS |
| `APP_DEBUG` | `true` | Set to `false` in production |
| `APP_URL` | `http://localhost` | Public URL |
| `APP_TIMEZONE` | `Asia/Jakarta` | Application timezone |
| `DB_CONNECTION` | `mysql` | Database driver |
| `DB_HOST` | `127.0.0.1` | Use `mysql` with Docker |
| `DB_USERNAME` | — | Must not be `root` with Docker |
| `DB_DATABASE` | `coding_exam_platform` | Database name |
| `JUDGE0_URL` | `http://localhost:2358` | Judge0 base URL |
| `JUDGE0_TIMEOUT` | `30` | Default execution timeout (seconds) |

---

## License

MIT
