# API Reference — INNOW Attendance System

Base URL: `http://localhost:8000`

All endpoints require a valid session cookie (`innow_session`) and a CSRF token for POST/PUT/DELETE requests.

---

## Authentication

### POST /api/login
Authenticate with email and PIN.

**Request body:**
```json
{
  "email": "thabo.m@innow.com",
  "pin": "1001"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful.",
  "token": "<hex_token>",
  "csrf_token": "<hex_csrf_token>",
  "user": {
    "id": "STF-1001",
    "name": "Thabo Mokoena",
    "email": "thabo.m@innow.com",
    "role": "System Administrator",
    "department": "Operations & IT",
    "status": "ONSITE"
  }
}
```

### GET /logout
Destroy the current session. Redirects to `/login`.

---

## Attendance

### GET /api/checkin/qr-payload
Generate a fresh QR payload for terminal scanning.

**Auth required:** Yes  
**Rate limit:** Yes

**Response:**
```json
{
  "success": true,
  "message": "Active QR payload generated.",
  "payload": { "token": "...", "terminal_id": "TRM-MAIN-GATE", ... }
}
```

### POST /api/checkin/qr
Check in/out via QR payload.

**Auth required:** Yes  
**CSRF required:** Yes  
**Rate limit:** Yes

**Request body:**
```json
{
  "qr_token": "<payload_token>",
  "action": "CLOCK_IN"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Attendance recorded.",
  "record": { "id": "LOG-1234", ... }
}
```

### POST /api/checkin/button
One-click check-in button.

**Auth required:** Yes  
**CSRF required:** Yes  
**Rate limit:** Yes

**Request body:**
```json
{
  "action": "CLOCK_IN",
  "notes": "Optional notes"
}
```

### POST /api/manual-entry
Admin manually records attendance for another staff member.

**Auth required:** Yes (admin only)  
**CSRF required:** Yes

**Request body:**
```json
{
  "user_id": "STF-1002",
  "action": "CLOCK_IN",
  "notes": "Manual entry"
}
```

---

## Dashboard

### GET /api/dashboard/summary
Returns dashboard metrics and lists for the current user.

**Auth required:** Yes

**Response:**
```json
{
  "success": true,
  "metrics": {
    "total_staff": 16,
    "onsite_count": 5,
    "break_count": 2,
    "offsite_count": 9,
    "total_today_logs": 42
  },
  "onsite_staff": [...],
  "all_staff": [...],
  "recent_logs": [...]
}
```

---

## Staff Management

### GET /api/staff
List all staff. Admin only.

**Auth required:** Yes (admin only)

**Response:**
```json
{
  "success": true,
  "users": [...]
}
```

### POST /api/staff/add
Register a new staff member. Admin only.

**Auth required:** Yes (admin only)  
**CSRF required:** Yes

**Request body:**
```json
{
  "name": "Jane Doe",
  "email": "jane.doe@innow.com",
  "pin": "1234",
  "department": "Engineering",
  "role": "Staff Member"
}
```

### POST /api/staff/remove
Remove a staff member. Admin only.

**Auth required:** Yes (admin only)  
**CSRF required:** Yes

**Request body:**
```json
{
  "id": "STF-1002"
}
```

---

## Leave Requests

### GET /api/leaves
List leave requests visible to the logged-in user. Regular users see only their own. Admins see all.

**Auth required:** Yes

**Response:**
```json
{
  "success": true,
  "leaves": [
    {
      "id": "LEAVE-1234",
      "user_id": "STF-1001",
      "user_name": "Thabo Mokoena",
      "leave_type": "Annual Leave",
      "start_date": "2026-08-01",
      "end_date": "2026-08-05",
      "days_requested": 5,
      "reason": "Family vacation",
      "status": "PENDING",
      "reviewed_by_name": null,
      "created_at": "2026-07-29 14:00:00"
    }
  ]
}
```

### POST /api/leaves
Submit a new leave request.

**Auth required:** Yes  
**CSRF required:** Yes

**Request body:**
```json
{
  "leave_type": "Annual Leave",
  "start_date": "2026-08-01",
  "end_date": "2026-08-05",
  "days_requested": 5,
  "reason": "Family vacation"
}
```

### POST /api/leaves/update
Approve or decline a leave request. Admin only.

**Auth required:** Yes (admin only)  
**CSRF required:** Yes

**Request body:**
```json
{
  "id": "LEAVE-1234",
  "status": "APPROVED"
}
```

### POST /api/leaves/delete
Delete a leave request. Admin only.

**Auth required:** Yes (admin only)  
**CSRF required:** Yes

**Request body:**
```json
{
  "id": "LEAVE-1234"
}
```

---

## Announcements

### GET /api/announcements
List all announcements. Visible to all authenticated users.

**Auth required:** Yes

**Response:**
```json
{
  "success": true,
  "announcements": [
    {
      "id": "ANN-1234",
      "user_id": "STF-1001",
      "user_name": "Thabo Mokoena",
      "title": "Office Closure",
      "message": "The office will be closed on 2026-08-01.",
      "created_at": "2026-07-29 10:00:00"
    }
  ]
}
```

### POST /api/announcements
Post a new announcement.

**Auth required:** Yes  
**CSRF required:** Yes

**Request body:**
```json
{
  "title": "Office Closure",
  "message": "The office will be closed on 2026-08-01."
}
```

### POST /api/announcements/delete
Delete an announcement. Admin or owner only.

**Auth required:** Yes  
**CSRF required:** Yes

**Request body:**
```json
{
  "id": "ANN-1234"
}
```

---

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description.",
  "errors": []
}
```

Common status codes:
- `400` — Bad request (missing fields, validation failed)
- `401` — Authentication required (missing or invalid session cookie)
- `403` — Forbidden (insufficient permissions)
- `404` — Resource not found
- `429` — Rate limit exceeded
- `500` — Server error (uncaught exception)
