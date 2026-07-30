-- ====================================================================
-- INNOW Digital Attendance Tracking System - Clean Seed Data
-- Run this if you need to remove seed/placeholder data from an
-- existing database while preserving real application data.
-- ====================================================================

-- Remove seed attendance records (IDs LOG-0001 through LOG-0014)
DELETE FROM attendance_records
WHERE id BETWEEN 'LOG-0001' AND 'LOG-0014';

-- Remove seed users (IDs STF-1001 through STF-1014)
DELETE FROM users
WHERE id BETWEEN 'STF-1001' AND 'STF-1014';

-- Clean up any expired sessions from seed/admin testing
DELETE FROM sessions
WHERE expires_at <= NOW();
