<?php
// seed.php — Cross-DB compatible (MySQL + PostgreSQL/Supabase)
require_once 'config.php';

$admin_hash = password_hash('admin', PASSWORD_DEFAULT);
$gate_hash  = password_hash('gate', PASSWORD_DEFAULT);
$ramesh_hash = password_hash('ramesh123', PASSWORD_DEFAULT);
$suresh_hash = password_hash('suresh123', PASSWORD_DEFAULT);
$amit_hash   = password_hash('amit123', PASSWORD_DEFAULT);

if ($db_driver === 'pgsql') {
    // PostgreSQL (Supabase) — ON CONFLICT DO NOTHING instead of INSERT IGNORE
    $pdo->exec("INSERT INTO users (username, password_hash, role) VALUES ('admin', '$admin_hash', 'admin') ON CONFLICT (username) DO NOTHING");
    $pdo->exec("INSERT INTO users (username, password_hash, role) VALUES ('gate', '$gate_hash', 'gate') ON CONFLICT (username) DO NOTHING");
    $pdo->exec("INSERT INTO users (username, password_hash, role) VALUES ('ramesh', '$ramesh_hash', 'gate') ON CONFLICT (username) DO NOTHING");
    $pdo->exec("INSERT INTO users (username, password_hash, role) VALUES ('suresh', '$suresh_hash', 'gate') ON CONFLICT (username) DO NOTHING");
    $pdo->exec("INSERT INTO users (username, password_hash, role) VALUES ('amit', '$amit_hash', 'gate') ON CONFLICT (username) DO NOTHING");
} else {
    // MySQL — original INSERT IGNORE
    $pdo->exec("INSERT IGNORE INTO users (username, password_hash, role) VALUES ('admin', '$admin_hash', 'admin')");
    $pdo->exec("INSERT IGNORE INTO users (username, password_hash, role) VALUES ('gate', '$gate_hash', 'gate')");
    $pdo->exec("INSERT IGNORE INTO users (username, password_hash, role) VALUES ('ramesh', '$ramesh_hash', 'gate')");
    $pdo->exec("INSERT IGNORE INTO users (username, password_hash, role) VALUES ('suresh', '$suresh_hash', 'gate')");
    $pdo->exec("INSERT IGNORE INTO users (username, password_hash, role) VALUES ('amit', '$amit_hash', 'gate')");
}

echo "Users seeded successfully on " . strtoupper($db_driver) . " database.";
?>
