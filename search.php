<?php
// search.php
require_once 'includes/auth.php';
requireAdmin();
require_once 'config.php';

$search_query   = trim($_GET['q'] ?? '');
$filter_dept    = trim($_GET['dept'] ?? '');
$filter_status  = trim($_GET['status'] ?? '');
$filter_date    = trim($_GET['date'] ?? '');
$page           = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
$per_page       = 15;
$offset         = ($page - 1) * $per_page;

$search_results = [];
$total_records  = 0;
$message = '';
$msgType = '';

try {
    $where = [];
    $params = [];

    if (!empty($search_query)) {
        $where[] = "(name LIKE :query OR phone_number LIKE :query OR checked_in_by LIKE :query OR checked_out_by LIKE :query OR entered_by LIKE :query)";
        $params[':query'] = "%" . $search_query . "%";
    }

    if (!empty($filter_dept)) {
        $where[] = "host_department = :dept";
        $params[':dept'] = $filter_dept;
    }

    if (!empty($filter_status)) {
        $where[] = "status = :status";
        $params[':status'] = $filter_status;
    }

    if (!empty($filter_date)) {
        $where[] = "DATE(time_in) = :filter_date";
        $params[':filter_date'] = $filter_date;
    }

    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // Count total records for pagination
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM visitors $where_sql");
    $count_stmt->execute($params);
    $total_records = (int)$count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);

    // Fetch paginated records
    $stmt = $pdo->prepare("SELECT daily_seq, id, name, phone_number, host_department, purpose_details, status, time_in, time_out, checked_in_by, checked_out_by, entered_by FROM visitors $where_sql ORDER BY time_in DESC LIMIT $per_page OFFSET $offset");
    $stmt->execute($params);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
    $msgType = 'error';
}
?>

<?php include 'includes/header.php'; ?>

<div class="glass-panel p-8 rounded-2xl relative overflow-hidden mb-8">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-emerald-400 shadow-[0_0_10px_rgba(6,182,212,0.8)]"></div>

    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200 dark:border-cyan-500/30">
        <div class="p-2 bg-brand-50 dark:bg-cyan-900/40 rounded-xl text-brand-600 dark:text-cyan-400 dark:shadow-[0_0_10px_rgba(6,182,212,0.5)]">
            <i class="ph ph-clock-counter-clockwise text-2xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-bold text-slate-900 dark:text-cyan-50 tracking-tight drop-shadow-md">
                Visitor History & Audit Logs
            </h2>
            <p class="text-xs text-slate-500 dark:text-cyan-600">Search records, filter by department or date, and export reports</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-xl border flex gap-3 items-start <?php echo $msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-300'; ?>">
            <i class="ph-fill <?php echo $msgType === 'error' ? 'ph-warning-circle text-rose-500' : 'ph-check-circle text-emerald-500'; ?> text-xl shrink-0 mt-0.5"></i>
            <div>
                <h4 class="font-semibold text-sm"><?php echo $msgType === 'error' ? 'Error' : 'Success'; ?></h4>
                <p class="text-sm opacity-90"><?php echo htmlspecialchars($message); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Multi-criteria Filter Form -->
    <form method="GET" action="search.php" class="space-y-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search Text -->
            <div class="md:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500">
                    <i class="ph ph-magnifying-glass text-lg"></i>
                </div>
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" class="input-glass w-full pl-12 shadow-sm text-sm" placeholder="Search by Name, Phone, or Guard...">
            </div>

            <!-- Department Filter -->
            <div>
                <select name="dept" class="input-glass w-full text-sm">
                    <option value="">All Departments</option>
                    <option value="BCA" <?php echo $filter_dept === 'BCA' ? 'selected' : ''; ?>>BCA</option>
                    <option value="BBA" <?php echo $filter_dept === 'BBA' ? 'selected' : ''; ?>>BBA</option>
                    <option value="MCA" <?php echo $filter_dept === 'MCA' ? 'selected' : ''; ?>>MCA</option>
                    <option value="Administration" <?php echo $filter_dept === 'Administration' ? 'selected' : ''; ?>>Administration</option>
                    <option value="Accounts / Fee Section" <?php echo $filter_dept === 'Accounts / Fee Section' ? 'selected' : ''; ?>>Accounts / Fee</option>
                    <option value="Examination Cell" <?php echo $filter_dept === 'Examination Cell' ? 'selected' : ''; ?>>Exam Cell</option>
                    <option value="Library" <?php echo $filter_dept === 'Library' ? 'selected' : ''; ?>>Library</option>
                    <option value="Other" <?php echo $filter_dept === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="input-glass w-full text-sm">
                    <option value="">All Statuses</option>
                    <option value="Inside" <?php echo $filter_status === 'Inside' ? 'selected' : ''; ?>>Inside</option>
                    <option value="Checked Out" <?php echo $filter_status === 'Checked Out' ? 'selected' : ''; ?>>Checked Out</option>
                </select>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pt-2">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 dark:text-cyan-400">Date Log:</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>" class="input-glass py-1.5 px-3 text-xs">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-slate-900 dark:bg-cyan-500/10 text-white dark:text-cyan-400 hover:bg-brand-600 dark:hover:bg-cyan-500 dark:hover:text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md border border-transparent dark:border-cyan-500/50 flex items-center gap-1.5 text-xs uppercase tracking-wider">
                    <i class="ph-bold ph-funnel text-base"></i> Apply Filters
                </button>
                <?php if (!empty($search_query) || !empty($filter_dept) || !empty($filter_status) || !empty($filter_date)): ?>
                    <a href="search.php" class="bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold py-2.5 px-4 rounded-xl text-xs flex items-center hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">Reset</a>
                <?php endif; ?>
                <a href="export.php?q=<?php echo urlencode($search_query); ?>&date=<?php echo urlencode($filter_date ?: date('Y-m-d')); ?>" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-4 rounded-xl transition-all shadow-md flex items-center gap-1.5 text-xs tracking-wider">
                    <i class="ph-bold ph-download-simple text-base"></i> Export CSV
                </a>
            </div>
        </div>
    </form>
