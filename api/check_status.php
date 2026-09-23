<?php
// api/check_status.php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    $stmt = $pdo->prepare("SELECT id, name, host_department, status, time_in, time_out, entered_by, checked_out_by FROM visitors WHERE id = ?");
    $stmt->execute([$id]);
    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($visitor) {
        if ($visitor['time_in']) {
            $visitor['formatted_time_in'] = date('h:i A \o\n M d', strtotime($visitor['time_in']));
        }
        if ($visitor['time_out']) {
            $visitor['formatted_time_out'] = date('h:i A \o\n M d', strtotime($visitor['time_out']));
        }
        echo json_encode(['success' => true, 'data' => $visitor]);
    } else {
        echo json_encode(['error' => 'Visitor not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
