-- Database setup for Campus Visitor Management System (BCA_1) - MySQL Version

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
    entered_by VARCHAR(50) NULL,
    CHECK (status IN ('Pending', 'Inside', 'Checked Out', 'Rejected'))
);

-- Users table for Authentication and Role-Based Access Control
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (role IN ('admin', 'gate'))
);
