<?php
// search.php
require_once 'includes/auth.php';
requireAdmin();
require_once 'config.php';

$search_query = trim($_GET['q'] ?? '');
$search_results = [];
$message = '';
$msgType = '';
$is_search = !empty($search_query);

try {
    if ($is_search) {
        $like_query = "%" . $search_query . "%";
        $stmt = $pdo->prepare("SELECT daily_seq, id, name, phone_number, host_department, purpose_details, status, time_in, time_out FROM visitors WHERE name LIKE :query OR phone_number LIKE :query ORDER BY time_in DESC");
        $stmt->execute([':query' => $like_query]);
    } else {
        // Default: Fetch latest 20 records
        $stmt = $pdo->prepare("SELECT daily_seq, id, name, phone_number, host_department, purpose_details, status, time_in, time_out FROM visitors ORDER BY time_in DESC LIMIT 20");
        $stmt->execute();
    }
    
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
    $msgType = 'error';
}
?>

<?php include 'includes/header.php'; ?>

<div class="glass-panel p-8 rounded-2xl relative overflow-hidden mb-8">
    <!-- Decorative accent line -->
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-emerald-400 shadow-[0_0_10px_rgba(6,182,212,0.8)]"></div>

    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-200 dark:border-cyan-500/30">
        <div class="p-2 bg-brand-50 dark:bg-cyan-900/40 rounded-lg text-brand-600 dark:text-cyan-400 dark:shadow-[0_0_10px_rgba(6,182,212,0.5)]">
            <i class="ph ph-clock-counter-clockwise text-2xl"></i>
        </div>
        <h2 class="text-3xl font-bold text-slate-900 dark:text-cyan-50 tracking-tight drop-shadow-md">
            Visitor History
        </h2>
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

    <form method="GET" action="search.php" class="flex flex-col md:flex-row gap-4 mb-3">
        <div class="flex-grow relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </div>
            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" class="input-glass w-full pl-12 shadow-sm" placeholder="Search by Visitor Name or Phone Number...">
        </div>
        <button type="submit" class="bg-slate-900 dark:bg-cyan-500/10 text-white dark:text-cyan-400 hover:bg-brand-600 dark:hover:bg-cyan-500 dark:hover:text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg border border-transparent dark:border-cyan-500/50 hover:shadow-brand-500/30 dark:hover:shadow-[0_0_20px_rgba(6,182,212,0.6)] whitespace-nowrap flex items-center justify-center gap-2 uppercase tracking-widest">
            <i class="ph-bold ph-scan text-lg"></i> Scan Records
        </button>
    </form>

    <div class="flex justify-between items-center px-1">
        <p class="text-xs text-slate-500 dark:text-cyan-600 flex items-center gap-1.5 font-semibold tracking-wide">
            <i class="ph-fill ph-database text-slate-400 dark:text-cyan-500"></i> Searching all historical access logs.
        </p>
        <a href="export.php?q=<?php echo urlencode($search_query); ?>" class="text-xs font-bold text-brand-600 dark:text-cyan-400 hover:text-brand-800 dark:hover:text-cyan-200 transition-colors flex items-center gap-1.5 border border-brand-200 dark:border-cyan-500/50 px-3 py-1.5 rounded-lg bg-brand-50/50 dark:bg-cyan-900/20 hover:bg-brand-100 dark:hover:bg-cyan-900/40 dark:shadow-[0_0_8px_rgba(6,182,212,0.3)]">
            <i class="ph-bold ph-download-simple"></i> Export (CSV)
        </a>
    </div>
</div>

<!-- Excel Export by Date Panel -->
<div class="glass-panel p-6 rounded-2xl relative overflow-hidden mb-8">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 to-cyan-400"></div>
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/40 rounded-lg text-emerald-600 dark:text-emerald-400">
                <i class="ph-fill ph-microsoft-excel-logo text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 dark:text-emerald-50 text-lg">Export to Excel</h3>
                <p class="text-xs text-slate-500 dark:text-emerald-600 font-medium">Download daily visitor log as Excel-compatible CSV</p>
            </div>
        </div>
        <form method="GET" action="export.php" class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="flex items-center gap-2">
                <label class="text-sm font-bold text-slate-600 dark:text-cyan-300 whitespace-nowrap">Select Date:</label>
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" class="input-glass text-sm py-2">
            </div>
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-emerald-500/30 flex items-center gap-2 whitespace-nowrap">
                <i class="ph-bold ph-download-simple text-lg"></i> Download Excel
            </button>
        </form>
    </div>
</div>

