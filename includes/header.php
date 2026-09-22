<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniPass - Campus Visitor Management</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', // Enable class-based dark mode
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
                        'fade-in': 'fadeIn 0.15s ease-out',
                        'slide-up': 'slideUp 0.15s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0.6' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0.6', transform: 'translateY(4px)' },
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
        
        // Initialize theme on load
        initTheme();
    </script>

    <style type="text/tailwindcss">
        body {
            @apply antialiased text-slate-800 bg-slate-50 dark:text-slate-100 dark:bg-[#050b14] transition-colors duration-300;
        }
        /* Custom subtle scrollbar */
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
        
        /* Glass/Command Panel utility */
        .glass-panel {
            @apply bg-white/70 backdrop-blur-md border border-slate-200 shadow-xl 
                   dark:bg-[#0a1220]/80 dark:border-cyan-500/30 dark:shadow-[0_0_20px_rgba(6,182,212,0.15)];
        }
        
        .input-glass {
            @apply bg-white/50 backdrop-blur-sm border border-slate-300 text-slate-900 
                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent rounded-xl px-4 py-3 transition-all
                   dark:bg-[#0f172a] dark:border-cyan-500/50 dark:text-cyan-50 dark:focus:ring-cyan-400 dark:focus:shadow-[0_0_15px_rgba(6,182,212,0.4)];
        }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-x-hidden">

    <!-- Subtle Background Glows (Dark Mode Only) -->
    <div class="fixed inset-0 z-[-1] pointer-events-none hidden dark:block">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-cyan-900/20 blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-cyan-600/10 blur-[120px]"></div>
    </div>

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 glass-panel border-b-0 border-b border-slate-200 dark:border-cyan-500/50">
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
            
            <!-- Links & Actions -->
            <div class="flex items-center space-x-6">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="hidden md:flex space-x-6 items-center">
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard.php" class="text-sm font-bold tracking-wide text-slate-600 dark:text-cyan-200 hover:text-brand-500 dark:hover:text-cyan-400 transition-colors flex items-center gap-1.5 border-b-2 border-transparent hover:border-brand-500 dark:hover:border-cyan-400 pb-1">
                                <i class="ph-bold ph-squares-four text-lg"></i> Dashboard
                            </a>
                            <a href="search.php" class="text-sm font-bold tracking-wide text-slate-600 dark:text-cyan-200 hover:text-brand-500 dark:hover:text-cyan-400 transition-colors flex items-center gap-1.5 border-b-2 border-transparent hover:border-brand-500 dark:hover:border-cyan-400 pb-1">
                                <i class="ph-bold ph-clock-counter-clockwise text-lg"></i> History
                            </a>
                        <?php else: ?>
                            <?php 
                                $current_page = basename($_SERVER['PHP_SELF']);
                            ?>
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
                        
                        <div class="w-px h-6 bg-slate-300 dark:bg-cyan-800 mx-2"></div>
                        
                        <a href="logout.php" class="text-sm font-bold tracking-wide text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 transition-colors flex items-center gap-1.5 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 rounded-lg border border-rose-200 dark:border-rose-500/30">
                            <i class="ph-bold ph-sign-out text-lg"></i> Logout
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="w-px h-6 bg-slate-300 dark:bg-cyan-800 hidden md:block"></div>
                
                <!-- Theme Toggle Button -->
                <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors focus:outline-none" title="Toggle Dark/Light Mode">
                    <!-- Sun icon shown in dark mode -->
                    <i class="ph ph-sun text-xl hidden dark:block hover:text-amber-400"></i>
                    <!-- Moon icon shown in light mode -->
                    <i class="ph ph-moon text-xl block dark:hidden hover:text-brand-600"></i>
                </button>
            </div>
            
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto p-6 mt-8 animate-fade-in">
