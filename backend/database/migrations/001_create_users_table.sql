CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pin VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    department VARCHAR(50) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    emergency_contact VARCHAR(100),
    address TEXT,
    status VARCHAR(20) DEFAULT 'OFFSITE',
    avatar_url TEXT,
    qr_code VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
