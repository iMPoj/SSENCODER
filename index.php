<?php
// Keep session alive on the server for 24 hours (86400 seconds)
ini_set('session.gc_maxlifetime', 86400);
// 0 means the cookie expires ONLY when the browser/tab is closed
session_set_cookie_params(0);
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    $_SESSION['role'] = 'viewer';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#E42278">
    <script>
        window.userRole = <?php echo json_encode($_SESSION['role'] ?? 'viewer'); ?>;
    </script>
    <title>Reckitt | Inventory Management</title>
    <link rel="icon" type="image/x-icon" href="reckitt-logo.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body id="app-body" class="min-h-screen">

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 z-[9999] flex flex-col items-center justify-center">
        <div class="relative flex flex-col items-center gap-6">
            <!-- Animated ping ring -->
            <div class="absolute w-32 h-32 rounded-full border-2 border-[#E42278]/20 animate-ping"></div>
            <div class="absolute w-24 h-24 rounded-full border-2 border-[#E42278]/10 animate-ping" style="animation-delay:0.3s"></div>
            
            <img src="reckitt-logo.png" class="loader-logo w-24 h-24 object-contain relative z-10" alt="Loading...">
            
            <div class="flex flex-col items-center gap-2">
                <div class="loader-text tracking-[0.3em] uppercase text-[10px]">
                    Updating Inventory
                </div>
                <p id="loadingMessage" class="text-xs text-gray-400 transition-opacity duration-300 opacity-0">Initializing...</p>
            </div>
        </div>
    </div>

    <?php include 'header.php'; ?>

    <main id="main-content" class="pt-20 pb-12 px-4 sm:px-6 lg:px-8 min-h-screen">
        <div id="page-content-wrapper" class="max-w-7xl mx-auto context-enter">
            <?php
                include 'pages/reckitt_home.php';
                include 'pages/dashboard.php';
                include 'pages/stocks_dashboard.php';
                include 'pages/order_book.php';
                include 'pages/unserved.php';
                include 'pages/fulfillable.php';
                include 'pages/admin.php';
                include 'pages/users.php';
                include 'pages/ready_orders.php';
                include 'pages/fulfilled_orders.php';
            ?>
        </div>
    </main>

    <?php include 'components/modals.php'; ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        // Safety: force-hide loader after 5s in case JS module fails to load
        const _safetyTimer = setTimeout(() => {
            const loader = document.getElementById('loadingOverlay');
            if (loader && !loader.classList.contains('hidden')) {
                const msg = document.getElementById('loadingMessage');
                if (msg) { msg.textContent = 'Taking longer than expected...'; msg.style.opacity = '1'; }
                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => { loader.classList.add('hidden'); loader.style.display = 'none'; }, 400);
                }, 600);
            }
        }, 5000);

        // Global error handler — hide loader on uncaught JS errors
        window.addEventListener('error', (e) => {
            clearTimeout(_safetyTimer);
            const loader = document.getElementById('loadingOverlay');
            if (loader) { loader.style.opacity = '0'; setTimeout(() => loader.classList.add('hidden'), 400); }
        });
    </script>

    <script type="module" src="js/main.js"></script>
    <script src="js/global.js" defer></script>
</body>
</html>
