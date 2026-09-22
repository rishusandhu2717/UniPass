<?php
// dashboard.php
require_once 'includes/auth.php';
requireAdmin(); // Only admin can access dashboard
require_once 'config.php';

// Fetch Active Visitors
$active_visitors = [];
try {
    $stmt = $pdo->query("SELECT id, name, phone_number, host_department, time_in FROM visitors WHERE status = 'Inside' ORDER BY time_in DESC");
    $active_visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
}

// Fetch Metrics
$metric_total_today = 0;
$metric_inside = 0;
$metric_checked_out = 0;

try {
    $m_stmt1 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE DATE(time_in) = CURRENT_DATE");
    $metric_total_today = $m_stmt1->fetchColumn();
    
    $m_stmt2 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'Inside'");
    $metric_inside = $m_stmt2->fetchColumn();
    
    $m_stmt3 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'Checked Out' AND DATE(time_out) = CURRENT_DATE");
    $metric_checked_out = $m_stmt3->fetchColumn();
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>

<?php include 'includes/header.php'; ?>

<!-- Metrics Panel -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-panel p-6 rounded-2xl relative overflow-hidden flex items-center justify-between">
        <div class="absolute top-0 left-0 w-1 h-full bg-brand-500 shadow-[0_0_10px_rgba(6,182,212,0.8)]"></div>
        <div>
            <p class="text-sm font-bold text-slate-500 dark:text-cyan-600 uppercase tracking-wider mb-1">Total Visitors Today</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-cyan-50 drop-shadow-md"><?php echo $metric_total_today; ?></h3>
        </div>
        <div class="w-14 h-14 bg-brand-50 dark:bg-cyan-900/40 rounded-xl text-brand-600 dark:text-cyan-400 flex items-center justify-center border dark:border-cyan-500/30">
            <i class="ph-fill ph-users text-2xl"></i>
        </div>
    </div>
    
    <div class="glass-panel p-6 rounded-2xl relative overflow-hidden flex items-center justify-between">
        <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.8)]"></div>
        <div>
            <p class="text-sm font-bold text-slate-500 dark:text-emerald-600 uppercase tracking-wider mb-1">Currently Inside</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-emerald-50 drop-shadow-md"><?php echo $metric_inside; ?></h3>
        </div>
        <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/40 rounded-xl text-emerald-600 dark:text-emerald-400 flex items-center justify-center border dark:border-emerald-500/30">
            <i class="ph-fill ph-user-check text-2xl"></i>
        </div>
    </div>
    
    <div class="glass-panel p-6 rounded-2xl relative overflow-hidden flex items-center justify-between">
        <div class="absolute top-0 left-0 w-1 h-full bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.8)]"></div>
        <div>
            <p class="text-sm font-bold text-slate-500 dark:text-rose-600 uppercase tracking-wider mb-1">Checked Out Today</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-rose-50 drop-shadow-md"><?php echo $metric_checked_out; ?></h3>
        </div>
        <div class="w-14 h-14 bg-rose-50 dark:bg-rose-900/40 rounded-xl text-rose-600 dark:text-rose-400 flex items-center justify-center border dark:border-rose-500/30">
            <i class="ph-fill ph-sign-out text-2xl"></i>
        </div>
    </div>
</div>

<!-- Active Monitors Panel (Admin Read-Only) -->
<div class="glass-panel p-8 rounded-2xl relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-emerald-400"></div>

    <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-200 dark:border-cyan-500/30">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-brand-50 dark:bg-cyan-900/40 rounded-lg text-brand-600 dark:text-cyan-400 dark:shadow-[0_0_10px_rgba(6,182,212,0.5)]">
                <i class="ph ph-video-camera text-2xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-slate-900 dark:text-cyan-50 tracking-tight drop-shadow-md">
                Live Campus Occupancy
            </h2>
        </div>
        <div class="bg-white/50 dark:bg-[#08101a] backdrop-blur-sm px-4 py-2 rounded-xl border border-slate-200 dark:border-cyan-500/50 shadow-sm flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-cyan-400 animate-pulse"></div>
            <span class="text-slate-600 dark:text-cyan-300 font-bold text-sm uppercase tracking-wide">Live Feed</span>
        </div>
    </div>

    <div class="overflow-hidden border border-slate-200 dark:border-cyan-500/30 rounded-xl bg-white/30 dark:bg-[#050b14]/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 dark:bg-[#0a1220] border-b border-slate-200 dark:border-cyan-500/40 text-slate-600 dark:text-cyan-400">
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">ID</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Name</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Phone</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Department</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Time In</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Elapsed</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider text-right">View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-cyan-900/30">
                    <?php if (empty($active_visitors)): ?>
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400 dark:text-cyan-700">No visitors are currently inside.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($active_visitors as $visitor): ?>
                            <?php 
                                $in_time = new DateTime($visitor['time_in']);
                                $now = new DateTime();
                                $diff = $in_time->diff($now);
                                $elapsed = '';
                                if ($diff->h > 0) $elapsed .= $diff->h . 'h ';
                                $elapsed .= $diff->i . 'm';
                            ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-cyan-900/20 transition-colors">
                                <td class="p-4 text-slate-500 dark:text-cyan-500 text-sm font-mono align-middle">#<?php echo str_pad($visitor['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td class="p-4 text-slate-900 dark:text-cyan-50 font-bold align-middle"><?php echo htmlspecialchars($visitor['name']); ?></td>
                                <td class="p-4 text-slate-600 dark:text-cyan-200 text-sm align-middle"><?php echo htmlspecialchars($visitor['phone_number']); ?></td>
                                <td class="p-4 align-middle">
                                    <span class="inline-flex px-2 py-1 rounded text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200 dark:bg-[#020617] dark:text-cyan-400 dark:border-cyan-500/50"><?php echo htmlspecialchars($visitor['host_department']); ?></span>
                                </td>
                                <td class="p-4 text-slate-500 dark:text-cyan-300 text-sm align-middle"><?php echo date('h:i A', strtotime($visitor['time_in'])); ?></td>
                                <td class="p-4 text-amber-600 dark:text-amber-400 font-mono text-sm font-bold align-middle"><?php echo $elapsed; ?></td>
                                <td class="p-4 text-right align-middle">
                                    <a href="view_pass.php?id=<?php echo $visitor['id']; ?>" target="_blank" class="px-3 py-1.5 text-xs font-bold rounded bg-slate-100 text-slate-600 border border-slate-300 hover:bg-slate-200 dark:bg-cyan-900/30 dark:text-cyan-300 dark:border-cyan-700/50 dark:hover:bg-cyan-800 transition-colors inline-block">View Pass</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
