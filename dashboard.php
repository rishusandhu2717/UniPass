<?php
// dashboard.php
require_once 'includes/auth.php';
requireAdmin(); // Only admin can access dashboard
require_once 'config.php';

// Fetch Active Visitors
$active_visitors = [];
try {
    $stmt = $pdo->query("SELECT id, daily_seq, name, phone_number, host_department, time_in, checked_in_by, entered_by FROM visitors WHERE status = 'Inside' ORDER BY time_in DESC");
    $active_visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
}

// Fetch Metrics
$metric_total_today = 0;
$metric_inside = 0;
$metric_checked_out = 0;
$dept_stats = [];

try {
    $m_stmt1 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE DATE(time_in) = " . db_curdate());
    $metric_total_today = (int)$m_stmt1->fetchColumn();
    
    $m_stmt2 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'Inside'");
    $metric_inside = (int)$m_stmt2->fetchColumn();
    
    $m_stmt3 = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status = 'Checked Out' AND DATE(time_out) = " . db_curdate());
    $metric_checked_out = (int)$m_stmt3->fetchColumn();

    // Department breakdown for today
    $d_stmt = $pdo->query("SELECT host_department, COUNT(*) as cnt FROM visitors WHERE DATE(time_in) = " . db_curdate() . " GROUP BY host_department ORDER BY cnt DESC");
    $dept_stats = $d_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>

<?php include 'includes/header.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Metrics Panel -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-panel p-6 rounded-2xl relative overflow-hidden flex items-center justify-between">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-brand-500 shadow-[0_0_10px_rgba(6,182,212,0.8)]"></div>
        <div>
            <p class="text-xs font-bold text-slate-500 dark:text-cyan-500 uppercase tracking-wider mb-1">Total Visitors Today</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-cyan-50 drop-shadow-md"><?php echo $metric_total_today; ?></h3>
            <span class="text-[11px] text-slate-400">Unique gate passes issued</span>
        </div>
        <div class="w-14 h-14 bg-brand-50 dark:bg-cyan-900/40 rounded-2xl text-brand-600 dark:text-cyan-400 flex items-center justify-center border border-slate-200 dark:border-cyan-500/30">
            <i class="ph-fill ph-users text-2xl"></i>
        </div>
    </div>
    
    <div class="glass-panel p-6 rounded-2xl relative overflow-hidden flex items-center justify-between">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.8)]"></div>
        <div>
            <p class="text-xs font-bold text-slate-500 dark:text-emerald-500 uppercase tracking-wider mb-1">Currently Inside</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-emerald-400 drop-shadow-md"><?php echo $metric_inside; ?></h3>
            <span class="text-[11px] text-emerald-600 dark:text-emerald-500/80 font-medium">Active campus badges</span>
        </div>
        <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-900/40 rounded-2xl text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-slate-200 dark:border-emerald-500/30">
            <i class="ph-fill ph-user-check text-2xl"></i>
        </div>
    </div>
    
    <div class="glass-panel p-6 rounded-2xl relative overflow-hidden flex items-center justify-between">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.8)]"></div>
        <div>
            <p class="text-xs font-bold text-slate-500 dark:text-rose-500 uppercase tracking-wider mb-1">Checked Out Today</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-rose-400 drop-shadow-md"><?php echo $metric_checked_out; ?></h3>
            <span class="text-[11px] text-slate-400">Completed campus departures</span>
        </div>
        <div class="w-14 h-14 bg-rose-50 dark:bg-rose-900/40 rounded-2xl text-rose-600 dark:text-rose-400 flex items-center justify-center border border-slate-200 dark:border-rose-500/30">
            <i class="ph-fill ph-sign-out text-2xl"></i>
        </div>
    </div>
</div>

