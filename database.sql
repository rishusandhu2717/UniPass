-- Database setup for Campus Visitor Management System (UniPass) - MySQL / TiDB Cloud Version

-- 1. Create the visitors table:
CREATE TABLE IF NOT EXISTS visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    host_department VARCHAR(100) NOT NULL,
    purpose_details TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    time_in TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    time_out TIMESTAMP NULL,
    daily_seq INT NULL,
    checked_in_by VARCHAR(100) NULL,
    checked_out_by VARCHAR(100) NULL,
    entered_by VARCHAR(50) NULL,
    CHECK (status IN ('Pending', 'Inside', 'Checked Out', 'Rejected'))
);

-- 2. Users table for Authentication and Role-Based Access Control
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (role IN ('admin', 'gate'))
);
