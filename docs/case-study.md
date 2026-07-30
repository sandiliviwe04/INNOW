# Case Study — INNOW Digital Attendance System

## 1. Problem Statement

INNOW needed a replacement for paper-based attendance registers and manual spreadsheets. The old process was:

- Time-consuming for staff to sign in/out
- Prone to buddy-punching (one person clocking in for another)
- Difficult for managers to get real-time visibility of who is onsite
- No centralized record of leave requests or approvals
- No offline fallback for Cape Town load-shedding scenarios

## 2. Approach

We designed and built a **PHP + MySQL** web application that runs entirely on the local network. No cloud dependencies. No SaaS subscription.

### Key Architectural Decisions

| Decision | Rationale |
|----------|-----------|
| **PHP 8 + MySQL** | Matches the client's existing LAMP/XAMPP skill set and hosting environment. Zero migration cost. |
| **Built-in PHP server** | Eliminates the need for Apache/Nginx configuration during development and demo. `php -S localhost:8000` is all that is needed. |
| **Session auth (no JWT)** | Simpler threat model for a closed local network. No token leakage risk. Sessions expire after 7 days and are server-side invalidated on logout. |
| **Cookie-based session** | Secure, HttpOnly, SameSite=Lax. Works across all modern browsers without local storage complexity. |
| **QR code payloads** | QR was chosen over NFC because: (1) no proprietary hardware required, (2) any phone camera can scan, (3) the front-gate camera use case works with QR payloads verified server-side. |
| **30-second polling** | The dashboard and attendance pages poll every 30 seconds. This balances real-time feel with server load. For a system of <100 staff, this is negligible overhead. |
| **CSRF protection** | All state-changing API endpoints validate a server-generated CSRF token bound to the session. Prevents cross-site request forgery. |
| **Rate limiting** | Login and check-in endpoints are rate-limited per IP to prevent abuse. |

### Database Design

- **users** — staff profiles, roles, PIN hashes, status
- **sessions** — active sessions with 7-day expiry
- **attendance_records** — every clock-in, clock-out, break, and manual entry
- **leave_requests** — leave applications with reviewer tracking
- **announcements** — company-wide notices with author tracking

Foreign keys enforce referential integrity. Deleting a user cascades to their attendance records and leave requests.

## 3. Challenges Encountered

### Challenge: Cookie-based auth vs. fetch API
**Problem:** The frontend uses `fetch()` for API calls. By default, `fetch()` does not send cookies on same-origin requests in some browser configurations.

**Solution:** Added `credentials: 'same-origin'` to `authFetch()`. This ensures the session cookie is sent with every API request.

### Challenge: PHP 8 type strictness
**Problem:** Passing `null` to `ResponseHelper::success(array $data = [], ...)` caused a `TypeError` on delete/approve actions, returning HTML error pages instead of JSON.

**Solution:** Changed all `null` responses to `[]`. Also wrapped the router dispatch in a try-catch so uncaught exceptions return clean JSON.

### Challenge: Join ambiguity in Session::findByToken
**Problem:** `SELECT s.*, u.name ...` returned `s.id` (the session ID) in the `id` field. Controllers that used `$user['id']` were inserting session IDs into `user_id` columns, violating foreign keys.

**Solution:** All controllers now use `$user['user_id']` for the actual user ID. The session ID is accessed as `$user['id']`.

### Challenge: Duplicate .env files
**Problem:** The project had both a root `.env` and `backend/.env`. Only the root was loaded, causing `backend/.env` values (like `APP_SECRET`) to be silently ignored.

**Solution:** `frontend/public/index.php` now loads **both** `.env` files. `backend/.env` overrides root values for overlapping keys.

## 4. Outcomes

### Functional Outcomes
- Staff can clock in/out in under 5 seconds
- Admins see real-time (30s) dashboard metrics
- Leave requests are tracked with reviewer attribution
- Announcements are visible to the entire organization instantly
- QR code scan-and-go works from any phone camera

### Non-Functional Outcomes
- The app runs on a closed local network — no internet required after initial setup
- Database is fully local — data never leaves the LAN
- 7-day session persistence with secure HttpOnly cookies
- CSRF and rate-limiting protection on all state-changing endpoints

## 5. Scalability Notes

The current architecture is designed for <100 concurrent users on a single XAMPP server. For future expansion:

- **Replace built-in PHP server** with Apache or Nginx for production
- **Implement JWT or OAuth2** if the system is ever exposed beyond the local network
- **Add a message queue** (e.g., Redis) if real-time WebSocket updates replace polling
- **Database sharding or read replicas** would be needed if the user base grows beyond 500 active staff
- **Frontend migration to React/Vue** would make the mobile experience smoother if native app development is later desired

## 6. Technology Stack

| Layer | Technology |
|-------|------------|
| Frontend | PHP views, Tailwind CSS (CDN), Vanilla JS, Lucide Icons |
| Backend | PHP 8.1+, PSR-4 autoloading |
| Database | MySQL 8.0 (innow_db) |
| Server | PHP Built-in Server (development) / Apache (production) |
| Auth | Session-based, HttpOnly cookies, CSRF tokens |
| Docs | Markdown rendered via Marked.js |
