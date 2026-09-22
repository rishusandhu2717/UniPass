<?php
// api/check_status.php
header('Content-Type: application/json');

// We need config, but auth might redirect if not logged in.
// Since the Kiosk is logged in as 'gate', the session should be valid.
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing ID']);
    exit();
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['error' => 'Invalid ID']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, name, host_department, status, time_in FROM visitors WHERE id = ?");
    $stmt->execute([$id]);
    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($visitor) {
        // Format time if they are approved
        if ($visitor['status'] === 'Inside' && $visitor['time_in']) {
            $visitor['formatted_time'] = date('h:i A \o\n M d', strtotime($visitor['time_in']));
        }
        echo json_encode(['success' => true, 'data' => $visitor]);
    } else {
        echo json_encode(['error' => 'Visitor not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