<!-- Department Footfall & Live Feed Controls -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Visual Analytics: Today's Footfall by Department -->
    <div class="glass-panel p-6 rounded-2xl lg:col-span-1 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-200 dark:border-cyan-500/30">
                <i class="ph-bold ph-chart-donut text-brand-600 dark:text-cyan-400 text-xl"></i>
                <h4 class="font-bold text-slate-800 dark:text-cyan-100 text-sm uppercase tracking-wide">Dept. Distribution</h4>
            </div>

            <?php if (empty($dept_stats)): ?>
                <div class="py-8 text-center text-slate-400 dark:text-cyan-800 text-xs">No department activity recorded today yet.</div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php 
                        $max_cnt = max(array_column($dept_stats, 'cnt')); 
                        foreach ($dept_stats as $ds):
                            $pct = $metric_total_today > 0 ? round(($ds['cnt'] / $metric_total_today) * 100) : 0;
                    ?>
                        <div>
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-slate-700 dark:text-cyan-200"><?php echo htmlspecialchars($ds['host_department']); ?></span>
                                <span class="text-slate-500 dark:text-cyan-400 font-mono"><?php echo $ds['cnt']; ?> (<?php echo $pct; ?>%)</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-brand-500 to-indigo-500 h-2 rounded-full" style="width: <?php echo max(5, $pct); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-6 pt-3 border-t border-slate-200 dark:border-cyan-900/50 flex justify-between items-center text-xs text-slate-500 dark:text-cyan-600">
            <span>Peak Activity: 10 AM - 2 PM</span>
            <a href="search.php" class="text-brand-600 dark:text-cyan-400 hover:underline font-bold">View History &rarr;</a>
        </div>
    </div>

    <!-- Active Monitors Panel (Admin Read-Only) -->
    <div class="glass-panel p-8 rounded-2xl relative overflow-hidden lg:col-span-2">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-emerald-400"></div>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-slate-200 dark:border-cyan-500/30">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-brand-50 dark:bg-cyan-900/40 rounded-xl text-brand-600 dark:text-cyan-400 dark:shadow-[0_0_10px_rgba(6,182,212,0.5)]">
                    <i class="ph ph-video-camera text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-cyan-50 tracking-tight drop-shadow-md">
                        Live Campus Occupancy
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-cyan-600">Real-time inside visitor roster</p>
                </div>
            </div>
            
            <!-- Live Feed Auto-Refresh Controls -->
            <div class="flex items-center gap-2">
                <div class="bg-white/50 dark:bg-[#08101a] backdrop-blur-sm px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-cyan-500/50 shadow-sm flex items-center gap-2">
                    <div id="live-indicator" class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-cyan-400 animate-pulse"></div>
                    <span id="refresh-status" class="text-slate-600 dark:text-cyan-300 font-bold text-xs uppercase tracking-wide">Live (15s)</span>
                </div>
                <button id="toggle-refresh-btn" onclick="toggleAutoRefresh()" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-cyan-300 transition-colors text-xs font-bold" title="Pause / Resume Live Feed">
                    <i id="pause-icon" class="ph-bold ph-pause text-base"></i>
                </button>
            </div>
        </div>

        <div class="overflow-hidden border border-slate-200 dark:border-cyan-500/30 rounded-xl bg-white/30 dark:bg-[#050b14]/80">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-[#0a1220] border-b border-slate-200 dark:border-cyan-500/40 text-slate-600 dark:text-cyan-400">
                            <th class="p-3.5 font-semibold text-xs uppercase tracking-wider">Pass ID</th>
                            <th class="p-3.5 font-semibold text-xs uppercase tracking-wider">Visitor</th>
                            <th class="p-3.5 font-semibold text-xs uppercase tracking-wider">Department</th>
                            <th class="p-3.5 font-semibold text-xs uppercase tracking-wider">Guard</th>
                            <th class="p-3.5 font-semibold text-xs uppercase tracking-wider">Time In</th>
                            <th class="p-3.5 font-semibold text-xs uppercase tracking-wider">Elapsed</th>
                            <th class="p-3.5 font-semibold text-xs uppercase tracking-wider text-right">Pass</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-cyan-900/30">
                        <?php if (empty($active_visitors)): ?>
                            <tr>
                                <td colspan="7" class="p-12 text-center text-slate-400 dark:text-cyan-700">No visitors are currently inside the campus.</td>
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
                                    <td class="p-3.5 text-slate-500 dark:text-cyan-500 text-xs font-mono align-middle">
                                        <span class="font-bold text-slate-800 dark:text-cyan-200">#<?php echo str_pad($visitor['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                        <?php if (!empty($visitor['daily_seq'])): ?>
                                            <div class="text-[10px] text-slate-400">Token #<?php echo $visitor['daily_seq']; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3.5 text-slate-900 dark:text-cyan-50 font-bold align-middle">
                                        <div><?php echo htmlspecialchars($visitor['name']); ?></div>
                                        <div class="text-[11px] font-mono text-slate-500 dark:text-cyan-300 font-normal"><?php echo htmlspecialchars($visitor['phone_number']); ?></div>
                                    </td>
                                    <td class="p-3.5 align-middle">
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200 dark:bg-[#020617] dark:text-cyan-400 dark:border-cyan-500/50"><?php echo htmlspecialchars($visitor['host_department']); ?></span>
                                    </td>
                                    <td class="p-3.5 text-slate-600 dark:text-cyan-200 text-xs font-semibold align-middle">
                                        <i class="ph-fill ph-shield-check text-brand-500 dark:text-cyan-400 mr-1"></i>
                                        <?php echo htmlspecialchars($visitor['checked_in_by'] ?: ($visitor['entered_by'] ?: 'Staff')); ?>
                                    </td>
                                    <td class="p-3.5 text-slate-500 dark:text-cyan-300 text-xs font-mono align-middle"><?php echo date('h:i A', strtotime($visitor['time_in'])); ?></td>
                                    <td class="p-3.5 text-amber-600 dark:text-amber-400 font-mono text-xs font-bold align-middle"><?php echo $elapsed; ?></td>
                                    <td class="p-3.5 text-right align-middle">
                                        <a href="view_pass.php?id=<?php echo $visitor['id']; ?>" target="_blank" class="px-2.5 py-1 text-xs font-bold rounded bg-slate-100 text-slate-600 border border-slate-300 hover:bg-slate-200 dark:bg-cyan-900/30 dark:text-cyan-300 dark:border-cyan-700/50 dark:hover:bg-cyan-800 transition-colors inline-block">Pass</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Live Auto-Refresh Logic (15 seconds)
    let countdown = 15;
    let isPaused = false;
    const statusEl = document.getElementById('refresh-status');
    const pauseIcon = document.getElementById('pause-icon');
    const indicator = document.getElementById('live-indicator');

    const interval = setInterval(() => {
        if (!isPaused) {
            countdown--;
            if (statusEl) statusEl.textContent = 'Live (' + countdown + 's)';
            if (countdown <= 0) {
                window.location.reload();
            }
        }
    }, 1000);

    function toggleAutoRefresh() {
        isPaused = !isPaused;
        if (isPaused) {
            statusEl.textContent = 'Paused';
            pauseIcon.classList.remove('ph-pause');
            pauseIcon.classList.add('ph-play');
            indicator.classList.remove('animate-pulse');
            indicator.classList.add('bg-amber-400');
        } else {
            countdown = 15;
            statusEl.textContent = 'Live (15s)';
            pauseIcon.classList.remove('ph-play');
            pauseIcon.classList.add('ph-pause');
            indicator.classList.add('animate-pulse');
            indicator.classList.remove('bg-amber-400');
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