</div>

<div class="glass-panel p-8 rounded-2xl">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-slate-200 dark:border-cyan-500/30 pb-4">
        <h3 class="text-xl font-bold text-slate-800 dark:text-cyan-50">
            Records Found: <span class="text-brand-600 dark:text-cyan-400 font-mono"><?php echo $total_records; ?></span>
        </h3>
        <span class="text-xs text-slate-400">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?></span>
    </div>

    <?php if (empty($search_results)): ?>
        <div class="py-12 text-center flex flex-col items-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-cyan-900/30 flex items-center justify-center mb-4 text-slate-400 dark:text-cyan-600 border border-slate-200 dark:border-cyan-500/30">
                <i class="ph ph-files text-3xl"></i>
            </div>
            <p class="text-slate-500 dark:text-cyan-400 font-bold tracking-wide">No visitor records match the selected criteria.</p>
        </div>
    <?php else: ?>
        <div class="overflow-hidden border border-slate-200 dark:border-cyan-500/30 rounded-xl bg-white/30 dark:bg-[#050b14]/80 dark:shadow-[0_0_15px_rgba(6,182,212,0.1)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-[#0a1220] border-b border-slate-200 dark:border-cyan-500/40 text-slate-600 dark:text-cyan-400">
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Pass / Token</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Visitor Details</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Purpose</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Status</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Time Log & Guards</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider text-right">Pass</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-cyan-900/30">
                        <?php foreach ($search_results as $record): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-cyan-900/20 transition-colors duration-200">
                                <td class="p-4 text-slate-500 dark:text-cyan-500 font-mono text-xs align-top tracking-wider">
                                    <span class="font-bold text-slate-800 dark:text-cyan-200">#<?php echo str_pad($record['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                    <?php if (!empty($record['daily_seq'])): ?>
                                        <div class="text-[11px] text-brand-600 dark:text-cyan-400 font-bold">Token #<?php echo $record['daily_seq']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 align-top">
                                    <div class="text-slate-900 dark:text-cyan-50 font-bold flex items-center gap-2">
                                        <?php echo htmlspecialchars($record['name']); ?>
                                    </div>
                                    <div class="text-slate-500 dark:text-cyan-200 text-xs mt-1 flex items-center gap-1.5 font-mono tracking-wider">
                                        <i class="ph ph-phone text-xs dark:text-cyan-500"></i>
                                        <?php echo htmlspecialchars($record['phone_number']); ?>
                                    </div>
                                </td>
                                <td class="p-4 align-top max-w-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-200 text-slate-800 dark:bg-[#020617] dark:text-cyan-400 dark:border dark:border-cyan-500/50 mb-1.5">
                                        <?php echo htmlspecialchars($record['host_department']); ?>
                                    </span>
                                    <div class="text-slate-600 dark:text-cyan-300 text-xs truncate" title="<?php echo htmlspecialchars($record['purpose_details']); ?>">
                                        <?php echo htmlspecialchars($record['purpose_details']); ?>
                                    </div>
                                </td>
                                <td class="p-4 align-top">
                                    <?php if ($record['status'] === 'Inside'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-500/50">
                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div> Inside
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-600">
                                            Checked Out
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-xs align-top">
                                    <div class="flex items-center gap-1.5 text-slate-700 dark:text-cyan-100 mb-1 font-medium font-mono">
                                        <i class="ph-fill ph-arrow-circle-right text-emerald-500 dark:text-cyan-400 text-sm"></i>
                                        <span><?php echo date('d M y, h:i A', strtotime($record['time_in'])); ?></span>
                                        <span class="text-[10px] text-slate-400 font-sans">(by <?php echo htmlspecialchars($record['checked_in_by'] ?: ($record['entered_by'] ?: 'Staff')); ?>)</span>
                                    </div>
                                    <?php if ($record['time_out']): ?>
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-rose-400 font-medium font-mono">
                                            <i class="ph-fill ph-arrow-circle-left text-rose-500 text-sm"></i>
                                            <span><?php echo date('d M y, h:i A', strtotime($record['time_out'])); ?></span>
                                            <?php if (!empty($record['checked_out_by'])): ?>
                                                <span class="text-[10px] text-slate-400 uppercase font-sans">(by <?php echo htmlspecialchars($record['checked_out_by']); ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-slate-400 dark:text-cyan-700 text-xs italic pl-4">Still inside campus</div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right align-top">
                                    <a href="view_pass.php?id=<?php echo $record['id']; ?>" target="_blank" class="px-2.5 py-1 text-xs font-bold rounded bg-slate-100 text-slate-600 border border-slate-300 hover:bg-slate-200 dark:bg-cyan-900/30 dark:text-cyan-300 dark:border-cyan-700/50 dark:hover:bg-cyan-800 transition-colors inline-block">Pass</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <?php 
                $query_params = $_GET;
                unset($query_params['page']);
                $base_url = 'search.php?' . http_build_query($query_params);
                $sep = !empty($query_params) ? '&' : '';
            ?>
            <div class="flex justify-between items-center mt-6 pt-4 border-t border-slate-200 dark:border-cyan-900/50 text-xs">
                <a href="<?php echo $base_url . $sep . 'page=' . max(1, $page - 1); ?>" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 font-bold <?php echo $page <= 1 ? 'pointer-events-none opacity-40' : 'hover:bg-slate-200 dark:hover:bg-slate-700'; ?>">
                    &larr; Previous Page
                </a>
                <span class="font-bold text-slate-500 dark:text-cyan-400">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <a href="<?php echo $base_url . $sep . 'page=' . min($total_pages, $page + 1); ?>" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 font-bold <?php echo $page >= $total_pages ? 'pointer-events-none opacity-40' : 'hover:bg-slate-200 dark:hover:bg-slate-700'; ?>">
                    Next Page &rarr;
                </a>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>