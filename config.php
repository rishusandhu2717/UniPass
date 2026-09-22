<?php
// config.php

// Global Error Handling Configuration
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide raw errors from users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// MySQL Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'vms_db'); // Make sure this database exists in MySQL
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Updated password for XAMPP default

try {
    // Attempt to connect to MySQL database using PDO
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Log the error securely (in a real-world app, log to a file)
    error_log("Database connection failed: " . $e->getMessage());
    
    // Graceful error handling for the user
    die("<div style='background-color: #020617; color: #f43f5e; padding: 30px; font-family: sans-serif; text-align: center; border-bottom: 2px solid #e11d48;'>
            <h2 style='margin-bottom: 10px;'>System Error</h2>
            <p>We are currently experiencing database connection issues. Please check your MySQL credentials and try again later.</p>
         </div>");
}
?>
