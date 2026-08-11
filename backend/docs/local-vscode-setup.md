# Local VS Code Setup Guide

## Prerequisites

- Windows 10/11
- Visual Studio Code (latest version)
- XAMPP or PHP 8.1+ CLI
- Composer (optional, for dependency management)
- Git (optional, for version control)

## Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Install with PHP 8.1+ and MySQL enabled
3. Start **Apache** and **MySQL** from the XAMPP Control Panel
4. Verify PHP is available in your terminal:
   ```bash
   php -v
   ```

## Step 2: Open the Project in VS Code

```bash
# Clone or extract the project
cd C:\Users\Liviwe Sandi\Downloads\INNOW(attendance-tracking-system)

# Open in VS Code
code .
```

## Step 3: Configure the Environment

1. Open `.env` in the project root and verify these values:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=innow_db
   DB_USER=root
   DB_PASS=KwaNomaLiv24!
   ```

2. Open `backend/.env` and verify backend settings:
   ```env
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   APP_SECRET=innow_secret_key_2026
   ```

## Step 4: Import the Database

1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Create a database named `innow_db` (if it does not already exist)
3. Import `database.sql` from the project root:
   - Click **Import** → Choose file `database.sql` → Click **Go**

Alternatively, via MySQL CLI:
```bash
mysql -u root -pKwaNomaLiv24! innow_db < database.sql
```

## Step 5: Start the PHP Built-in Server

```bash
cd "C:\Users\Liviwe Sandi\Downloads\INNOW(attendance-tracking-system)\frontend\public"
php -S localhost:8000
```

Then open http://localhost:8000 in your browser.

## Step 6: VS Code Recommended Extensions

- PHP Intelephense (PHP language support)
- PHP Debug (Xdebug integration)
- ESLint (if you extend the frontend JS)
- Thunder Client or REST Client (for API testing)

## Troubleshooting

| Issue | Solution |
|-------|----------|
| `Class 'Innow\Config\Database' not found` | Ensure `frontend/public/index.php` is the entry point, not `backend/public/index.php` |
| `Database Connection Error` | Verify `.env` credentials; check that MySQL is running in XAMPP |
| `404 Not Found` on `/api/*` | Make sure you are accessing via `localhost:8000` (the built-in PHP server front controller) |
| CSS/JS not loading | Check that Tailwind CSS CDN and Lucide Icons CDN are reachable from your network |
