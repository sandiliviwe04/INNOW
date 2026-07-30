# User Guide — INNOW Digital Attendance System

## Table of Contents

1. [Getting Started](#getting-started)
2. [Staff / Employee Guide](#staff--employee-guide)
3. [Admin / Supervisor Guide](#admin--supervisor-guide)
4. [FAQ](#faq)

---

## Getting Started

### Accessing the System

1. Open your browser and go to: **http://localhost:8000**
2. You will be redirected to the **Login** page
3. Enter your email and 4-digit PIN
4. Click **Sign In**

> Sample accounts (for testing):
> - Email: `thabo.m@innow.com` | PIN: `1001` (Admin)
> - Email: `lerato.m@innow.com` | PIN: `1005` (Staff)

---

## Staff / Employee Guide

### Clocking In

1. After logging in, click **Check-In** in the navigation menu
2. You will see a big red **Clock In** button
3. Click it — your status changes to **ONSITE** and the dashboard updates within 30 seconds

### Clocking Out

1. Go to **Check-In**
2. Click **Clock Out** — your status changes to **OFFSITE**

### QR Code Check-In (Optional)

If your organization uses QR check-in terminals:

1. Go to **Check-In**
2. Click **Scan QR Code**
3. Point your device camera at the terminal QR code
4. The system automatically records your attendance

### Viewing Leave Balance and History

1. Click **Leave Management** in the navigation menu
2. Click **Request Leave** to submit a new leave request
3. Fill in the leave type, dates, and reason
4. Submit — your request will appear in the table with status **PENDING**
5. Once reviewed, the status updates to **APPROVED** or **DECLINED**, and the reviewer's name is shown in the **Reviewed By** column

### Announcements

1. Click **Announcements** in the navigation menu
2. All company-wide announcements are listed here
3. Click **New Announcement** to post a new one (if you have permission)

---

## Admin / Supervisor Guide

### Dashboard Overview

1. After logging in as an admin, you land on the **Dashboard**
2. The dashboard shows:
   - **Total Staff**: count of all registered employees
   - **Onsite Now**: staff currently checked in
   - **On Break**: staff currently on break
   - **Offsite**: staff currently offsite
   - **Today's Logs**: total attendance events today
   - **Recent Logs**: last 15 clock-in/out events

The dashboard refreshes automatically every 30 seconds.

### Managing Staff

1. Click **Staff Directory**
2. To **add** a new staff member: click **Add Staff**, fill in their details, and submit
3. To **remove** a staff member: click the **trash icon** next to their name

### Approving / Declining Leave Requests

1. Click **Leave Management**
2. All leave requests are visible (not just your own)
3. For any **PENDING** request, click:
   - **Approve** (green) — the request is approved
   - **Decline** (red) — the request is declined
4. The **Reviewed By** column updates to show your name

### Posting Announcements

1. Click **Announcements**
2. Click **New Announcement**
3. Enter a title and message
4. Click **Post Announcement**
5. All employees can now see the announcement immediately

### Viewing Attendance Logs

1. Click **Logs** (admins only)
2. See every clock-in, clock-out, break, and QR scan event
3. Filter or search to find specific staff or date ranges

---

## FAQ

**Q: I forgot my PIN. What do I do?**  
A: Contact your System Administrator. They can reset your PIN in the Staff Directory.

**Q: Can I clock in from my phone?**  
A: Yes. The system is responsive and works on mobile browsers. Go to http://localhost:8000 on your phone.

**Q: What happens if I lose internet connection?**  
A: The system runs on your local network. As long as the server is running, attendance is recorded in the local MySQL database and synced when connectivity is restored.

**Q: How long is my session valid?**  
A: Sessions last 7 days. After that, you will need to log in again.

**Q: Can I edit an announcement after posting?**  
A: Not yet. Use the trash icon to delete and repost, or ask your admin to edit via MySQL Workbench.
