<?php
// config.php

// Global Error Handling Configuration
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide raw errors from users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// ============================================================
// PRIMARY: MySQL (Local XAMPP) Configuration
// ============================================================
define('DB_HOST',     'localhost');
define('DB_PORT',     '3306');
define('DB_NAME',     'vms_db');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');

// ============================================================
// FALLBACK: Supabase PostgreSQL Configuration
// ============================================================
define('SUPABASE_HOST', 'db.mabfdafcijpjfqsavbvb.supabase.co');
define('SUPABASE_PORT', '5432');
define('SUPABASE_NAME', 'postgres');
define('SUPABASE_USER', 'postgres');
define('SUPABASE_PASS', 'rishusandhu27');

// ============================================================
// SMART CONNECTION: MySQL first, Supabase if MySQL unavailable
// ============================================================
$pdo = null;
$db_driver = null; // 'mysql' or 'pgsql' — used for query compatibility

// --- Step 1: Try MySQL (Primary) ---
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 3, // fast timeout so fallback is quick
    ]);
    $db_driver = 'mysql';

} catch (PDOException $e) {
    // MySQL not available — log silently and try Supabase
    error_log("[UniPass] MySQL unavailable (" . $e->getMessage() . "). Switching to Supabase...");
    $pdo = null;
}

// --- Step 2: Fallback to Supabase PostgreSQL ---
if ($pdo === null) {
    try {
        $dsn = "pgsql:host=" . SUPABASE_HOST . ";port=" . SUPABASE_PORT . ";dbname=" . SUPABASE_NAME . ";sslmode=require";
        $pdo = new PDO($dsn, SUPABASE_USER, SUPABASE_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $db_driver = 'pgsql';
        error_log("[UniPass] Connected to Supabase PostgreSQL (fallback).");

    } catch (PDOException $e) {
        // Both failed — show user-friendly error
        error_log("[UniPass] Supabase also failed: " . $e->getMessage());
        die("<div style='background-color:#020617;color:#f43f5e;padding:30px;font-family:sans-serif;text-align:center;border-bottom:2px solid #e11d48;'>
                <h2 style='margin-bottom:10px;'>System Error</h2>
                <p>No database connection available. Please ensure MySQL (XAMPP) is running, or check your internet connection for Supabase access.</p>
             </div>");
    }
}

// ============================================================
// HELPER: Returns correct SQL date function for current driver
// Usage: use db_curdate() in queries instead of CURDATE()
// ============================================================
function db_curdate() {
    global $db_driver;
    return ($db_driver === 'pgsql') ? 'CURRENT_DATE' : 'CURDATE()';
}
?>
