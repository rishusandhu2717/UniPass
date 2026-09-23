<?php
// api/dashboard_stats.php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // 1. Metric: Total today
    $m_stmt1 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE DATE(time_in) = " . db_curdate());
    $metric_total_today = (int)$m_stmt1->fetchColumn();

    // 2. Metric: Inside
    $m_stmt2 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'Inside'");
    $metric_inside = (int)$m_stmt2->fetchColumn();

    // 3. Metric: Checked Out Today
    $m_stmt3 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'Checked Out' AND DATE(time_out) = " . db_curdate());
    $metric_checked_out = (int)$m_stmt3->fetchColumn();

    // 4. Dept breakdown
    $d_stmt = $pdo->query("SELECT host_department, COUNT(*) as cnt FROM visitors WHERE DATE(time_in) = " . db_curdate() . " GROUP BY host_department ORDER BY cnt DESC");
    $dept_stats = $d_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Active visitors
    $stmt = $pdo->query("SELECT id, daily_seq, name, phone_number, host_department, time_in, checked_in_by, entered_by FROM visitors WHERE status = 'Inside' ORDER BY time_in DESC");
    $raw_active = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $now = new DateTime();
    $active_visitors = [];
    foreach ($raw_active as $v) {
        $in_time = new DateTime($v['time_in']);
        $diff = $in_time->diff($now);
        $elapsed = '';
        if ($diff->h > 0) $elapsed .= $diff->h . 'h ';
        $elapsed .= $diff->i . 'm';

        $active_visitors[] = [
            'id' => $v['id'],
            'formatted_id' => '#' . str_pad($v['id'], 5, '0', STR_PAD_LEFT),
            'daily_seq' => $v['daily_seq'],
            'name' => $v['name'],
            'phone_number' => $v['phone_number'],
            'host_department' => $v['host_department'],
            'time_in_formatted' => date('h:i A', strtotime($v['time_in'])),
            'elapsed' => $elapsed,
            'guard' => $v['checked_in_by'] ?: ($v['entered_by'] ?: 'Staff'),
        ];
    }

    echo json_encode([
        'success' => true,
        'metrics' => [
            'total_today' => $metric_total_today,
            'inside' => $metric_inside,
            'checked_out' => $metric_checked_out,
        ],
        'dept_stats' => $dept_stats,
        'active_visitors' => $active_visitors
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
