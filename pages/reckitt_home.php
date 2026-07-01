<div id="reckittHomePage" class="page-section hidden">
    <div class="w-full min-h-[calc(100vh-6rem)] flex flex-col items-center justify-center py-12 px-4">
        
        <!-- Hero Section -->
        <div class="text-center max-w-2xl mx-auto mb-16 animate-fadeInUp">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-[#E42278] to-[#ED7BAB] shadow-2xl shadow-[#E42278]/30 mb-8 transform hover:scale-105 transition-transform duration-300">
                <img src="reckitt-logo.png" alt="Reckitt" class="w-14 h-14 object-contain filter brightness-0 invert">
            </div>
            <h1 class="text-5xl font-black text-[#0D111A] tracking-tight mb-4 leading-none">
                Reckitt Inventory
                <span class="block text-[#E42278]">Management System</span>
            </h1>
            <p class="text-lg text-[#6B7280] font-medium leading-relaxed">
                A centralized platform for tracking stock, managing purchase orders, and monitoring sales performance across all business units.
            </p>
        </div>
        
        <!-- Quick Action Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl w-full mb-12">
            
            <a href="#dashboard" data-tab="dashboard" class="group glass-card p-6 flex flex-col gap-4 cursor-pointer hover:-translate-y-1 transition-all duration-300 hover:shadow-xl hover:shadow-pink-100">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#E42278]/10 to-[#ED7BAB]/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#E42278]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#0D111A] text-lg group-hover:text-[#E42278] transition-colors">Executive Dashboard</h3>
                    <p class="text-sm text-[#6B7280] mt-1">Sales performance, targets, and key metrics at a glance.</p>
                </div>
                <div class="mt-auto flex items-center text-[#E42278] text-sm font-semibold gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    Open Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>
            
            <a href="#stocksDashboard" data-tab="stocksDashboard" class="group glass-card p-6 flex flex-col gap-4 cursor-pointer hover:-translate-y-1 transition-all duration-300 hover:shadow-xl hover:shadow-pink-100">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500/10 to-blue-600/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#0D111A] text-lg group-hover:text-blue-600 transition-colors">Stock Levels</h3>
                    <p class="text-sm text-[#6B7280] mt-1">Real-time inventory levels for all SKUs across locations.</p>
                </div>
                <div class="mt-auto flex items-center text-blue-600 text-sm font-semibold gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    View Stocks
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>
            
            <a href="#orderBook" data-tab="orderBook" class="group glass-card p-6 flex flex-col gap-4 cursor-pointer hover:-translate-y-1 transition-all duration-300 hover:shadow-xl hover:shadow-pink-100">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500/10 to-violet-600/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#0D111A] text-lg group-hover:text-violet-600 transition-colors">Order Book</h3>
                    <p class="text-sm text-[#6B7280] mt-1">Master record of all processed purchase orders.</p>
                </div>
                <div class="mt-auto flex items-center text-violet-600 text-sm font-semibold gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    View Orders
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>
            
            <a href="#unserved" data-tab="unserved" class="group glass-card p-6 flex flex-col gap-4 cursor-pointer hover:-translate-y-1 transition-all duration-300 hover:shadow-xl hover:shadow-red-100">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-500/10 to-red-600/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#0D111A] text-lg group-hover:text-red-600 transition-colors">Unserved Items</h3>
                    <p class="text-sm text-[#6B7280] mt-1">Orders with out-of-stock items requiring attention.</p>
                </div>
                <div class="mt-auto flex items-center text-red-600 text-sm font-semibold gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    Review Unserved
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>
            
            <a href="#fulfillable" data-tab="fulfillable" class="group glass-card p-6 flex flex-col gap-4 cursor-pointer hover:-translate-y-1 transition-all duration-300 hover:shadow-xl hover:shadow-green-100">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-emerald-600/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#0D111A] text-lg group-hover:text-emerald-600 transition-colors">Fulfillable Opportunities</h3>
                    <p class="text-sm text-[#6B7280] mt-1">Unserved items now ready to be dispatched.</p>
                </div>
                <div class="mt-auto flex items-center text-emerald-600 text-sm font-semibold gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    View Fulfillable
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>

            <a href="#readyOrders" data-tab="readyOrders" class="group glass-card p-6 flex flex-col gap-4 cursor-pointer hover:-translate-y-1 transition-all duration-300 hover:shadow-xl hover:shadow-amber-100">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500/10 to-amber-600/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                </div>
                <div>
                    <h3 class="font-bold text-[#0D111A] text-lg group-hover:text-amber-600 transition-colors">Ready Orders</h3>
                    <p class="text-sm text-[#6B7280] mt-1">Draft orders staged and ready for posting.</p>
                </div>
                <div class="mt-auto flex items-center text-amber-600 text-sm font-semibold gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    View Ready
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>
        </div>

        <!-- Footer Bar -->
        <div class="text-center text-xs text-[#6B7280] space-y-1">
            <p class="font-semibold uppercase tracking-widest text-[10px]">Reckitt Philippines &bull; Inventory Management System</p>
            <p><?php echo date('Y'); ?> &bull; All data is confidential and for internal use only.</p>
        </div>
    </div>
</div>
