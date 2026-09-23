<?php
// seed.php — Cross-compatible for MySQL and TiDB Cloud
require_once 'config.php';

$admin_hash  = password_hash('admin', PASSWORD_DEFAULT);
$gate_hash   = password_hash('gate', PASSWORD_DEFAULT);
$ramesh_hash = password_hash('ramesh123', PASSWORD_DEFAULT);
$suresh_hash = password_hash('suresh123', PASSWORD_DEFAULT);
$amit_hash   = password_hash('amit123', PASSWORD_DEFAULT);

$users = [
    ['admin', $admin_hash, 'admin'],
    ['gate', $gate_hash, 'gate'],
    ['ramesh', $ramesh_hash, 'gate'],
    ['suresh', $suresh_hash, 'gate'],
    ['amit', $amit_hash, 'gate']
];

foreach ($users as $u) {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)");
    $stmt->execute($u);
}

echo "Users seeded successfully on " . strtoupper($db_driver) . " database.\n";
?>
