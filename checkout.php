<?php
// checkout.php
require_once 'includes/auth.php'; // Enforce authentication
require_once 'config.php';

$message = '';
$msgType = '';
$search_query = trim($_GET['q'] ?? '');
$target_qr_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$qr_visitor = null;

// If arrived via QR Code scan or link
if ($target_qr_id && isset($_GET['action']) && $_GET['action'] === 'checkout') {
    $stmt = $pdo->prepare("SELECT id, name, phone_number, host_department, time_in, status FROM visitors WHERE id = ?");
    $stmt->execute([$target_qr_id]);
    $qr_visitor = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Checkout Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submitted_token)) {
        $message = 'Security validation failed (Invalid CSRF token).';
        $msgType = 'error';
    } else {
        $visitor_id = filter_var($_POST['visitor_id'], FILTER_VALIDATE_INT);
        if ($visitor_id) {
            try {
                $guard_name = $_SESSION['username'] ?? 'Staff';
                $stmt = $pdo->prepare("UPDATE visitors SET status = 'Checked Out', time_out = CURRENT_TIMESTAMP, checked_out_by = :guard WHERE id = :id AND status = 'Inside'");
                $stmt->execute([
                    ':id'    => $visitor_id,
                    ':guard' => $guard_name,
                ]);

                if ($stmt->rowCount() > 0) {
                    $message = "Visitor #$visitor_id successfully checked out by $guard_name.";
                    $msgType = 'success';
                    $qr_visitor = null; // Clear QR card after checkout
                } else {
                    $message = 'Visitor could not be checked out (may already be checked out or invalid ID).';
                    $msgType = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $msgType = 'error';
            }
        }
    }
}

