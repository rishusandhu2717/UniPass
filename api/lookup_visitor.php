<?php
// api/lookup_visitor.php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$phone = trim($_GET['phone'] ?? '');

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo json_encode(['found' => false]);
    exit();
}

try {
    // Check if currently inside
    $insideStmt = $pdo->prepare("SELECT id FROM visitors WHERE phone_number = ? AND status = 'Inside' LIMIT 1");
    $insideStmt->execute([$phone]);
    if ($insideStmt->fetch()) {
        echo json_encode(['found' => false, 'is_inside' => true]);
        exit();
    }

    // Lookup latest previous visit details
    $stmt = $pdo->prepare("SELECT name, host_department, purpose_details FROM visitors WHERE phone_number = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$phone]);
    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($visitor) {
        echo json_encode([
            'found' => true,
            'name' => $visitor['name'],
            'department' => $visitor['host_department'],
            'purpose' => $visitor['purpose_details']
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
