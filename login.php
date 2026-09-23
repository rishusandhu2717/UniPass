<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}
require_once 'config.php';

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: checkin.php");
    }
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($submitted_token)) {
        $error = 'Security validation failed (Invalid CSRF token). Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($username) && !empty($password)) {
            try {
                $stmt = $pdo->prepare("SELECT id, username, full_name, password_hash, role FROM users WHERE username = :username");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Prevent Session Fixation
                    session_regenerate_id(true);

                    // Login successful
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['username']  = $user['username'];
                    $_SESSION['full_name'] = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
                    $_SESSION['role']      = $user['role'];
                    
                    if ($user['role'] === 'admin') {
                        header("Location: dashboard.php");
                    } else {
                        header("Location: checkin.php");
                    }
                    exit();
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                error_log('Login DB Error: ' . $e->getMessage());
                $error = 'An internal system error occurred.';
            }
        } else {
            $error = 'Please enter both username and password.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<main class="flex-grow flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-md glass-panel p-8 rounded-2xl relative overflow-hidden shadow-2xl">
        <!-- Accent line -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-indigo-500"></div>

        <div class="text-center mb-6 pb-4 border-b border-slate-200 dark:border-cyan-500/30">
            <div class="w-14 h-14 mx-auto bg-slate-100 dark:bg-cyan-950/60 rounded-2xl text-brand-600 dark:text-cyan-400 flex items-center justify-center mb-3 border border-slate-200 dark:border-cyan-500/40 shadow-sm">
                <i class="ph-fill ph-lock-key text-3xl"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-cyan-50 tracking-tight">
                Login Page
            </h2>
            <p class="text-sm text-slate-500 dark:text-cyan-300/80 mt-1 font-medium">Enter your credentials</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-5 p-3.5 rounded-xl border flex gap-2.5 items-start bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-300 text-xs">
                <i class="ph-fill ph-warning-circle text-rose-500 text-lg shrink-0 mt-0.5"></i>
                <p class="font-semibold leading-relaxed"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">

            <div class="space-y-1.5">
                <label for="username" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500">
                        <i class="ph ph-user text-base"></i>
                    </div>
                    <input type="text" id="username" name="username" required class="input-glass w-full pl-10 text-sm" placeholder="Enter username" autocomplete="username">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500">
                        <i class="ph ph-lock text-base"></i>
                    </div>
                    <input type="password" id="password" name="password" required class="input-glass w-full pl-10 text-sm" placeholder="Enter password" autocomplete="current-password">
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="w-full bg-slate-900 dark:bg-cyan-500/20 text-white dark:text-cyan-300 hover:bg-brand-600 dark:hover:bg-cyan-500 dark:hover:text-slate-900 font-bold py-3.5 px-6 rounded-xl transition-all shadow-md border border-transparent dark:border-cyan-500/50 flex items-center justify-center gap-2 text-sm uppercase tracking-wider">
                    <i class="ph-bold ph-sign-in text-base"></i> Sign In
                </button>
            </div>
        </form>
    </div>

    <!-- Demo Access Credentials Panel (Bottom of Page) -->
    <div class="w-full max-w-md mt-6 p-4 rounded-2xl glass-panel border border-slate-200 dark:border-cyan-500/30 text-xs">
        <div class="flex items-center gap-1.5 mb-2.5 pb-2 border-b border-slate-200 dark:border-cyan-900/50 font-bold text-slate-700 dark:text-cyan-200">
            <i class="ph-bold ph-key text-brand-500"></i>
            <span>Demo Access Credentials</span>
        </div>
        <div class="grid grid-cols-2 gap-2 text-[11px]">
            <!-- Admin -->
            <button type="button" onclick="fillCreds('admin', 'admin')" class="text-left p-2 rounded-lg bg-slate-100 dark:bg-slate-900/60 hover:bg-slate-200 dark:hover:bg-cyan-950/60 border border-slate-200 dark:border-cyan-500/20 transition-colors">
                <div class="font-bold text-amber-600 dark:text-amber-400">Administrator</div>
                <div class="text-slate-500 dark:text-slate-400 font-mono">admin / admin</div>
            </button>
            <!-- Guard Harsh -->
            <button type="button" onclick="fillCreds('guard.harsh', 'harsh123')" class="text-left p-2 rounded-lg bg-slate-100 dark:bg-slate-900/60 hover:bg-slate-200 dark:hover:bg-cyan-950/60 border border-slate-200 dark:border-cyan-500/20 transition-colors">
                <div class="font-bold text-brand-600 dark:text-cyan-300">Guard Harsh</div>
                <div class="text-slate-500 dark:text-slate-400 font-mono">guard.harsh / harsh123</div>
            </button>
            <!-- Guard Inder -->
            <button type="button" onclick="fillCreds('guard.inder', 'inder123')" class="text-left p-2 rounded-lg bg-slate-100 dark:bg-slate-900/60 hover:bg-slate-200 dark:hover:bg-cyan-950/60 border border-slate-200 dark:border-cyan-500/20 transition-colors">
                <div class="font-bold text-brand-600 dark:text-cyan-300">Guard Inder</div>
                <div class="text-slate-500 dark:text-slate-400 font-mono">guard.inder / inder123</div>
            </button>
            <!-- Guard Preet -->
            <button type="button" onclick="fillCreds('guard.preet', 'preet123')" class="text-left p-2 rounded-lg bg-slate-100 dark:bg-slate-900/60 hover:bg-slate-200 dark:hover:bg-cyan-950/60 border border-slate-200 dark:border-cyan-500/20 transition-colors">
                <div class="font-bold text-brand-600 dark:text-cyan-300">Guard Preet</div>
                <div class="text-slate-500 dark:text-slate-400 font-mono">guard.preet / preet123</div>
            </button>
        </div>
        <p class="text-[10px] text-slate-400 mt-2 text-center">Click any account to auto-fill credentials above</p>
    </div>
</main>

<script>
    function fillCreds(u, p) {
        document.getElementById('username').value = u;
        document.getElementById('password').value = p;
        document.getElementById('username').focus();
    }
</script>

<?php include 'includes/footer.php'; ?>
