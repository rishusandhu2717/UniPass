<?php
// config.php

// Application Timezone (IST - Campus Standard)
date_default_timezone_set('Asia/Kolkata');

// Global Error Handling Configuration
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide raw errors from users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// ============================================================
// ENVIRONMENT VARIABLE & DATABASE_URL PARSING (Render & Cloud)
// Supports single connection string (e.g. DATABASE_URL or MYSQL_URL)
// ============================================================
$db_url = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
$parsed_url = !empty($db_url) ? parse_url($db_url) : null;

// ============================================================
// PRIMARY: MySQL (Local XAMPP) Configuration
// ============================================================
define('DB_HOST',     getenv('DB_HOST') ?: 'localhost');
define('DB_PORT',     getenv('DB_PORT') ?: '3306');
define('DB_NAME',     getenv('DB_NAME') ?: 'vms_db');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');

// ============================================================
// ONLINE CLOUD: TiDB Cloud MySQL Configuration
// ============================================================
define('TIDB_HOST',     $parsed_url['host'] ?? (getenv('TIDB_HOST') ?: 'gateway01.ap-northeast-1.prod.aws.tidbcloud.com'));
define('TIDB_PORT',     $parsed_url['port'] ?? (getenv('TIDB_PORT') ?: '4000'));
define('TIDB_USER',     $parsed_url['user'] ?? (getenv('TIDB_USER') ?: '44Hq83chaYcfhH4.root'));
define('TIDB_PASS',     $parsed_url['pass'] ?? (getenv('TIDB_PASS') ?: 'sOQIKKdgdiIJ9OAo'));

// App Database name: if connection string has '/sys' or empty, use 'vms_db'
$url_db = isset($parsed_url['path']) ? ltrim($parsed_url['path'], '/') : '';
define('TIDB_NAME',     (!empty($url_db) && $url_db !== 'sys') ? $url_db : (getenv('TIDB_NAME') ?: 'vms_db'));

// Detect Render Cloud Environment or explicit flag
$is_cloud_environment = getenv('RENDER') || !empty($db_url) || getenv('FORCE_TIDB_CLOUD') === 'true';
define('FORCE_TIDB_CLOUD', $is_cloud_environment);

// ============================================================
// SMART CONNECTION: MySQL Local first, TiDB Cloud fallback
// ============================================================
$pdo = null;
$db_driver = 'mysql';

// --- Step 1: Try Local MySQL (if not in Cloud environment) ---
if (!FORCE_TIDB_CLOUD) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 2, // fast timeout so fallback is quick
        ]);
        $pdo->exec("SET time_zone = '+05:30';");
        error_log("[UniPass] Connected to Local MySQL.");
    } catch (PDOException $e) {
        // Local MySQL not available — log silently and try TiDB Cloud
        error_log("[UniPass] Local MySQL unavailable (" . $e->getMessage() . "). Switching to TiDB Cloud...");
        $pdo = null;
    }
}

// --- Step 2: Connect / Fallback to TiDB Cloud MySQL ---
if ($pdo === null) {
    try {
        $dsn = "mysql:host=" . TIDB_HOST . ";port=" . TIDB_PORT . ";dbname=" . TIDB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 8,
        ];
        
        // TiDB Cloud requires SSL/TLS connection
        if (defined('PDO::MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = '';
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, TIDB_USER, TIDB_PASS, $options);
        
        // Ensure application schema and timezone are active
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . TIDB_NAME . "; USE " . TIDB_NAME . ";");
        $pdo->exec("SET time_zone = '+05:30';");
        error_log("[UniPass] Connected to TiDB Cloud MySQL (" . TIDB_NAME . ").");

    } catch (PDOException $e) {
        // Both failed — show user-friendly error
        error_log("[UniPass] TiDB Cloud also failed: " . $e->getMessage());
        die("<div style='background-color:#020617;color:#f43f5e;padding:30px;font-family:sans-serif;text-align:center;border-bottom:2px solid #e11d48;'>
                <h2 style='margin-bottom:10px;'>System Error</h2>
                <p>No database connection available. Please ensure local MySQL is running or check your internet connection for TiDB Cloud access.</p>
             </div>");
    }
}

// ============================================================
// CSRF SECURITY HELPERS
// ============================================================
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================================
// HELPER: Returns correct SQL date function
// Both Local and Remote are MySQL
// ============================================================
function db_curdate() {
    return 'CURDATE()';
}
?>
