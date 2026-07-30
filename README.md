<div align="center">
    <img width="1200" height="475" alt="INNOW Banner" src="https://via.placeholder.com/1200x475/7f1d1d/ffffff?text=INNOW+Digital+Attendance+System" />
</div>

# INNOW — Digital Attendance System

A self-hosted, offline-capable attendance tracking system for INNOW facility staff. Clock in/out, manage leave requests, post announcements, and view real-time dashboard metrics — all running on a local MySQL database with no cloud dependencies.

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | PHP views, Tailwind CSS (CDN), Vanilla JavaScript, Lucide Icons |
| **Backend** | PHP 8.1+, custom PSR-4 autoloader |
| **Database** | MySQL 8.0 (`innow_db`) |
| **Server** | PHP Built-in Server (development) / Apache via XAMPP (production) |
| **Auth** | Session-based, HttpOnly cookies, CSRF protection |
| **Docs** | Markdown rendered via Marked.js |

## Features

- 📋 **Dashboard** — real-time metrics for total staff, onsite count, break count, offsite count, and today's logs
- ⏱️ **Clock In/Out** — one-click attendance with duplicate prevention (5-second cooldown)
- 📷 **QR Check-In** — scan-and-go using any phone camera and server-verified QR payloads
- 🏖️ **Leave Management** — submit leave requests, track status, and approve/decline as an admin
- 📢 **Announcements** — company-wide notices visible to all authenticated staff
- 👥 **Staff Directory** — admins can add and remove staff members
- 📊 **Attendance Logs** — full history of clock-in, clock-out, break, and manual entry events
- 🔒 **Security** — CSRF tokens, rate limiting, 30-minute session expiry, and role-based access control

## Prerequisites

- PHP 8.1+ (CLI or XAMPP)
- MySQL 8.0
- A modern web browser

## Local Setup

### 1. Clone or Extract

```bash
cd C:\Users\Liviwe Sandi\Downloads\INNOW(attendance-tracking-system)
```

### 2. Configure Environment

**Root `.env`** (database credentials):
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=innow_db
DB_USER=root
DB_PASS=
```

**`backend/.env`** (backend settings):
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_SECRET=innow_secret_key_2026
```

### 3. Import Database

```bash
mysql -u root -p innow_db < database.sql
```

Or use **phpMyAdmin**: create `innow_db`, then import `database.sql`.

### 4. Start the Server

```bash
cd frontend/public
php -S localhost:8000
```

### 5. Open in Browser

Navigate to: **http://localhost:8000**

**Sample accounts:**
- Admin: `thabo.m@innow.com` / `1001`
- Staff: `lerato.m@innow.com` / `1005`

## Project Structure

```
INNOW(attendance-tracking-system)/
├── frontend/
│   ├── public/
│   │   └── index.php          # Front controller, .env loader, router
│   └── views/                 # PHP view templates
│       ├── auth/
│       ├── attendance/
│       ├── staff/
│       ├── leave/
│       ├── announcements/
│       ├── docs/
│       │   └── viewer.php     # Markdown doc viewer
│       └── partials/
├── backend/
│   ├── src/
│   │   ├── Config/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Middleware/
│   │   ├── Services/
│   │   ├── Utils/
│   │   └── Router.php
│   ├── config/
│   │   ├── database.php
│   │   └── app.php
│   ├── database/
│   │   └── seeds/
│   │       └── seed_users.sql
│   ├── tests/                 # PHPUnit test suite
│   ├── vendor/
│   └── .env
├── docs/                      # Markdown deliverables
│   ├── user-guide.md
│   ├── case-study.md
│   ├── deployment.md
│   ├── api-documentation.md
│   ├── staff-training.md
│   └── local-vscode-setup.md
├── database.sql
└── .env
```

## Running Tests

```bash
cd backend
vendor/bin/phpunit --configuration phpunit.xml
```

## Success Criteria Checklist

| Criterion | Status |
|-----------|--------|
| Live dashboard updates within seconds | ✅ 30-second polling with real-time metrics |
| Error handling and duplicate prevention | ✅ CSRF, rate limiting, 5-second duplicate guard |
| Data backup and recovery | ✅ Documented mysqldump approach in `docs/deployment.md` |
| Scalability for future expansion | ✅ Documented in `docs/case-study.md` |
| Mobile / cross-platform support | ✅ Responsive Tailwind UI, works on all modern browsers |

## Documentation

Full documentation is available in the `/docs` folder or via the **Docs** page in the app:

- [User Guide](docs/user-guide.md) — how staff and admins use the system
- [Case Study](docs/case-study.md) — problem, approach, decisions, outcomes
- [Deployment Guide](docs/deployment.md) — local, Xneelo VPS, backup/recovery
- [API Reference](docs/api-documentation.md) — all REST endpoints
- [Staff Training](docs/staff-training.md) — training session outline and quick reference
- [VS Code Setup](docs/local-vscode-setup.md) — development environment setup
- [MySQL Workbench Guide](docs/mysql-workbench-guide.md) — database browsing and queries

## License

MIT
