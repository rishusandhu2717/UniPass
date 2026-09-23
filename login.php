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
                $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Prevent Session Fixation
                    session_regenerate_id(true);

                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
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

<main class="flex-grow flex items-center justify-center p-6 mt-[-40px]">
    <div class="w-full max-w-md glass-panel p-8 rounded-2xl relative overflow-hidden">
        <!-- Decorative accent line -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-emerald-400 shadow-[0_0_10px_rgba(6,182,212,0.8)]"></div>

        <div class="text-center mb-8 pb-4 border-b border-slate-200 dark:border-cyan-500/30">
            <div class="w-16 h-16 mx-auto bg-brand-50 dark:bg-cyan-900/40 rounded-2xl text-brand-600 dark:text-cyan-400 dark:shadow-[0_0_15px_rgba(6,182,212,0.5)] flex items-center justify-center mb-4 border dark:border-cyan-500/50">
                <i class="ph-fill ph-lock-key text-4xl drop-shadow-md"></i>
            </div>
            <h2 class="text-3xl font-bold text-slate-900 dark:text-cyan-50 tracking-tight drop-shadow-md">
                System Access
            </h2>
            <p class="text-slate-500 dark:text-cyan-300 mt-2 font-medium tracking-wide">Enter your credentials to proceed.</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 p-4 rounded-xl border flex gap-3 items-start bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-300">
                <i class="ph-fill ph-warning-circle text-rose-500 text-xl shrink-0 mt-0.5"></i>
                <p class="text-sm font-semibold opacity-90"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">

            <div class="space-y-2">
                <label for="username" class="block text-sm font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500">
                        <i class="ph ph-user"></i>
                    </div>
                    <input type="text" id="username" name="username" required class="input-glass w-full pl-10" placeholder="admin or gate" autocomplete="username">
                </div>
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-sm font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500">
                        <i class="ph ph-lock"></i>
                    </div>
                    <input type="password" id="password" name="password" required class="input-glass w-full pl-10" placeholder="••••••••" autocomplete="current-password">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-slate-900 dark:bg-cyan-500/10 text-white dark:text-cyan-400 hover:bg-brand-600 dark:hover:bg-cyan-500 dark:hover:text-white font-black py-4 px-6 rounded-xl transition-all shadow-lg border border-transparent dark:border-cyan-500/50 hover:shadow-brand-500/30 dark:hover:shadow-[0_0_20px_rgba(6,182,212,0.6)] flex items-center justify-center gap-2 text-lg uppercase tracking-widest">
                    <i class="ph-bold ph-sign-in"></i> Authenticate
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
