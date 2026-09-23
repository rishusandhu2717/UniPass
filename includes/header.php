<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniPass - Campus Visitor Management</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            500: '#06b6d4', // Cyan main
                            600: '#0891b2',
                            900: '#164e63',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.2s ease-out',
                        'slide-up': 'slideUp 0.2s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0.4' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0.4', transform: 'translateY(6px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <script>
        // Theme Toggle Logic
        function initTheme() {
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
        
        initTheme();
    </script>

    <style type="text/tailwindcss">
        body {
            @apply antialiased text-slate-800 bg-slate-50 dark:text-slate-100 dark:bg-[#050b14] transition-colors duration-300;
        }
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            @apply bg-transparent;
        }
        ::-webkit-scrollbar-thumb {
            @apply bg-slate-300 dark:bg-cyan-900 rounded-full;
        }
        ::-webkit-scrollbar-thumb:hover {
            @apply bg-brand-500 dark:bg-cyan-500;
        }
        
        .glass-panel {
            @apply bg-white/80 backdrop-blur-md border border-slate-200 shadow-xl 
                   dark:bg-[#0a1220]/80 dark:border-cyan-500/30 dark:shadow-[0_0_20px_rgba(6,182,212,0.15)];
        }
        
        .input-glass {
            @apply bg-white/70 backdrop-blur-sm border border-slate-300 text-slate-900 
                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent rounded-xl px-4 py-3 transition-all
                   dark:bg-[#0f172a] dark:border-cyan-500/50 dark:text-cyan-50 dark:focus:ring-cyan-400 dark:focus:shadow-[0_0_15px_rgba(6,182,212,0.4)];
        }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-x-hidden">

    <!-- Background Ambient Glow (Dark Mode) -->
    <div class="fixed inset-0 z-[-1] pointer-events-none hidden dark:block">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-cyan-900/20 blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-cyan-600/10 blur-[120px]"></div>
    </div>

    <!-- Sticky Navbar -->
    <nav class="sticky top-0 z-50 glass-panel border-b border-slate-200 dark:border-cyan-500/50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            
            <!-- Logo -->
            <div class="flex items-center space-x-3 group">
                <div class="relative w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-cyan-400 flex items-center justify-center text-white shadow-lg group-hover:shadow-cyan-500/50 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <i class="ph-bold ph-identification-badge text-2xl"></i>
                </div>
                <a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'dashboard.php' : 'checkin.php'; ?>" class="text-2xl font-black tracking-tighter text-slate-900 dark:text-white">
                    Uni<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-cyan-500 dark:from-indigo-400 dark:to-cyan-400">Pass</span>
                </a>
            </div>
            
            <!-- Desktop Links & Actions -->
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                    
                    <div class="hidden lg:flex space-x-4 items-center">
                        <!-- User Role Badge -->
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-cyan-950/60 border border-slate-200 dark:border-cyan-500/30 text-slate-600 dark:text-cyan-300">
                            <i class="ph-fill ph-user-circle text-sm text-brand-500"></i>
                            <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? ($_SESSION['username'] ?? 'User')); ?></span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] uppercase font-mono <?php echo ($_SESSION['role'] ?? '') === 'admin' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-300' : 'bg-cyan-500/20 text-cyan-600 dark:text-cyan-300'; ?>">
                                <?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>
                            </span>
                        </div>

                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard.php" class="text-sm font-bold tracking-wide <?php echo $current_page === 'dashboard.php' ? 'text-brand-600 dark:text-cyan-400 border-brand-500 dark:border-cyan-400' : 'text-slate-600 dark:text-cyan-200 hover:text-brand-500 dark:hover:text-cyan-400 border-transparent'; ?> transition-colors flex items-center gap-1.5 border-b-2 pb-1">
                                <i class="ph-bold ph-squares-four text-lg"></i> Dashboard
                            </a>
                            <a href="search.php" class="text-sm font-bold tracking-wide <?php echo $current_page === 'search.php' ? 'text-brand-600 dark:text-cyan-400 border-brand-500 dark:border-cyan-400' : 'text-slate-600 dark:text-cyan-200 hover:text-brand-500 dark:hover:text-cyan-400 border-transparent'; ?> transition-colors flex items-center gap-1.5 border-b-2 pb-1">
                                <i class="ph-bold ph-clock-counter-clockwise text-lg"></i> History
                            </a>
                        <?php else: ?>
                            <!-- Gate Pill Selector -->
                            <div class="flex items-center bg-slate-100 dark:bg-[#0a1220] border border-slate-200 dark:border-cyan-500/30 rounded-xl p-1 gap-1">
                                <a href="checkin.php" class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-bold transition-all <?php echo $current_page === 'checkin.php' ? 'bg-white dark:bg-cyan-500/20 text-brand-600 dark:text-cyan-300 shadow-sm dark:shadow-[0_0_10px_rgba(6,182,212,0.3)] border border-slate-200 dark:border-cyan-500/50' : 'text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-cyan-400'; ?>">
                                    <i class="ph-bold ph-sign-in"></i> Check-in
                                </a>
                                <a href="checkout.php" class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-bold transition-all <?php echo $current_page === 'checkout.php' ? 'bg-white dark:bg-rose-500/20 text-rose-600 dark:text-rose-300 shadow-sm dark:shadow-[0_0_10px_rgba(244,63,94,0.3)] border border-slate-200 dark:border-rose-500/50' : 'text-slate-500 dark:text-slate-400 hover:text-rose-500 dark:hover:text-rose-400'; ?>">
                                    <i class="ph-bold ph-sign-out"></i> Check-out
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="w-px h-6 bg-slate-300 dark:bg-cyan-800 mx-1"></div>
                        
                        <a href="logout.php" class="text-sm font-bold tracking-wide text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 transition-colors flex items-center gap-1.5 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 rounded-lg border border-rose-200 dark:border-rose-500/30">
                            <i class="ph-bold ph-sign-out text-lg"></i> Logout
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Theme Toggle Button -->
                <button onclick="toggleTheme()" class="p-2 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors focus:outline-none" title="Toggle Dark/Light Mode">
                    <i class="ph ph-sun text-xl hidden dark:block hover:text-amber-400"></i>
                    <i class="ph ph-moon text-xl block dark:hidden hover:text-brand-600"></i>
                </button>

                <!-- Mobile Menu Hamburger Button -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-cyan-400 focus:outline-none" title="Open Menu">
                        <i id="mobile-menu-icon" class="ph-bold ph-list text-2xl"></i>
                    </button>
                <?php endif; ?>
            </div>
            
        </div>

        <!-- Mobile Drawer Navigation -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-200 dark:border-cyan-500/30 px-6 py-4 space-y-3 bg-white/95 dark:bg-[#070e1b]/95 backdrop-blur-lg">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-cyan-900/50 text-xs">
                    <span class="text-slate-500 dark:text-cyan-600 font-semibold">Logged in:</span>
                    <span class="font-bold text-slate-900 dark:text-cyan-100"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ($_SESSION['username'] ?? '')); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</span>
                </div>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="dashboard.php" class="block py-2 text-sm font-bold text-slate-700 dark:text-cyan-200 hover:text-brand-500 flex items-center gap-2">
                        <i class="ph-bold ph-squares-four text-lg"></i> Dashboard
                    </a>
                    <a href="search.php" class="block py-2 text-sm font-bold text-slate-700 dark:text-cyan-200 hover:text-brand-500 flex items-center gap-2">
                        <i class="ph-bold ph-clock-counter-clockwise text-lg"></i> History & Logs
                    </a>
                <?php else: ?>
                    <a href="checkin.php" class="block py-2 text-sm font-bold text-slate-700 dark:text-cyan-200 hover:text-brand-500 flex items-center gap-2">
                        <i class="ph-bold ph-sign-in text-lg"></i> Check-in Visitor
                    </a>
                    <a href="checkout.php" class="block py-2 text-sm font-bold text-slate-700 dark:text-rose-400 hover:text-rose-500 flex items-center gap-2">
                        <i class="ph-bold ph-sign-out text-lg"></i> Check-out Visitor
                    </a>
                <?php endif; ?>
                <div class="pt-2 border-t border-slate-200 dark:border-cyan-900/50">
                    <a href="logout.php" class="block py-2 text-sm font-bold text-rose-500 flex items-center gap-2">
                        <i class="ph-bold ph-sign-out text-lg"></i> Sign Out
                    </a>
                </div>
            </div>
            <script>
                function toggleMobileMenu() {
                    const menu = document.getElementById('mobile-menu');
                    const icon = document.getElementById('mobile-menu-icon');
                    if (menu.classList.contains('hidden')) {
                        menu.classList.remove('hidden');
                        icon.classList.remove('ph-list');
                        icon.classList.add('ph-x');
                    } else {
                        menu.classList.add('hidden');
                        icon.classList.remove('ph-x');
                        icon.classList.add('ph-list');
                    }
                }
            </script>
        <?php endif; ?>
    </nav>
    
    <!-- Main Content Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto p-6 mt-4 animate-fade-in">
