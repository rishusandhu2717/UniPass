<?php
// checkin.php
require_once 'includes/auth.php';
require_once 'config.php';

$message = '';
$msgType = '';
$visitor_id = null;
$visitor_name = '';
$visitor_dept = '';
$visitor_time_in = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = trim($_POST['name'] ?? '');
    $phone      = trim($_POST['phone_number'] ?? '');
    $department = trim($_POST['host_department'] ?? '');
    $purpose    = trim($_POST['purpose_details'] ?? '');

    if (empty($name) || empty($phone) || empty($department) || empty($purpose)) {
        $message = 'All fields are required.';
        $msgType = 'error';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = 'Phone number must be exactly 10 digits.';
        $msgType = 'error';
    } else {
        try {
            // Check if already inside
            $check_stmt = $pdo->prepare("SELECT id FROM visitors WHERE phone_number = :phone AND status = 'Inside'");
            $check_stmt->execute([':phone' => $phone]);

            if ($check_stmt->rowCount() > 0) {
                $message = 'This visitor is already checked in and currently inside the campus.';
                $msgType = 'error';
            } else {
                // Calculate daily_seq: count of today's entries + 1
                $seq_stmt = $pdo->query("SELECT COUNT(*) FROM visitors WHERE DATE(time_in) = " . db_curdate());
                $today_count = (int)$seq_stmt->fetchColumn();
                $daily_seq = $today_count + 1;

                // Get current user from session
                $entered_by = $_SESSION['username'] ?? 'Unknown';

                // Insert visitor directly as 'Inside' with daily_seq and entered_by
                $stmt = $pdo->prepare("INSERT INTO visitors (name, phone_number, host_department, purpose_details, status, time_in, daily_seq, entered_by) VALUES (:name, :phone, :dept, :purpose, 'Inside', CURRENT_TIMESTAMP, :seq, :entered_by)"); // CURRENT_TIMESTAMP is standard SQL (works on both MySQL & PostgreSQL)
                $stmt->execute([
                    ':name'       => $name,
                    ':phone'      => $phone,
                    ':dept'       => $department,
                    ':purpose'    => $purpose,
                    ':seq'        => $daily_seq,
                    ':entered_by' => $entered_by,
                ]);

                $visitor_id = $pdo->lastInsertId();

                if ($visitor_id) {
                    $message        = 'Visitor checked in successfully.';
                    $msgType        = 'success';
                    $visitor_name   = $name;
                    $visitor_dept   = $department;

                    $t_stmt = $pdo->prepare("SELECT time_in FROM visitors WHERE id = ?");
                    $t_stmt->execute([$visitor_id]);
                    $t_res = $t_stmt->fetch(PDO::FETCH_ASSOC);
                    $visitor_time_in = $t_res['time_in'];
                } else {
                    $message = 'Error capturing the new visitor ID.';
                    $msgType = 'error';
                }
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $msgType = 'error';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<?php if ($msgType === 'success' && $visitor_id): ?>

<div class="max-w-md mx-auto">
    <div id="visitor-pass" class="glass-panel p-8 rounded-2xl relative overflow-hidden text-center border-2 border-brand-500 dark:border-cyan-500 shadow-2xl shadow-brand-500/20 dark:shadow-[0_0_30px_rgba(6,182,212,0.4)] mb-6">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-cyan-600 to-cyan-400 dark:shadow-[0_0_15px_rgba(6,182,212,1)]"></div>
        
        <div class="w-24 h-24 mx-auto bg-slate-100 dark:bg-cyan-900/30 rounded-full border-4 border-white dark:border-cyan-500/50 shadow-md flex items-center justify-center mb-6 mt-2">
            <i class="ph-fill ph-user text-5xl text-slate-400 dark:text-cyan-400 drop-shadow-[0_0_8px_rgba(6,182,212,0.6)]"></i>
        </div>
        
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/50 mb-4 shadow-sm">
            <i class="ph-fill ph-check-circle text-sm"></i> ACCESS GRANTED
        </div>
        
        <h3 class="text-3xl font-bold text-slate-900 dark:text-cyan-50 uppercase tracking-widest mb-1 drop-shadow-sm">Visitor Pass</h3>
        <p class="text-brand-600 dark:text-cyan-400 font-mono text-base mb-1 font-bold tracking-widest">ID: #<?php echo str_pad($visitor_id, 5, '0', STR_PAD_LEFT); ?></p>
        <p class="text-slate-400 dark:text-cyan-700 font-mono text-xs mb-6 tracking-widest">Today's Entry No: <span class="font-black text-slate-600 dark:text-cyan-500"><?php echo $daily_seq; ?></span></p>
        
        <div class="text-left bg-slate-50 dark:bg-[#020617] p-5 rounded-xl border border-slate-200 dark:border-cyan-500/40 space-y-4 shadow-inner mb-2">
            <div class="flex justify-between items-center border-b border-slate-200 dark:border-cyan-900/50 pb-3">
                <span class="text-sm text-slate-500 dark:text-cyan-600 uppercase tracking-wider font-bold">Name</span>
                <span class="text-slate-900 dark:text-cyan-100 font-semibold tracking-wide text-lg"><?php echo htmlspecialchars($visitor_name); ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-200 dark:border-cyan-900/50 pb-3">
                <span class="text-sm text-slate-500 dark:text-cyan-600 uppercase tracking-wider font-bold">Dept</span>
                <span class="text-slate-900 dark:text-cyan-100 font-semibold tracking-wide"><?php echo htmlspecialchars($visitor_dept); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500 dark:text-cyan-600 uppercase tracking-wider font-bold">Time In</span>
                <span class="text-slate-900 dark:text-cyan-100 font-semibold tracking-wide"><?php echo date('h:i A \o\n M d', strtotime($visitor_time_in)); ?></span>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        <button onclick="window.print()" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-black py-4 px-6 rounded-xl transition-all shadow-[0_0_15px_rgba(6,182,212,0.4)] flex items-center justify-center gap-2 text-lg uppercase tracking-widest">
            <i class="ph-bold ph-printer text-2xl"></i> Print Pass
        </button>
        <a href="checkin.php" class="w-full bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold py-3 px-6 rounded-xl transition-all flex items-center justify-center text-sm uppercase tracking-wider">
            Check-in Next Visitor
        </a>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #visitor-pass, #visitor-pass * { visibility: visible; }
        #visitor-pass { position: absolute; left: 0; top: 0; width: 100%; margin: 0; box-shadow: none !important; border: 2px solid black !important; }
        .text-white { color: black !important; }
        .text-brand-600, .text-brand-400, .text-cyan-600, .text-cyan-400 { color: black !important; }
        .bg-slate-50, .bg-\[#020617\] { background: white !important; border-color: #ddd !important; }
        .text-slate-500 { color: #555 !important; }
        .text-slate-900, .text-cyan-50, .text-cyan-100 { color: black !important; }
        * { text-shadow: none !important; box-shadow: none !important; }
    }
</style>

<?php else: ?>

<div class="max-w-2xl mx-auto glass-panel p-8 rounded-2xl relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-emerald-400 shadow-[0_0_10px_rgba(6,182,212,0.8)]"></div>

    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-slate-200 dark:border-cyan-500/30">
        <div class="p-2 bg-brand-50 dark:bg-cyan-900/40 rounded-lg text-brand-600 dark:text-cyan-400 dark:shadow-[0_0_10px_rgba(6,182,212,0.5)]">
            <i class="ph ph-identification-badge text-2xl"></i>
        </div>
        <h2 class="text-3xl font-bold text-slate-900 dark:text-cyan-50 tracking-tight drop-shadow-md">Visitor Check-In</h2>
    </div>

    <?php if ($message): ?>
        <div class="mb-8 p-4 rounded-xl border flex gap-3 items-start <?php echo $msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-300'; ?>">
            <i class="ph-fill <?php echo $msgType === 'error' ? 'ph-warning-circle text-rose-500' : 'ph-check-circle text-emerald-500'; ?> text-xl shrink-0 mt-0.5"></i>
            <div>
                <h4 class="font-semibold text-sm"><?php echo $msgType === 'error' ? 'Error' : 'Success'; ?></h4>
                <p class="text-sm opacity-90"><?php echo htmlspecialchars($message); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="checkin.php" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label for="name" class="block text-sm font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">Full Name</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500"><i class="ph ph-user"></i></div>
                    <input type="text" id="name" name="name" required class="input-glass w-full pl-10" placeholder="John Doe">
                </div>
            </div>
            <div class="space-y-2">
                <label for="phone_number" class="block text-sm font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">Phone Number</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500"><i class="ph ph-phone"></i></div>
                    <input type="text" id="phone_number" name="phone_number" required pattern="[0-9]{10}" title="Please enter exactly 10 digits" class="input-glass w-full pl-10" placeholder="10-digit number">
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <label for="host_department" class="block text-sm font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">Host Department</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500"><i class="ph ph-buildings"></i></div>
                <select id="host_department" name="host_department" required class="input-glass w-full pl-10 appearance-none">
                    <option value="" disabled selected>Select a department...</option>
                    <option value="BCA">BCA (Bachelor of Computer Applications)</option>
                    <option value="BBA">BBA (Bachelor of Business Administration)</option>
                    <option value="MCA">MCA (Master of Computer Applications)</option>
                    <option value="Administration">Administration</option>
                    <option value="Other">Other</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500"><i class="ph-bold ph-caret-down"></i></div>
            </div>
        </div>

        <div class="space-y-2">
            <label for="purpose_details" class="block text-sm font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">Purpose of Visit</label>
            <textarea id="purpose_details" name="purpose_details" rows="3" required class="input-glass w-full resize-none" placeholder="Provide specific details about the visit..."></textarea>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-slate-900 dark:bg-cyan-500/10 text-white dark:text-cyan-400 hover:bg-brand-600 dark:hover:bg-cyan-500 dark:hover:text-white font-black py-4 px-6 rounded-xl transition-all shadow-lg border border-transparent dark:border-cyan-500/50 hover:shadow-brand-500/30 dark:hover:shadow-[0_0_20px_rgba(6,182,212,0.6)] flex items-center justify-center gap-2 text-lg uppercase tracking-widest">
                <i class="ph-bold ph-paper-plane-right"></i> Check-In Visitor
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