<div class="glass-panel p-8 rounded-2xl">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-slate-200 dark:border-cyan-500/30 pb-4">
        <h3 class="text-xl font-bold text-slate-800 dark:text-cyan-50">
            <?php if ($is_search): ?>
                Search Results for "<span class="text-brand-600 dark:text-cyan-400 drop-shadow-[0_0_5px_rgba(6,182,212,0.8)]"><?php echo htmlspecialchars($search_query); ?></span>"
            <?php else: ?>
                Recent Records <span class="text-sm font-medium text-slate-400 dark:text-cyan-600 ml-2">(Latest 10)</span>
            <?php endif; ?>
        </h3>
        
        <?php if (!empty($search_results) && $is_search): ?>
            <a href="export.php?q=<?php echo urlencode($search_query); ?>" class="text-sm font-bold text-slate-700 dark:text-cyan-400 hover:text-brand-600 dark:hover:text-cyan-200 transition-colors flex items-center gap-2 border border-slate-300 dark:border-cyan-500/50 px-4 py-2 rounded-xl bg-white/50 dark:bg-cyan-900/20 hover:bg-slate-50 dark:hover:bg-cyan-900/40 shadow-sm dark:shadow-[0_0_10px_rgba(6,182,212,0.3)]">
                <i class="ph-bold ph-file-csv text-lg text-emerald-500 dark:text-cyan-400"></i> Export Results
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($search_results)): ?>
        <div class="py-12 text-center flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-cyan-900/30 flex items-center justify-center mb-4 text-slate-400 dark:text-cyan-600 border dark:border-cyan-500/30">
                <i class="ph ph-files text-3xl"></i>
            </div>
            <p class="text-slate-500 dark:text-cyan-400 font-bold tracking-wide">No records found.</p>
        </div>
    <?php else: ?>
        <div class="overflow-hidden border border-slate-200 dark:border-cyan-500/30 rounded-xl bg-white/30 dark:bg-[#050b14]/80 dark:shadow-[0_0_15px_rgba(6,182,212,0.1)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-[#0a1220] border-b border-slate-200 dark:border-cyan-500/40 text-slate-600 dark:text-cyan-400">
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">S.No</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Visitor Details</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Purpose</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Status</th>
                            <th class="p-4 font-semibold text-xs uppercase tracking-wider">Time Log</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-cyan-900/30">
                        <?php foreach ($search_results as $record): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-cyan-900/20 transition-colors duration-200">
                                <td class="p-4 text-slate-500 dark:text-cyan-500 font-mono text-xs align-top tracking-wider">
                                    <?php if (!empty($record['daily_seq'])): ?>
                                        <span class="font-black text-slate-700 dark:text-cyan-300">#<?php echo $record['daily_seq']; ?></span>
                                        <div class="text-[10px] text-slate-400 dark:text-cyan-700"><?php echo str_pad($record['id'], 5, '0', STR_PAD_LEFT); ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 align-top">
                                    <div class="text-slate-900 dark:text-cyan-50 font-bold flex items-center gap-2">
                                        <?php echo htmlspecialchars($record['name']); ?>
                                    </div>
                                    <div class="text-slate-500 dark:text-cyan-200 text-sm mt-1 flex items-center gap-1.5 tracking-widest">
                                        <i class="ph ph-phone text-xs dark:text-cyan-500"></i>
                                        <?php echo htmlspecialchars($record['phone_number']); ?>
                                    </div>
                                </td>
                                <td class="p-4 align-top max-w-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-200 text-slate-800 dark:bg-[#020617] dark:text-cyan-400 dark:border dark:border-cyan-500/50 mb-1.5 dark:shadow-[0_0_5px_rgba(6,182,212,0.3)]">
                                        <?php echo htmlspecialchars($record['host_department']); ?>
                                    </span>
                                    <div class="text-slate-600 dark:text-cyan-300 text-sm truncate" title="<?php echo htmlspecialchars($record['purpose_details']); ?>">
                                        <?php echo htmlspecialchars($record['purpose_details']); ?>
                                    </div>
                                </td>
                                <td class="p-4 align-top">
                                    <?php if ($record['status'] === 'Inside'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-500/50">
                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div> Inside
                                        </span>
                                    <?php elseif ($record['status'] === 'Pending'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-500/50">
                                            <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div> Pending
                                        </span>
                                    <?php elseif ($record['status'] === 'Rejected'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-500/50">
                                            Rejected
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-600">
                                            Checked Out
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-sm align-top">
                                    <div class="flex items-center gap-1.5 text-slate-700 dark:text-cyan-100 mb-1 font-medium">
                                        <i class="ph-fill ph-arrow-circle-right text-emerald-500 dark:text-cyan-400 text-xs"></i>
                                        <?php if ($record['time_in']): ?>
                                            <span><?php echo date('d M y, h:i A', strtotime($record['time_in'])); ?></span>
                                        <?php else: ?>
                                            <span>--</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($record['time_out']): ?>
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-cyan-500 font-medium">
                                            <i class="ph-fill ph-arrow-circle-left text-rose-400 text-xs"></i>
                                            <span><?php echo date('d M y, h:i A', strtotime($record['time_out'])); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-slate-400 dark:text-cyan-700 text-xs italic pl-4">--</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>