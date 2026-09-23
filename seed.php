<?php
// seed.php — Seed application users for MySQL and TiDB Cloud
require_once 'config.php';

$users = [
    ['admin',       'Administrator', password_hash('admin', PASSWORD_DEFAULT),     'admin'],
    ['guard.harsh', 'Guard Harsh',   password_hash('harsh123', PASSWORD_DEFAULT),  'gate'],
    ['guard.inder', 'Guard Inder',   password_hash('inder123', PASSWORD_DEFAULT),  'gate'],
    ['guard.preet', 'Guard Preet',   password_hash('preet123', PASSWORD_DEFAULT),  'gate'],
];

foreach ($users as $u) {
    $stmt = $pdo->prepare("INSERT INTO users (username, full_name, password_hash, role) 
                           VALUES (?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE 
                           full_name = VALUES(full_name), 
                           password_hash = VALUES(password_hash), 
                           role = VALUES(role)");
    $stmt->execute($u);
}

echo "Users seeded successfully on " . strtoupper($db_driver) . " database.\n";
?>
