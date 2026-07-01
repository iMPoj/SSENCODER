<div id="unservedPage" class="hidden drill-enter">
    <div class="max-w-7xl mx-auto space-y-5 pb-12">

        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-600 via-red-500 to-rose-500 shadow-xl shadow-red-500/25 p-6 md:p-8">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-8 -left-8 w-36 h-36 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-red-100 text-xs font-bold uppercase tracking-widest mb-0.5">Attention Required</p>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight leading-none">Unserved Orders</h1>
                        <p class="text-red-100 text-sm mt-1">Items currently out of stock that need fulfillment.</p>
                    </div>
                </div>
                <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 flex flex-col items-start md:items-end gap-1">
                    <span class="text-red-100 text-[10px] font-black uppercase tracking-widest">Total Unserved Value</span>
                    <p id="unservedGrandTotal" class="text-3xl font-black text-white font-mono tracking-tight">₱0.00</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Filter Orders</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="col-span-2 md:col-span-3 lg:col-span-2">
                    <label for="unSkuFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">SKU / Description</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" id="unSkuFilter" class="glass-input !pl-10" placeholder="Search product...">
                    </div>
                </div>
                <div>
                    <label for="unLocFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Warehouse</label>
                    <select id="unLocFilter" class="glass-input">
                        <option value="all">All Locations</option>
                        <option value="Davao">Davao</option>
                        <option value="Gensan">Gensan</option>
                    </select>
                </div>
                <div>
                    <label for="unBuFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Business Unit</label>
                    <select id="unBuFilter" class="glass-input">
                        <option value="all">All BUs</option>
                        <option value="Health">Health</option>
                        <option value="Hygiene">Hygiene</option>
                        <option value="Nutri">Nutri</option>
                    </select>
                </div>
                <div>
                    <label for="unMonthFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Month</label>
                    <select id="unMonthFilter" class="glass-input">
                        <option value="all">All Months</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($m == date('n')) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="unYearFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Year</label>
                    <select id="unYearFilter" class="glass-input">
                        <?php $cy = date('Y'); for($y = $cy+1; $y >= $cy-2; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($y == $cy) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <label for="unCustomerFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Customer</label>
                <select id="unCustomerFilter" class="glass-input">
                    <option value="all">All Customers</option>
                </select>
            </div>
        </div>
        <!-- View Tabs -->
        <div class="flex gap-1 bg-white border border-slate-100 rounded-2xl shadow-sm p-1.5">
            <button id="unTabOrders" onclick="switchUnservedTab('orders')" class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-sm flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Orders View</button>
            <button id="unTabItems" onclick="switchUnservedTab('items')" class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-red-600 hover:bg-red-50 flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>Items View <span class="text-[10px] font-black bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full">NEW</span></button>
        </div>


        <!-- Table -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <!-- ★ NEW: View toggles -->
            <div class="px-6 py-3 bg-slate-50/40 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3 flex-wrap">
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 cursor-pointer hover:border-red-300 transition-colors">
                        <input type="checkbox" id="unHidePoToggle" class="w-4 h-4 rounded text-red-500 focus:ring-red-400 border-slate-300">
                        <span class="text-xs font-bold text-slate-600">Hide PO numbers</span>
                    </label>
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-slate-200 cursor-pointer hover:border-red-300 transition-colors">
                        <input type="checkbox" id="unExpandAllToggle" class="w-4 h-4 rounded text-red-500 focus:ring-red-400 border-slate-300">
                        <span class="text-xs font-bold text-slate-600">Expand all unserved items</span>
                    </label>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Click <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">▸</span> on any row to peek items</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer &amp; Order Info</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Address</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Location / BU</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Unserved Value</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="unservedList" class="divide-y divide-slate-50"></tbody>
                </table>
            </div>

            <!-- ★ NEW: CSS scoped via inline style for the toggles -->
            <style>
                #unservedList.po-hidden .po-line { display: none !important; }
                #unservedList .items-panel { display: none; }
                #unservedList .items-panel.open { display: table-row; }
                #unservedList.expand-all .items-panel { display: table-row; }
                #unservedList .row-toggle-btn[aria-expanded="true"] svg { transform: rotate(90deg); }
                #unservedList .row-toggle-btn svg { transition: transform 0.15s ease; }
            </style>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-4">
                <div id="unPageInfo" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Page 1 of 1</div>
                <div class="flex items-center gap-2">
                    <button id="unPrevBtn" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 bg-white text-slate-600 hover:border-red-300 hover:text-red-600 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        Previous
                    </button>
                    <button id="unNextBtn" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-red-500 text-white hover:bg-red-600 shadow-sm shadow-red-200 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        Next
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Items View panel (hidden by default) -->
<div id="unItemsView" class="hidden bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden" style="margin-top:16px">
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/40 flex items-center gap-3">
        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Flat Unserved Items</span>
        <span id="unItemsCount" class="text-[10px] font-black bg-red-100 text-red-600 px-2 py-0.5 rounded-full">0 items</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/70">
                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer</th>
                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">PO #</th>
                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">SKU</th>
                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Description</th>
                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Qty</th>
                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Value</th>
                </tr>
            </thead>
            <tbody id="unItemsList">
                <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">Switch to Orders view first to load data, then return here.</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
window.switchUnservedTab = function(tab) {
    var ordersTable = document.querySelector('#unservedPage > div > .space-y-6 > .bg-white:not(#unItemsView), #unservedPage .bg-white.border.border-slate-100.rounded-2xl:not(#unItemsView)');
    // find the orders table wrapper: first bg-white rounded after the tabs
    var tabsEl = document.querySelector('[id="unTabOrders"]');
    var ordersWrap = tabsEl ? tabsEl.closest('.flex.gap-1').nextElementSibling : null;
    var itemsView = document.getElementById('unItemsView');
    var btnOrders = document.getElementById('unTabOrders');
    var btnItems = document.getElementById('unTabItems');
    var activeClass = 'flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-sm flex items-center justify-center gap-2';
    var inactiveClass = 'flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-red-600 hover:bg-red-50 flex items-center justify-center gap-2';
    if (tab === 'orders') {
        if (ordersWrap) ordersWrap.style.display = '';
        if (itemsView) itemsView.classList.add('hidden');
        if (btnOrders) btnOrders.className = activeClass;
        if (btnItems) btnItems.className = inactiveClass;
    } else {
        if (ordersWrap) ordersWrap.style.display = 'none';
        if (itemsView) itemsView.classList.remove('hidden');
        if (btnOrders) btnOrders.className = inactiveClass;
        if (btnItems) btnItems.className = activeClass;
        loadUnservedItemsFlat();
    }
};

window.loadUnservedItemsFlat = async function() {
    var tbody = document.getElementById('unItemsList');
    var countEl = document.getElementById('unItemsCount');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-slate-400"><span class="animate-spin inline-block w-4 h-4 border-2 border-red-400 border-t-transparent rounded-full mr-2"></span>Loading items...</td></tr>';

    // Read current filter values from the unserved page filters
    var month = document.getElementById('unMonthFilter')?.value || 'all';
    var year = document.getElementById('unYearFilter')?.value || 'all';

    try {
        var fd = new FormData();
        fd.append('action', 'get_unserved_items_flat');
        fd.append('month', month);
        fd.append('year', year);
        var res = await fetch('api.php', { method: 'POST', body: fd });
        var data = await res.json();

        if (!data.success) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-red-400">' + (data.message || 'Error loading items.') + '</td></tr>';
            return;
        }

        var rows = data.data;
        if (countEl) countEl.textContent = rows.length + ' items';

        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-slate-400">No unserved items found.</td></tr>';
            return;
        }

        var peso = function(v) { return '₱' + parseFloat(v || 0).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}); };

        tbody.innerHTML = rows.map(function(r) {
            return '<tr class="hover:bg-red-50/30 border-b border-slate-50">' +
                '<td class="px-4 py-2.5 font-bold text-slate-700 text-xs">' + (r.customer || '') + '</td>' +
                '<td class="px-4 py-2.5 font-mono text-xs text-slate-500">' + (r.po_number || '') + '</td>' +
                '<td class="px-4 py-2.5 font-mono font-bold text-xs text-slate-600">' + (r.sku || '') + '</td>' +
                '<td class="px-4 py-2.5 text-xs text-slate-600">' + (r.description || '') + '</td>' +
                '<td class="px-4 py-2.5 text-xs text-right font-bold text-slate-700">' + r.quantity + '</td>' +
                '<td class="px-4 py-2.5 text-xs text-right font-bold text-red-600">' + peso(r.price) + '</td>' +
            '</tr>';
        }).join('');
    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-red-400">Network error. Please try again.</td></tr>';
    }
};
</script>
