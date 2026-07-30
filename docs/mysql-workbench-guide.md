# MySQL Workbench Guide

## Connecting to the INNOW Database

### Step 1: Open MySQL Workbench

1. Launch **MySQL Workbench** from your Start Menu
2. Click the **+** icon next to *MySQL Connections*

### Step 2: Connection Settings

| Field | Value |
|-------|-------|
| Connection Name | `INNOW Local` |
| Hostname | `127.0.0.1` |
| Port | `3306` |
| Username | `root` |
| Password | Click **Store in Vault** and enter `KwaNomaLiv24!` |

Click **Test Connection**. If successful, click **OK**.

### Step 3: Browse the Database

1. Select the `INNOW Local` connection
2. Expand the `Schemas` panel on the left
3. Find `innow_db` and expand it to see tables:
   - `users`
   - `sessions`
   - `attendance_records`
   - `leave_requests`
   - `announcements`

### Step 4: Common Queries

View all active users:
```sql
SELECT id, name, email, role, department, status FROM users ORDER BY name ASC;
```

View today's attendance logs:
```sql
SELECT r.*, u.name as staff_name
FROM attendance_records r
JOIN users u ON r.user_id = u.id
WHERE DATE(r.timestamp) = CURDATE()
ORDER BY r.timestamp DESC;
```

View pending leave requests:
```sql
SELECT l.*, u.name as staff_name
FROM leave_requests l
JOIN users u ON l.user_id = u.id
WHERE l.status = 'PENDING'
ORDER BY l.created_at ASC;
```

### Step 5: Running the Seed SQL

If you need to reset the staff data:
1. Open `backend/database/seeds/seed_users.sql`
2. Copy the contents
3. In MySQL Workbench, open a new query tab for `innow_db`
4. Paste and click the **Lightning bolt** (Execute)

Alternatively, double-click `database.sql` to restore the full dump.
