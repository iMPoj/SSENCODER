<div id="stocksDashboardPage" class="hidden">
    <div class="max-w-7xl mx-auto space-y-5 pb-12">

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-teal-600 to-teal-500 shadow-xl shadow-teal-500/25 p-6 md:p-8">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-8 -left-8 w-36 h-36 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 shadow-inner flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-teal-200 text-xs font-bold uppercase tracking-widest mb-0.5">Real-Time Visibility</p>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight leading-none">Inventory & Stocks</h1>
                        <p class="text-teal-200 text-sm mt-1">Product stock levels and pricing across all locations.</p>
                    </div>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 flex flex-col gap-0.5 min-w-[100px]">
                        <span class="text-teal-200 text-[10px] font-black uppercase tracking-widest">In Stock</span>
                        <p id="inStockCount" class="text-2xl font-black text-white font-mono">0</p>
                    </div>
                    <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 flex flex-col gap-0.5 min-w-[100px]">
                        <span class="text-amber-300 text-[10px] font-black uppercase tracking-widest">Low Stock</span>
                        <p id="lowStockCount" class="text-2xl font-black text-amber-200 font-mono">0</p>
                    </div>
                    <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 flex flex-col gap-0.5 min-w-[100px]">
                        <span class="text-red-300 text-[10px] font-black uppercase tracking-widest">Out of Stock</span>
                        <p id="outOfStockCount" class="text-2xl font-black text-red-200 font-mono">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Filter Inventory</span>
                <span id="resultCount" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold text-white bg-emerald-500 opacity-0 transition-opacity duration-300 ml-auto">0 found</span>
            </div>
            <div class="relative mb-4 group">
                <svg id="searchIcon" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-emerald-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="stockSearchInput" placeholder="Search by SKU, Barcode, or Description..."
                       class="w-full pl-10 pr-10 py-3 text-sm border border-slate-200 rounded-xl bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/20 focus:border-emerald-400 transition-all">
                <button id="clearSearchBtn" class="absolute inset-y-0 right-0 pr-3.5 flex items-center opacity-0 pointer-events-none transition-opacity duration-200 text-slate-400 hover:text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="locFilterStocks" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Location</label>
                    <select id="locFilterStocks" class="glass-input">
                        <option value="Davao">Davao</option>
                        <option value="Gensan">Gensan</option>
                    </select>
                </div>
                <div>
                    <label for="buFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Business Unit</label>
                    <select id="buFilter" class="glass-input">
                        <option value="all">All BUs</option>
                        <option value="Health">Health</option>
                        <option value="Hygiene">Hygiene</option>
                        <option value="Nutri">Nutri</option>
                    </select>
                </div>
                <div>
                    <label for="stockStatusFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Stock Status</label>
                    <select id="stockStatusFilter" class="glass-input">
                        <option value="all">All Stocks</option>
                        <option value="in_stock">In Stock (>10)</option>
                        <option value="low_stock">Low Stock (1-10)</option>
                        <option value="no_stock">Out of Stock (0)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden flex flex-col" id="tableContainer">
            <div class="px-6 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Inventory List</span>
                <span id="loadingIndicator" class="hidden text-xs font-bold text-slate-400 flex items-center gap-2">
                    <span class="w-3.5 h-3.5 border-2 border-emerald-200 border-t-emerald-500 rounded-full animate-spin"></span>
                    Updating...
                </span>
            </div>

            <div class="overflow-x-auto relative min-h-[400px]">
                <div id="tableSkeleton" class="hidden absolute inset-0 bg-white z-10 p-6 space-y-4">
                    <?php for($i = 0; $i < 6; $i++): ?>
                    <div class="flex gap-4 animate-pulse">
                        <div class="w-1/4 h-12 bg-slate-100 rounded-xl"></div>
                        <div class="w-1/3 h-12 bg-slate-100 rounded-xl"></div>
                        <div class="flex-1 h-12 bg-slate-100 rounded-xl"></div>
                        <div class="w-24 h-12 bg-slate-100 rounded-xl"></div>
                    </div>
                    <?php endfor; ?>
                </div>

                <table class="w-full text-sm text-left data-table">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 w-1/4">Barcode / SKU</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 w-1/3">Description</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Stock on Hand</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Price / Piece</th>
                        </tr>
                    </thead>
                    <tbody id="stocksDashboardList" class="divide-y divide-slate-50 transition-all duration-300">
                        </tbody>
                </table>
            </div>
            
            <div id="stocksEmptyState" class="hidden text-center py-20 transition-all duration-500 opacity-0 transform translate-y-4">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-8 h-8 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-base font-black text-slate-700 mb-1">No products found</h3>
                <p class="text-sm text-slate-400 max-w-xs mx-auto">Try adjusting your search terms or filters.</p>
                <button onclick="document.getElementById('stockSearchInput').value='';document.getElementById('stockSearchInput').dispatchEvent(new Event('input'))" class="mt-4 text-sm font-bold text-emerald-600 hover:text-emerald-700 transition-colors">
                    Clear search →
                </button>
            </div>
        </div>

    </div>
</div>

<style>
/* Smooth row animations for table */
#stocksDashboardList tr {
    animation: fadeSlideIn 0.3s ease-out forwards;
    opacity: 0;
    transform: translateY(10px);
}

#stocksDashboardList tr:nth-child(1) { animation-delay: 0ms; }
#stocksDashboardList tr:nth-child(2) { animation-delay: 25ms; }
#stocksDashboardList tr:nth-child(3) { animation-delay: 50ms; }
#stocksDashboardList tr:nth-child(4) { animation-delay: 75ms; }
#stocksDashboardList tr:nth-child(5) { animation-delay: 100ms; }
#stocksDashboardList tr:nth-child(6) { animation-delay: 125ms; }
#stocksDashboardList tr:nth-child(n+7) { animation-delay: 150ms; }

@keyframes fadeSlideIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover effect on table rows */
#stocksDashboardList tr {
    transition: all 0.2s ease;
}

#stocksDashboardList tr:hover {
    background-color: rgba(16, 185, 129, 0.05); /* Emerald tint */
    transform: translateX(4px);
}

/* Smooth transitions for empty state */
#stocksEmptyState.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Custom scrollbar for webkit */
.data-table::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.data-table::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.data-table::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.data-table::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Focus ring animation */
.glass-input:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); /* Emerald glow */
}
</style>