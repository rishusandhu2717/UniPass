<?php
// view_pass.php
require_once 'includes/auth.php';
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    die("Invalid pass ID.");
}

$stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
$stmt->execute([$id]);
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visitor) {
    die("Visitor record not found.");
}

$is_active = ($visitor['status'] === 'Inside');
$guard_in = $visitor['checked_in_by'] ?: ($visitor['entered_by'] ?: 'Guard');
$guard_out = $visitor['checked_out_by'] ?: 'Staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Pass #<?php echo str_pad($visitor['id'], 5, '0', STR_PAD_LEFT); ?> - UniPass</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            body * { visibility: hidden; }
            #visitor-pass, #visitor-pass * { visibility: visible; }
            #visitor-pass { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 100%; 
                border: 2px solid black !important; 
                box-shadow: none !important;
                background: white !important;
                color: black !important;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full">
        <!-- Digital Visitor Pass (NO QR CODE) -->
        <div id="visitor-pass" class="bg-white border-2 border-cyan-500 rounded-3xl shadow-2xl p-8 text-center relative overflow-hidden mb-6">
            <div class="absolute top-0 left-0 w-full h-3 bg-gradient-to-r from-indigo-600 via-cyan-500 to-emerald-400"></div>
            
            <!-- Campus Header -->
            <div class="flex items-center justify-center gap-2 mb-5 mt-1">
                <i class="ph-bold ph-identification-badge text-2xl text-cyan-600"></i>
                <h1 class="text-xl font-black tracking-tight text-slate-900">Uni<span class="text-cyan-600">Pass</span></h1>
                <span class="text-xs font-semibold px-2 py-0.5 bg-slate-100 text-slate-600 rounded">GATE PASS</span>
            </div>

            <!-- Campus Security Emblem -->
            <div class="w-24 h-24 mx-auto bg-slate-100 rounded-full border-4 border-white shadow-md flex items-center justify-center mb-5">
                <i class="ph-fill ph-identification-badge text-5xl text-cyan-600"></i>
            </div>
            
            <div class="mb-3">
                <?php if ($is_active): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-300 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> ACTIVE &bull; INSIDE CAMPUS
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-300">
                        <i class="ph-bold ph-check text-sm"></i> CHECKED OUT
                    </span>
                <?php endif; ?>
            </div>

            <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-0.5"><?php echo htmlspecialchars($visitor['name']); ?></h2>
            <p class="text-cyan-700 font-mono text-sm font-bold tracking-widest mb-1">PASS #<?php echo str_pad($visitor['id'], 5, '0', STR_PAD_LEFT); ?></p>
            <?php if (!empty($visitor['daily_seq'])): ?>
                <p class="text-slate-400 text-xs font-mono mb-6">Today's Token: <strong>#<?php echo $visitor['daily_seq']; ?></strong></p>
            <?php else: ?>
                <div class="mb-6"></div>
            <?php endif; ?>
            
            <div class="text-left bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3 shadow-inner">
                <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                    <span class="text-xs text-slate-500 uppercase tracking-wider font-bold">Contact</span>
                    <span class="text-slate-900 font-mono font-semibold text-sm"><?php echo htmlspecialchars($visitor['phone_number']); ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                    <span class="text-xs text-slate-500 uppercase tracking-wider font-bold">Department</span>
                    <span class="text-slate-900 font-bold text-sm"><?php echo htmlspecialchars($visitor['host_department']); ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                    <span class="text-xs text-slate-500 uppercase tracking-wider font-bold">Time In</span>
                    <span class="text-slate-900 font-semibold text-xs"><?php echo date('d M y, h:i A', strtotime($visitor['time_in'])); ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                    <span class="text-xs text-slate-500 uppercase tracking-wider font-bold">Checked In By</span>
                    <span class="text-cyan-700 font-bold text-xs uppercase flex items-center gap-1">
                        <i class="ph-fill ph-shield-check"></i>
                        <?php echo htmlspecialchars($guard_in); ?>
                    </span>
                </div>
                <?php if ($visitor['time_out']): ?>
                    <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-bold">Time Out</span>
                        <span class="text-rose-600 font-semibold text-xs"><?php echo date('d M y, h:i A', strtotime($visitor['time_out'])); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-bold">Checked Out By</span>
                        <span class="text-rose-700 font-bold text-xs uppercase flex items-center gap-1">
                            <i class="ph-fill ph-shield-check"></i>
                            <?php echo htmlspecialchars($guard_out); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-200 text-center">
                <p class="text-[11px] text-slate-400">Authorized Campus Visitor Pass &bull; UniPass System</p>
            </div>
        </div>

        <div class="flex gap-4 no-print">
            <button onclick="window.history.back()" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold py-3.5 px-4 rounded-xl transition-all flex items-center justify-center gap-1.5 text-sm uppercase tracking-wider">
                <i class="ph-bold ph-arrow-left"></i> Back
            </button>
            <button onclick="window.print()" class="w-2/3 bg-cyan-600 hover:bg-cyan-500 text-white font-black py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-cyan-500/30 flex items-center justify-center gap-2 text-sm uppercase tracking-wider">
                <i class="ph-bold ph-printer text-xl"></i> Print Pass
            </button>
        </div>
    </div>

</body>
</html>
