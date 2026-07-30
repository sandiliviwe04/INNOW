CREATE TABLE IF NOT EXISTS attendance_records (
    id VARCHAR(50) PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    action VARCHAR(20) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    method VARCHAR(20) NOT NULL,
    synced_to_db INTEGER DEFAULT 1,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