// Fetch Active Visitors
$active_visitors = [];
try {
    if (!empty($search_query)) {
        $like_query = "%" . $search_query . "%";
        $stmt = $pdo->prepare("SELECT id, daily_seq, name, phone_number, host_department, time_in, entered_by FROM visitors WHERE status = 'Inside' AND (name LIKE :query OR phone_number LIKE :query OR id = :exact_id) ORDER BY time_in DESC");
        $stmt->execute([
            ':query'    => $like_query,
            ':exact_id' => is_numeric($search_query) ? (int)$search_query : 0
        ]);
        $active_visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->query("SELECT id, daily_seq, name, phone_number, host_department, time_in, entered_by FROM visitors WHERE status = 'Inside' ORDER BY time_in DESC");
        $active_visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $message = 'Database error fetching active visitors.';
    $msgType = 'error';
}
?>

<?php include 'includes/header.php'; ?>

<!-- Direct QR Scan Confirmation Modal / Card -->
<?php if ($qr_visitor): ?>
    <div class="glass-panel p-6 rounded-2xl relative overflow-hidden mb-8 border-2 border-brand-500 shadow-xl dark:shadow-[0_0_20px_rgba(6,182,212,0.3)] animate-fade-in">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-cyan-900/40 flex items-center justify-center text-brand-600 dark:text-cyan-400">
                    <i class="ph-bold ph-qr-code text-2xl"></i>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-cyan-400">Scanned Pass Ready For Checkout</span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo htmlspecialchars($qr_visitor['name']); ?></h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pass #<?php echo str_pad($qr_visitor['id'], 5, '0', STR_PAD_LEFT); ?> &bull; Dept: <?php echo htmlspecialchars($qr_visitor['host_department']); ?> &bull; Status: <strong class="<?php echo $qr_visitor['status'] === 'Inside' ? 'text-emerald-500' : 'text-slate-500'; ?>"><?php echo htmlspecialchars($qr_visitor['status']); ?></strong></p>
                </div>
            </div>

            <?php if ($qr_visitor['status'] === 'Inside'): ?>
                <form method="POST" action="checkout.php" class="flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                    <input type="hidden" name="action" value="checkout">
                    <input type="hidden" name="visitor_id" value="<?php echo $qr_visitor['id']; ?>">
                    <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-rose-500/30 flex items-center gap-2">
                        <i class="ph-bold ph-sign-out text-lg"></i> Confirm Check-Out Now
                    </button>
                    <a href="checkout.php" class="bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold py-3 px-4 rounded-xl transition-all">Dismiss</a>
                </form>
            <?php else: ?>
                <span class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-400 text-xs font-bold">Already Checked Out</span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="glass-panel p-8 rounded-2xl relative overflow-hidden mb-8">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-500 to-orange-400 shadow-[0_0_10px_rgba(244,63,94,0.8)]"></div>

    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-200 dark:border-rose-500/30">
        <div class="p-2 bg-rose-50 dark:bg-rose-900/40 rounded-lg text-rose-600 dark:text-rose-400 dark:shadow-[0_0_10px_rgba(244,63,94,0.5)]">
            <i class="ph ph-sign-out text-2xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-bold text-slate-900 dark:text-rose-50 tracking-tight drop-shadow-md">
                Visitor Check-Out
            </h2>
            <p class="text-xs text-slate-500 dark:text-rose-300 mt-0.5">Process departures & log exit timestamps</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-xl border flex gap-3 items-start <?php echo $msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-300'; ?>">
            <i class="ph-fill <?php echo $msgType === 'error' ? 'ph-warning-circle text-rose-500' : 'ph-check-circle text-emerald-500'; ?> text-xl shrink-0 mt-0.5"></i>
            <div>
                <h4 class="font-semibold text-sm"><?php echo $msgType === 'error' ? 'Notice' : 'Success'; ?></h4>
                <p class="text-sm opacity-90"><?php echo htmlspecialchars($message); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <form method="GET" action="checkout.php" class="flex flex-col md:flex-row gap-4 mb-3">
        <div class="flex-grow relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-rose-500">
                <i class="ph ph-magnifying-glass text-lg"></i>
            </div>
            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" class="input-glass w-full pl-12 shadow-sm focus:ring-rose-500 dark:focus:ring-rose-400 dark:border-rose-500/50" placeholder="Search active visitors by Name, Phone, or Pass ID...">
        </div>
        <button type="submit" class="bg-slate-900 dark:bg-rose-500/10 text-white dark:text-rose-400 hover:bg-rose-600 dark:hover:bg-rose-500 dark:hover:text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg border border-transparent dark:border-rose-500/50 hover:shadow-rose-500/30 dark:hover:shadow-[0_0_20px_rgba(244,63,94,0.6)] whitespace-nowrap flex items-center justify-center gap-2 uppercase tracking-widest">
            <i class="ph-bold ph-funnel text-lg"></i> Filter
        </button>
        <?php if (!empty($search_query)): ?>
            <a href="checkout.php" class="bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold py-3 px-4 rounded-xl flex items-center justify-center hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="glass-panel p-8 rounded-2xl">
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-200 dark:border-cyan-500/30">
        <h3 class="text-xl font-bold text-slate-800 dark:text-cyan-50">Active Campus Occupancy</h3>
        <div class="bg-white/50 dark:bg-[#08101a] backdrop-blur-sm px-4 py-2 rounded-xl border border-slate-200 dark:border-cyan-500/50 shadow-sm flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-cyan-400 animate-pulse"></div>
            <span class="text-slate-600 dark:text-cyan-300 font-bold text-sm uppercase tracking-wide">Inside:</span>
            <span class="text-slate-900 dark:text-cyan-100 font-black text-xl drop-shadow-[0_0_5px_rgba(6,182,212,0.8)]"><?php echo count($active_visitors); ?></span>
        </div>
    </div>

    <div class="overflow-hidden border border-slate-200 dark:border-cyan-500/30 rounded-xl bg-white/30 dark:bg-[#050b14]/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 dark:bg-[#0a1220] border-b border-slate-200 dark:border-cyan-500/40 text-slate-600 dark:text-cyan-400">
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Pass ID</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Visitor</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Department</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Check-In By</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider">Time In</th>
                        <th class="p-4 font-semibold text-xs uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-cyan-900/30">
                    <?php if (empty($active_visitors)): ?>
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400 dark:text-cyan-700">No active visitors inside campus.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($active_visitors as $visitor): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-cyan-900/20 transition-colors">
                                <td class="p-4 text-slate-500 dark:text-cyan-500 text-sm font-mono align-middle">
                                    <span class="font-bold text-slate-800 dark:text-cyan-200">#<?php echo str_pad($visitor['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                    <?php if (!empty($visitor['daily_seq'])): ?>
                                        <span class="block text-[11px] text-slate-400">Token #<?php echo $visitor['daily_seq']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="text-slate-900 dark:text-cyan-50 font-bold"><?php echo htmlspecialchars($visitor['name']); ?></div>
                                    <div class="text-slate-500 dark:text-cyan-200 text-xs mt-0.5 tracking-wider font-mono"><?php echo htmlspecialchars($visitor['phone_number']); ?></div>
                                </td>
                                <td class="p-4 align-middle">
                                    <span class="inline-flex px-2 py-1 rounded text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200 dark:bg-[#020617] dark:text-cyan-400 dark:border-cyan-500/50"><?php echo htmlspecialchars($visitor['host_department']); ?></span>
                                </td>
                                <td class="p-4 text-slate-600 dark:text-cyan-300 text-xs font-semibold align-middle uppercase">
                                    <i class="ph-fill ph-shield-check text-brand-500 mr-0.5"></i>
                                    <?php echo htmlspecialchars($visitor['entered_by'] ?? 'Staff'); ?>
                                </td>
                                <td class="p-4 text-slate-500 dark:text-cyan-300 text-sm align-middle font-mono"><?php echo date('h:i A', strtotime($visitor['time_in'])); ?></td>
                                <td class="p-4 text-right align-middle">
                                    <div class="flex justify-end gap-2">
                                        <a href="view_pass.php?id=<?php echo $visitor['id']; ?>" target="_blank" class="px-3 py-1.5 text-xs font-bold rounded bg-slate-100 text-slate-600 border border-slate-300 hover:bg-slate-200 dark:bg-cyan-900/30 dark:text-cyan-300 dark:border-cyan-700/50 dark:hover:bg-cyan-800 transition-colors inline-block">Pass</a>
                                        
                                        <form method="POST" action="checkout.php" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                                            <input type="hidden" name="action" value="checkout">
                                            <input type="hidden" name="visitor_id" value="<?php echo $visitor['id']; ?>">
                                            <button type="submit" onclick="return confirm('Confirm check-out for <?php echo htmlspecialchars(addslashes($visitor['name'])); ?>?');" class="px-3.5 py-1.5 text-xs font-bold rounded bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-500 hover:text-white dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-500/50 dark:hover:bg-rose-500 dark:hover:text-white transition-all shadow-sm">Check Out</button>
                                        </form>
                                    </div>
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
