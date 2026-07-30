# Staff Training — INNOW Digital Attendance System

## Training Session Outline

**Duration:** 30 minutes  
**Audience:** All INNOW staff and managers  
**Trainer:** System Administrator or designated power user

---

## Agenda

1. Introduction (3 min)
2. Logging In (3 min)
3. Clocking In and Out (5 min)
4. Break Management (2 min)
5. QR Code Check-In (5 min)
6. Leave Requests (5 min)
7. Announcements (3 min)
8. Q&A (4 min)

---

## 1. Introduction (3 min)

- What is INNOW? A digital attendance and leave management system.
- Why? Faster check-in, real-time visibility, no more paper registers.
- Where? Access it from any device on the office network at `http://localhost:8000`.

---

## 2. Logging In (3 min)

- Open your browser (Chrome, Edge, Firefox)
- Go to **http://localhost:8000**
- Enter your **email** and **4-digit PIN**
- Click **Sign In**

**Demo:**
- Log in as a staff member (`lerato.m@innow.com` / `1005`)
- Log in as an admin (`thabo.m@innow.com` / `1001`)

---

## 3. Clocking In and Out (5 min)

- After logging in, click **Check-In** in the top navigation
- To clock in: click the big red **Clock In** button
- To clock out: click **Clock Out**
- Your status badge updates immediately on the dashboard

**Key points:**
- You cannot clock in again without clocking out first (duplicate prevention)
- The system prevents double-clock-in within a 5-second window
- If the button does not work, wait 5 seconds and try again

---

## 4. Break Management (2 min)

- While clocked in, you can click **Go on Break**
- Click **End Break** when you return
- Breaks are logged with timestamps

---

## 5. QR Code Check-In (5 min)

**For admins setting up the terminal:**
1. Go to **Check-In** → **Scan QR Code**
2. A terminal-specific QR payload is generated
3. Display this QR code at the entrance camera/terminal

**For staff:**
1. Go to **Check-In** → **Scan QR Code**
2. Point your phone camera at the terminal QR code
3. Tap the notification to confirm
4. Your attendance is recorded automatically

---

## 6. Leave Requests (5 min)

**For staff:**
1. Click **Leave Management**
2. Click **Request Leave**
3. Choose leave type (Annual, Sick, Unpaid, Maternity, Other)
4. Pick start and end dates
5. Enter number of days
6. Add a reason (required for "Other" leave)
7. Click **Submit Request**
8. Track status: PENDING → APPROVED or DECLINED
9. See who reviewed you in the **Reviewed By** column

**For managers:**
1. Go to **Leave Management**
2. See all staff leave requests
3. Click **Approve** or **Decline** for PENDING requests
4. The **Reviewed By** column updates with your name

---

## 7. Announcements (3 min)

- Click **Announcements** to see all company notices
- Admins: click **New Announcement** to post updates
- All staff see announcements immediately — no refresh needed
- Delete announcements using the trash icon (admins or author only)

---

## 8. Q&A (4 min)

Common questions:

**Q: Can I use my phone?**  
A: Yes. Open `http://localhost:8000` in your phone's browser.

**Q: What if I forget my PIN?**  
A: Contact your admin. They can reset it in the Staff Directory.

**Q: Can I edit a leave request after submitting?**  
A: Not yet. Delete the old one (if still PENDING) and submit a new one.

**Q: What if the server goes down?**  
A: Attendance is stored locally in MySQL. Once the server is back, everything is still there.

---

## Quick Reference Card

| Action | Path | Who |
|--------|------|-----|
| Clock In/Out | Check-In → Button | Everyone |
| QR Check-In | Check-In → Scan QR | Everyone |
| Request Leave | Leave Management → Request Leave | Everyone |
| Approve Leave | Leave Management → Approve/Decline | Admin |
| Post Announcement | Announcements → New Announcement | Admin |
| View Logs | Logs | Admin |
| Manage Staff | Staff Directory | Admin |
