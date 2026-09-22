<?php
require_once 'config.php';
$admin_hash = password_hash('admin', PASSWORD_DEFAULT);
$gate_hash = password_hash('gate', PASSWORD_DEFAULT);

$pdo->exec("INSERT IGNORE INTO users (username, password_hash, role) VALUES ('admin', '$admin_hash', 'admin')");
$pdo->exec("INSERT IGNORE INTO users (username, password_hash, role) VALUES ('gate', '$gate_hash', 'gate')");
echo "Users seeded.";
?>
