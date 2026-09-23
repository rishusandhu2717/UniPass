<?php
// export.php
require_once 'includes/auth.php';
requireAdmin(); // Only admin can export logs
require_once 'config.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$date_filter  = isset($_GET['date']) ? trim($_GET['date']) : '';

// Filename
$date_label = !empty($date_filter) ? $date_filter : 'all_' . date('Y-m-d');
$filename = 'UniPass_visitors_' . $date_label . '.csv';

// Set headers to force download of CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// BOM for Excel to correctly detect UTF-8 characters
fputs($output, "\xEF\xBB\xBF");

// Column headings
fputcsv($output, [
    'Daily Token No', 
    'Visitor Pass ID', 
    'Full Name', 
    'Phone Number', 
    'Host Department', 
    'Purpose of Visit', 
    'Status', 
    'Time In', 
    'Time Out',
    'Entered By (Gate Staff)',
    'Checked Out By (Gate Staff)'
]);

try {
    $params = [];
    $where_clauses = [];

    if (!empty($date_filter)) {
        $where_clauses[] = "DATE(time_in) = :date_filter";
        $params[':date_filter'] = $date_filter;
    }

    if (!empty($search_query)) {
        $like_query = "%" . $search_query . "%";
        $where_clauses[] = "(name LIKE :query OR phone_number LIKE :query OR entered_by LIKE :query)";
        $params[':query'] = $like_query;
    }

    $where = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : "";
    $sql = "SELECT daily_seq, id, name, phone_number, host_department, purpose_details, status, time_in, time_out, entered_by, checked_out_by 
            FROM visitors 
            $where 
            ORDER BY time_in DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['daily_seq'] ? '#' . $row['daily_seq'] : '-',
            '#' . str_pad($row['id'], 5, '0', STR_PAD_LEFT),
            $row['name'],
            $row['phone_number'],
            $row['host_department'],
            $row['purpose_details'],
            $row['status'],
            $row['time_in'] ? date('d-m-Y h:i A', strtotime($row['time_in'])) : '-',
            $row['time_out'] ? date('d-m-Y h:i A', strtotime($row['time_out'])) : '-',
            $row['entered_by'] ?? 'Staff',
            $row['checked_out_by'] ?? '-',
        ]);
    }
} catch (PDOException $e) {
    fputcsv($output, ['Error', $e->getMessage()]);
}

fclose($output);
exit();
?>
