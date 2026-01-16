<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 'viewer';
$username = $_SESSION['username'] ?? 'User';
?>
<header id="app-header" class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <div class="flex items-center gap-4">
                <a href="index.php" class="flex-shrink-0 flex items-center gap-3 group">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gradient-to-r from-pink-600 to-purple-600 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                        <img src="reckitt-logo.png" alt="Reckitt Logo" class="relative h-9 w-auto object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-lg text-slate-800 tracking-tight leading-tight group-hover:text-indigo-600 transition-colors">Inventory</span>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest leading-none">System</span>
                    </div>
                </a>
                
                <nav class="hidden lg:ml-8 lg:flex lg:space-x-1">
                    <a href="index.php#dashboard" data-tab="dashboard" class="nav-link px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">Dashboard</a>
                    <a href="index.php#stocksDashboard" data-tab="stocksDashboard" class="nav-link px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">Stocks</a>
                    <a href="product_search.php" class="nav-link px-3 py-2 rounded-md text-sm font-medium <?php echo ($currentPage == 'product_search.php') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'; ?> transition-all">Search</a>
                    <a href="index.php#orderBook" data-tab="orderBook" class="nav-link px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">Orders</a>
                    
                    <a href="index.php#unserved" data-tab="unserved" class="nav-link px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">Unserved</a>
                    <a href="index.php#fulfillable" data-tab="fulfillable" class="nav-link px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">Fulfillable</a>
                    <a href="index.php#readyOrders" data-tab="readyOrders" class="nav-link px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all">Ready</a>
                </nav>
            </div>

            <div class="flex items-center gap-3">
                
                <div class="hidden lg:flex items-center gap-2">
                    <?php if (in_array($user_role, ['admin', 'encoder'])): ?>
                        <a href="create_order.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            New Order
                        </a>
                    <?php endif; ?>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="index.php#admin" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors" title="Admin Settings">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <div class="relative group/user ml-2">
                        <button class="flex items-center gap-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs ring-2 ring-white shadow-sm">
                                <?php echo strtoupper(substr($username, 0, 2)); ?>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-xs font-semibold text-slate-700 leading-none"><?php echo htmlspecialchars($username); ?></p>
                                <p class="text-[10px] font-medium text-slate-400 uppercase leading-none mt-1"><?php echo htmlspecialchars($user_role); ?></p>
                            </div>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 py-1 opacity-0 invisible group-hover/user:opacity-100 group-hover/user:visible transition-all duration-200 transform origin-top-right z-50">
                            <div class="px-4 py-3 border-b border-slate-100 md:hidden">
                                <p class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($username); ?></p>
                                <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($user_role); ?></p>
                            </div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                Sign out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">Log in</a>
                <?php endif; ?>

                <div class="lg:hidden flex items-center ml-2">
                    <button id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-200 bg-white">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="index.php#dashboard" data-tab="dashboard" class="nav-link-mobile block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:text-indigo-600 hover:bg-indigo-50">Dashboard</a>
            <a href="index.php#stocksDashboard" data-tab="stocksDashboard" class="nav-link-mobile block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:text-indigo-600 hover:bg-indigo-50">Stocks</a>
            <a href="product_search.php" class="nav-link-mobile block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:text-indigo-600 hover:bg-indigo-50 <?php echo ($currentPage == 'product_search.php') ? 'bg-indigo-50 text-indigo-700' : ''; ?>">Product Search</a>
            <a href="index.php#orderBook" data-tab="orderBook" class="nav-link-mobile block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:text-indigo-600 hover:bg-indigo-50">Orders</a>
            <a href="index.php#unserved" data-tab="unserved" class="nav-link-mobile block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:text-indigo-600 hover:bg-indigo-50">Unserved</a>
            <a href="index.php#fulfillable" data-tab="fulfillable" class="nav-link-mobile block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:text-indigo-600 hover:bg-indigo-50">Fulfillable</a>
            <a href="index.php#readyOrders" data-tab="readyOrders" class="nav-link-mobile block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:text-indigo-600 hover:bg-indigo-50">Ready Orders</a>
        </div>
        
        <?php if (in_array($user_role, ['admin', 'encoder'])): ?>
        <div class="pt-4 pb-3 border-t border-slate-200">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-slate-800"><?php echo htmlspecialchars($username); ?></div>
                    <div class="text-sm font-medium text-slate-500"><?php echo htmlspecialchars($user_role); ?></div>
                </div>
            </div>
            <div class="mt-3 px-2 space-y-1">
                <a href="create_order.php" class="block px-3 py-2 rounded-md text-base font-medium text-slate-600 hover:text-indigo-600 hover:bg-slate-50">Create New Order</a>
                <?php if ($user_role === 'admin'): ?>
                    <a href="index.php#admin" class="block px-3 py-2 rounded-md text-base font-medium text-slate-600 hover:text-indigo-600 hover:bg-slate-50">Admin Settings</a>
                <?php endif; ?>
                <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">Sign out</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</header>