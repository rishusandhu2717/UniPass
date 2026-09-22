<?php
require_once 'includes/auth.php';
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
$stmt->execute([$id]);
$visitor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$visitor || $visitor['status'] !== 'Inside') {
    die("Invalid pass or visitor is not active.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visitor Pass #<?php echo $id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        @media print {
            body * { visibility: hidden; }
            #visitor-pass, #visitor-pass * { visibility: visible; }
            #visitor-pass { position: absolute; left: 0; top: 0; width: 100%; border: 2px solid black !important; box-shadow: none !important; }
            button { display: none; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full">
        <!-- Digital Visitor Pass -->
        <div id="visitor-pass" class="bg-white border-2 border-brand-500 rounded-2xl shadow-2xl p-8 text-center relative overflow-hidden mb-6">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-cyan-600 to-cyan-400"></div>
            
            <div class="w-24 h-24 mx-auto bg-slate-100 rounded-full border-4 border-white shadow-md flex items-center justify-center mb-6 mt-2">
                <i class="ph-fill ph-user text-5xl text-slate-400"></i>
            </div>
            
            <h3 class="text-3xl font-bold text-slate-900 uppercase tracking-widest mb-1">Visitor</h3>
            <p class="text-cyan-600 font-mono text-base mb-8 font-bold tracking-widest">ID: #<?php echo str_pad($visitor['id'], 5, '0', STR_PAD_LEFT); ?></p>
            
            <div class="text-left bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4 shadow-inner">
                <div class="flex justify-between items-center border-b border-slate-200 pb-3">
                    <span class="text-sm text-slate-500 uppercase tracking-wider font-bold">Name</span>
                    <span class="text-slate-900 font-semibold tracking-wide text-lg"><?php echo htmlspecialchars($visitor['name']); ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200 pb-3">
                    <span class="text-sm text-slate-500 uppercase tracking-wider font-bold">Dept</span>
                    <span class="text-slate-900 font-semibold tracking-wide"><?php echo htmlspecialchars($visitor['host_department']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500 uppercase tracking-wider font-bold">Time In</span>
                    <span class="text-slate-900 font-semibold tracking-wide"><?php echo date('h:i A \o\n M d', strtotime($visitor['time_in'])); ?></span>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <a href="dashboard.php" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2">
                Back
            </a>
            <button onclick="window.print()" class="w-2/3 bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-lg shadow-cyan-500/30 flex items-center justify-center gap-2">
                <i class="ph-bold ph-printer text-xl"></i> Print Pass
            </button>
        </div>
    </div>

</body>
</html>
