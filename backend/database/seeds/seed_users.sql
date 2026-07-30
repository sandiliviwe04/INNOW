-- Delete existing attendance records
DELETE FROM attendance_records;

-- Delete existing users
DELETE FROM users;

-- Insert new staff and admin teams
INSERT INTO users (id, name, email, pin, role, department, phone, emergency_contact, status, avatar_url, qr_code) VALUES
('STF-1001', 'Thabo Mokoena', 'thabo.m@innow.com', '1001', 'System Administrator', 'Operations & IT', '+27 82 111 0001', 'Emergency Contact: +27 82 222 0001', 'ONSITE', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150', 'INNOW-QR-1001-THABO'),
('STF-1002', 'Lerato Moloi', 'lerato.m@innow.com', '1002', 'Operations Manager', 'Operations & IT', '+27 82 111 0002', 'Emergency Contact: +27 82 222 0002', 'ONSITE', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150', 'INNOW-QR-1002-LERATO'),
('STF-1003', 'Liviwe Sandi', 'liviwe.s@innow.com', '1003', 'System Administrator', 'Engineering', '+27 82 111 0003', 'Emergency Contact: +27 82 222 0003', 'ONSITE', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150', 'INNOW-QR-1003-LIVIWE'),
('STF-1004', 'Hendrik Fourie', 'hendrik.f@innow.com', '1004', 'IT Administrator', 'Infrastructure', '+27 82 111 0004', 'Emergency Contact: +27 82 222 0004', 'ONSITE', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150', 'INNOW-QR-1004-HENDRIK'),
('STF-1005', 'Kagiso Phiri', 'kagiso.p@innow.com', '1005', 'Senior Developer', 'Software Engineering', '+27 82 111 0005', 'Emergency Contact: +27 82 222 0005', 'ONSITE', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150', 'INNOW-QR-1005-KAGISO'),
('STF-1006', 'Mpho Molefe', 'mpho.m@innow.com', '1006', 'Developer', 'Software Engineering', '+27 82 111 0006', 'Emergency Contact: +27 82 222 0006', 'BREAK', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150', 'INNOW-QR-1006-MPHO'),
('STF-1007', 'Gareth Smith', 'gareth.s@innow.com', '1007', 'UX Designer', 'Design & UX', '+27 82 111 0007', 'Emergency Contact: +27 82 222 0007', 'ONSITE', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150', 'INNOW-QR-1007-GARETH'),
('STF-1008', 'Jessica Govender', 'jessica.g@innow.com', '1008', 'QA Engineer', 'Quality Assurance', '+27 82 111 0008', 'Emergency Contact: +27 82 222 0008', 'OFFSITE', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150', 'INNOW-QR-1008-JESSICA'),
('STF-1009', 'Tshepo Sefofane', 'tshepo.s@innow.com', '1009', 'Data Analyst', 'Analytics & AI', '+27 82 111 0009', 'Emergency Contact: +27 82 222 0009', 'ONSITE', 'https://images.unsplash.com/photo-1552058544-f2b08422138a?w=150', 'INNOW-QR-1009-TSHEPO'),
('STF-1010', 'Liezel Smit', 'liezel.s@innow.com', '1010', 'Project Manager', 'Software Engineering', '+27 82 111 0010', 'Emergency Contact: +27 82 222 0010', 'ONSITE', 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=150', 'INNOW-QR-1010-LIEZEL'),
('STF-1011', 'Francois Du Plessis', 'francois.dp@innow.com', '1011', 'DevOps Engineer', 'Infrastructure', '+27 82 111 0011', 'Emergency Contact: +27 82 222 0011', 'ONSITE', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150', 'INNOW-QR-1011-FRANCOIS'),
('STF-1012', 'Sunette Coetzee', 'sunette.c@innow.com', '1012', 'HR Manager', 'Human Resources', '+27 82 111 0012', 'Emergency Contact: +27 82 222 0012', 'ONSITE', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150', 'INNOW-QR-1012-SUNETTE'),
('STF-1013', 'Emihle Dumo', 'emihle.d@innow.com', '1013', 'Junior Developer', 'Software Engineering', '+27 82 111 0013', 'Emergency Contact: +27 82 222 0013', 'ONSITE', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=150', 'INNOW-QR-1013-EMIHLE'),
('STF-1014', 'Bungcwalisa Magobiyane', 'bungcwalisa.m@innow.com', '1014', 'Security Officer', 'Facilities', '+27 82 111 0014', 'Emergency Contact: +27 82 222 0014', 'ONSITE', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150', 'INNOW-QR-1014-BUNGCWALISA');

-- Insert sample attendance records
INSERT INTO attendance_records (id, user_id, action, timestamp, method, synced_to_db, notes) VALUES
('LOG-0001', 'STF-1001', 'CLOCK_IN', NOW() - INTERVAL 4 HOUR, 'PIN', 1, 'Morning shift check-in'),
('LOG-0002', 'STF-1002', 'CLOCK_IN', NOW() - INTERVAL 3 HOUR, 'QR', 1, 'Front gate scanner'),
('LOG-0003', 'STF-1003', 'CLOCK_IN', NOW() - INTERVAL 3 HOUR, 'BUTTON', 1, 'One-click check-in'),
('LOG-0004', 'STF-1004', 'CLOCK_IN', NOW() - INTERVAL 2 HOUR, 'QR', 1, 'Front gate scanner'),
('LOG-0005', 'STF-1004', 'BREAK_START', NOW() - INTERVAL 30 MINUTE, 'BUTTON', 1, 'Tea break'),
('LOG-0006', 'STF-1005', 'CLOCK_IN', NOW() - INTERVAL 2 HOUR, 'BUTTON', 1, 'Morning arrival'),
('LOG-0007', 'STF-1006', 'CLOCK_IN', NOW() - INTERVAL 1 HOUR, 'PIN', 1, 'Late arrival'),
('LOG-0008', 'STF-1009', 'CLOCK_IN', NOW() - INTERVAL 45 MINUTE, 'QR', 1, 'Front gate scanner'),
('LOG-0009', 'STF-1010', 'CLOCK_IN', NOW() - INTERVAL 3 HOUR, 'BUTTON', 1, 'One-click check-in'),
('LOG-0010', 'STF-1011', 'CLOCK_IN', NOW() - INTERVAL 5 HOUR, 'PIN', 1, 'Early arrival'),
('LOG-0011', 'STF-1012', 'CLOCK_IN', NOW() - INTERVAL 4 HOUR, 'BUTTON', 1, 'One-click check-in'),
('LOG-0012', 'STF-1013', 'CLOCK_IN', NOW() - INTERVAL 30 MINUTE, 'PIN', 1, 'Morning check-in'),
('LOG-0013', 'STF-1014', 'CLOCK_IN', NOW() - INTERVAL 6 HOUR, 'BUTTON', 1, 'Security shift start'),
('LOG-0014', 'STF-1001', 'BREAK_START', NOW() - INTERVAL 15 MINUTE, 'BUTTON', 1, 'Coffee break');

