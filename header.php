<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 86400);
    session_set_cookie_params(0);
    session_start();
}
$user_role    = $_SESSION['role'] ?? 'viewer';
$username     = $_SESSION['username'] ?? 'User';
$avatar_url   = $_SESSION['avatar_url'] ?? null;
$display_name = $_SESSION['display_name'] ?? $username;
?>
<header id="app-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">        <div class="flex justify-between h-16 items-center gap-4">

            <!-- Logo + Desktop Nav -->
            <div class="flex items-center gap-2 min-w-0">
                <a href="#reckittHome" data-tab="reckittHome" class="flex-shrink-0 flex items-center gap-2.5 group" title="Home">
                    <div class="relative">
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-[#E42278] to-[#ED7BAB] rounded-full blur-md opacity-0 group-hover:opacity-30 transition-opacity duration-500"></div>
                        <img src="reckitt-logo.png" alt="Reckitt Logo" class="relative h-8 w-auto object-contain">
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="font-black text-base text-[#0D111A] tracking-tight group-hover:text-[#E42278] transition-colors duration-200">Inventory</span>
                        <span class="text-[9px] uppercase font-bold text-[#9CA3AF] tracking-widest">System</span>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex lg:ml-4 items-center gap-0.5" aria-label="Main navigation">
                    <a href="index.php#dashboard"       data-tab="dashboard"       class="nav-link">Dashboard</a>
                    <a href="index.php#stocksDashboard" data-tab="stocksDashboard" class="nav-link">Stocks</a>
                    <a href="product_search.php" class="nav-link <?php echo ($currentPage === 'product_search.php') ? 'active' : ''; ?>">Search</a>
                    <a href="index.php#orderBook"       data-tab="orderBook"       class="nav-link">Orders</a>
                    <a href="index.php#unserved"        data-tab="unserved"        class="nav-link">Unserved</a>
                    <a href="index.php#fulfillable"     data-tab="fulfillable"     class="nav-link">Fulfillable</a>
                    <a href="index.php#readyOrders"     data-tab="readyOrders"     class="nav-link">Ready</a>
                    <a href="index.php#fulfilled_orders" data-tab="fulfilled_orders" class="nav-link">Fulfilled</a>
                </nav>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2 flex-shrink-0">

                <!-- New Order Button (encoder/admin only) -->
                <?php if (in_array($user_role, ['admin', 'encoder'])): ?>
                    <a href="create_order.php" class="hidden lg:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-sm font-semibold bg-gradient-to-r from-[#E42278] to-[#ED7BAB] shadow-[0_4px_14px_rgba(228,34,120,0.3)] hover:shadow-[0_6px_20px_rgba(228,34,120,0.4)] hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#E42278]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        New Order
                    </a>
                <?php endif; ?>

                <!-- Admin link -->
                <?php if ($user_role === 'admin'): ?>
                    <a href="index.php#users" data-tab="users" class="hidden lg:flex p-2 text-[#6B7280] hover:text-[#E42278] hover:bg-[rgba(228,34,120,0.08)] rounded-xl transition-colors" title="Users">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </a>
                    <a href="index.php#admin" data-tab="admin" class="hidden lg:flex p-2 text-[#6B7280] hover:text-[#E42278] hover:bg-[rgba(228,34,120,0.08)] rounded-xl transition-colors" title="Admin">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </a>
                <?php endif; ?>

                <!-- User Dropdown (logged in) -->
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <div class="relative group/user">
                        <button class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-[rgba(228,34,120,0.06)] transition-colors focus:outline-none focus:ring-2 focus:ring-[#E42278]" aria-label="User menu">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-bold text-sm shadow-[0_3px_10px_rgba(228,34,120,0.3)] ring-2 ring-white overflow-hidden">
                                <?php if ($avatar_url): ?>
                                    <img src="<?php echo htmlspecialchars($avatar_url); ?>?v=<?php echo time(); ?>" alt="Profile" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?php 
                                        // Smart initials: Extracts "JL" from "John Lloyd"
                                        $initials = preg_match_all('/\b\w/', $display_name, $m) ? implode('', $m[0]) : substr($display_name, 0, 2);
                                        echo strtoupper(substr($initials, 0, 2)); 
                                    ?>
                                <?php endif; ?>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-semibold text-[#0D111A] leading-none"><?php echo htmlspecialchars($display_name); ?></p>
                                <p class="text-[10px] font-semibold text-[#9CA3AF] uppercase tracking-wider mt-0.5"><?php echo htmlspecialchars($user_role); ?></p>
                            </div>
                            <svg class="w-3.5 h-3.5 text-[#9CA3AF] hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-[0_8px_32px_rgba(0,0,0,0.1)] ring-1 ring-black/5 py-1.5 opacity-0 invisible group-hover/user:opacity-100 group-hover/user:visible transition-all duration-200 origin-top-right z-50">
                            <div class="px-3 py-2 mb-1 border-b border-slate-100">
                                <p class="text-xs font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($display_name); ?></p>
                                <p class="text-[10px] font-bold text-[#E42278] uppercase tracking-wider"><?php echo htmlspecialchars($user_role); ?></p>
                            </div>
                            <a href="logout.php" class="flex items-center gap-2 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors mx-1 rounded-lg">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="text-sm font-semibold text-[#6B7280] hover:text-[#E42278] px-3 py-2 rounded-xl hover:bg-[rgba(228,34,120,0.06)] transition-colors">Log In</a>
                <?php endif; ?>

                <!-- Mobile hamburger -->
                <button id="mobile-menu-button" class="lg:hidden p-2 rounded-xl text-[#6B7280] hover:text-[#E42278] hover:bg-[rgba(228,34,120,0.08)] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#E42278] transition-colors" aria-label="Toggle menu" aria-expanded="false">
                    <svg id="hamburger-open" class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg id="hamburger-close" class="h-5 w-5 hidden" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-100/80 bg-white/98 backdrop-blur-xl shadow-lg">
        <div class="px-3 py-3 space-y-0.5">
            <a href="index.php#dashboard"       data-tab="dashboard"       class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">Dashboard</a>
            <a href="index.php#stocksDashboard" data-tab="stocksDashboard" class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">Stocks</a>
            <a href="product_search.php"        class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60 <?php echo ($currentPage === 'product_search.php') ? 'text-[#E42278] bg-pink-50/80' : ''; ?>">Product Search</a>
            <a href="index.php#orderBook"       data-tab="orderBook"       class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">Orders</a>
            <a href="index.php#unserved"        data-tab="unserved"        class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">Unserved</a>
            <a href="index.php#fulfillable"     data-tab="fulfillable"     class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">Fulfillable</a>
            <a href="index.php#readyOrders"     data-tab="readyOrders"     class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">Ready Orders</a>
            <a href="index.php#fulfilled_orders" data-tab="fulfilled_orders" class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">Fulfilled</a>
        </div>

        <?php if (in_array($user_role, ['admin', 'encoder'])): ?>
        <div class="px-3 py-3 border-t border-slate-100 space-y-0.5">
            <a href="create_order.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-[#E42278] hover:bg-pink-50/60">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Order
            </a>
            <?php if ($user_role === 'admin'): ?>
                <a href="index.php#admin" data-tab="admin" class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">Admin Settings</a>
            <a href="index.php#users" data-tab="users" class="nav-link-mobile flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:text-[#E42278] hover:bg-pink-50/60">User Management</a>
            <?php endif; ?>
            <a href="logout.php" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </a>
        </div>
        <?php endif; ?>
    </div>
</header>

<script>
// Mobile menu toggle with icon swap
(function() {
    const btn = document.getElementById('mobile-menu-button');
    const menu = document.getElementById('mobile-menu');
    const iconOpen = document.getElementById('hamburger-open');
    const iconClose = document.getElementById('hamburger-close');
    if (!btn || !menu) return;

    btn.addEventListener('click', () => {
        const isOpen = !menu.classList.contains('hidden');
        menu.classList.toggle('hidden', isOpen);
        iconOpen.classList.toggle('hidden', !isOpen);
        iconClose.classList.toggle('hidden', isOpen);
        btn.setAttribute('aria-expanded', String(!isOpen));
    });

    // Close mobile menu when a nav link inside it is clicked
    menu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        });
    });

    // Close mobile menu on outside click
    document.addEventListener('click', (e) => {
        if (!btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>
