# fiqtest

A personal side project — built so I don't have to manually grade my students' coding assignments anymore.

Integrated with Judge0 as the code execution engine. Built with Laravel 13 and deployable via Docker.

## Features

- **Academic Management** — Academic periods, courses, and class offerings
- **Student Management** — Student records identified by NIM (student ID)
- **Coding Exams** — Create coding problems with test cases, scoring weights, and reference solutions
- **Automated Code Execution** — Integrated with Judge0 to run and grade student code submissions
- **Proctoring** — Tab switching detection, fullscreen enforcement, and configurable warning limits
- **Grade Reports** — Per-class grade reports with per-question breakdown and CSV export
- **Admin Panel** — Full dashboard for managing all entities
- **Judge0 Settings** — Configure URL, API key, and test the connection directly from the admin panel
- **Markdown Question Editor** — Write problem descriptions in Markdown with live preview (EasyMDE)
- **Rich Rendering for Students** — Question descriptions rendered with full Markdown, LaTeX (KaTeX), and Mermaid.js diagram support

## Tech Stack

- **Backend**: Laravel 13, PHP 8.4
- **Database**: MySQL 8
- **Frontend**: Blade, Tailwind CSS (CDN), Font Awesome 6 (CDN), EasyMDE, KaTeX, Mermaid.js
- **Code Execution**: Judge0 CE
- **Web Server**: Nginx
- **Containerization**: Docker + Docker Compose

---

## Local Installation (Development)

### Requirements

- PHP 8.4+
- Composer
- Node.js 18+
- MySQL 8
- Judge0 (optional for development)

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/fiqgant/fiqtest.git
cd fiqtest
```

**2. Install dependencies**
```bash
composer install
npm install
```

**3. Set up environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure database in `.env`**
```env
DB_HOST=127.0.0.1
DB_DATABASE=coding_exam_platform
DB_USERNAME=root
DB_PASSWORD=your_password
```

**5. Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

**6. Build assets**
```bash
npm run build
```

**7. Start the development server**
```bash
php artisan serve
```

Access the admin panel at `http://localhost:8000/admin`

**Default admin credentials:**
- Email: `admin@example.com`
- Password: `password`

---

## VPS Deployment (Docker)

### Requirements

- Docker Engine 24+
- Docker Compose v2
- Port 80 open

### Steps

**1. Install Docker on the VPS**
```bash
curl -fsSL https://get.docker.com | sh
apt install -y docker-compose-plugin
```

**2. Clone the repository**
```bash
git clone https://github.com/fiqgant/fiqtest.git
cd fiqtest
```

**3. Create the production `.env` file**
```bash
cp .env.example .env
nano .env
```

Update the following values:
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

> `DB_USERNAME` must NOT be `root` — use any other username.

**4. Start all services**
```bash
docker compose up -d --build
```

This will automatically:
- Build the Laravel app image
- Pull MySQL, Nginx, Judge0, Redis, and Postgres images
- Run all database migrations
- Cache config, routes, and views
- Start PHP-FPM

**5. Seed the admin account (first time only)**
```bash
docker compose exec app php artisan db:seed --class=AdminSeeder --force
```

**6. Open the app**

Visit `http://your-server-ip` in your browser.

**Default admin credentials:**
- Email: `admin@example.com`
- Password: `password`

**7. Configure Judge0**

Go to Admin Panel → Settings → Judge0, set the Base URL to `http://judge0:2358`, then click **Test Connection**.

### Updating the Application

```bash
git pull
docker compose up -d --build app
docker compose exec app php artisan migrate --force
```

---

## Directory Structure

```
app/
├── Http/Controllers/Admin/   # All admin controllers
├── Models/                   # Eloquent models
└── Services/
    └── Judge0Service.php     # Judge0 integration

resources/views/
├── admin/                    # Admin panel views
│   ├── layouts/app.blade.php # Main layout
│   ├── dashboard.blade.php
│   ├── settings/judge0.blade.php
│   └── reports/offering.blade.php
└── exam/                     # Student-facing exam views

database/migrations/          # All migrations
docker/
├── entrypoint.sh             # Container startup script
└── nginx.conf                # Nginx configuration
```

---

## Judge0 Configuration

Judge0 is configured through the admin panel, not directly in `.env`.

**Admin Panel → Settings → Judge0:**

| Field | Description |
|-------|-------------|
| Base URL | URL of the Judge0 instance, e.g. `http://judge0:2358` |
| API Host | Fill in if using a RapidAPI proxy, leave blank for self-hosted |
| Timeout | Execution time limit in seconds, default 30 |
| API Key | Fill in if using RapidAPI, leave blank for self-hosted |

After filling in the fields, click **Test Connection** to verify.

---

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_NAME` | `fiqtest` | Application name |
| `APP_ENV` | `local` | Set to `production` on VPS |
| `APP_DEBUG` | `true` | Set to `false` in production |
| `APP_URL` | `http://localhost` | Public URL of the application |
| `APP_TIMEZONE` | `Asia/Jakarta` | Application timezone |
| `DB_CONNECTION` | `mysql` | Database driver |
| `DB_HOST` | `127.0.0.1` | Database host (use `mysql` with Docker) |
| `DB_USERNAME` | — | Must not be `root` when using Docker |
| `DB_DATABASE` | `coding_exam_platform` | Database name |
| `JUDGE0_URL` | `http://localhost:2358` | Default Judge0 URL (can be changed from admin) |
| `JUDGE0_TIMEOUT` | `30` | Default Judge0 timeout |

---

## License

MIT
