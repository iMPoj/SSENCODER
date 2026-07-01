<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#E42278">
    <title>Sign In — Reckitt Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .login-card {
            animation: fadeInUp 0.65s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .input-group {
            animation: fadeInUp 0.5s ease both;
            opacity: 0;
        }
        .input-group:nth-child(1) { animation-delay: 0.15s; }
        .input-group:nth-child(2) { animation-delay: 0.25s; }
        .input-group:nth-child(3) { animation-delay: 0.35s; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden bg-white">

    <!-- Animated background blobs (float keyframe defined in style.css) -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <!-- Subtle dot-grid overlay -->
    <div class="absolute inset-0 opacity-[0.025]" style="background-image: radial-gradient(#0D111A 1px, transparent 1px); background-size: 28px 28px; pointer-events:none;"></div>

    <!-- Login Card -->
    <div class="login-card w-full max-w-sm relative z-10">
        <div class="glass-card p-8 md:p-10">

            <!-- Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-[#E42278] to-[#ED7BAB] shadow-xl shadow-[#E42278]/30 mb-5 hover:scale-105 transition-transform duration-300 cursor-default">
                    <img src="reckitt-logo.png" alt="Reckitt" class="w-12 h-12 object-contain brightness-0 invert">
                </div>
                <h1 class="text-2xl font-black text-[#0D111A] mb-1 tracking-tight">Welcome Back</h1>
                <p class="text-sm text-gray-400">Sign in to your inventory dashboard</p>
            </div>

            <!-- Error Box -->
            <div id="error-message" class="hidden mb-5 p-3.5 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="error-text"></span>
            </div>

            <!-- Form -->
            <form id="login-form" class="space-y-4" novalidate>

                <div class="input-group">
                    <label for="username" class="block text-sm font-semibold text-[#0D111A] mb-1.5">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text" id="username" name="username" required
                               class="glass-input pl-11"
                               placeholder="Enter your username"
                               autocomplete="username"
                               spellcheck="false">
                    </div>
                </div>

                <div class="input-group">
                    <label for="password" class="block text-sm font-semibold text-[#0D111A] mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required
                               class="glass-input pl-11 pr-12"
                               placeholder="Enter your password"
                               autocomplete="current-password">
                        <button type="button" id="toggle-password" aria-label="Toggle password visibility"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-[#E42278] transition-colors">
                            <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="input-group pt-1">
                    <button type="submit" id="submit-btn" class="btn-primary w-full py-3 text-base justify-center">
                        <span id="btn-label">Sign In</span>
                        <svg id="loading-spinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        <svg id="arrow-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-100"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="px-3 bg-white text-gray-400 font-medium">or</span>
                </div>
            </div>

            <!-- Viewer Access -->
            <a href="index.php" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl border-[1.5px] border-gray-200 text-sm font-semibold text-gray-600 hover:border-[#E42278] hover:text-[#E42278] hover:bg-pink-50/40 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Continue as Viewer
            </a>
        </div>

        <p class="text-center mt-6 text-xs text-gray-400">
            &copy; <?php echo date('Y'); ?> Reckitt Inventory Management
        </p>
    </div>

    <script>
    // Password toggle (inline — does not rely on login.js loading)
    document.getElementById('toggle-password').addEventListener('click', function() {
        const pwd = document.getElementById('password');
        const isHidden = pwd.type === 'password';
        pwd.type = isHidden ? 'text' : 'password';
        this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        document.getElementById('eye-icon').innerHTML = isHidden
            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
    });
    </script>
    <script src="login.js" defer></script>
</body>
</html>
