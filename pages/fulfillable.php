<div id="fulfillablePage" class="hidden space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-0.5">Operations</p>
            <h1 class="text-2xl font-black text-[#0D111A] tracking-tight">Fulfillable Opportunities</h1>
            <p class="text-sm text-[#6B7280] mt-0.5">Unserved priority items that now have stock available.</p>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <div class="text-right">
                <p class="text-[10px] font-bold text-[#6B7280] uppercase tracking-widest">Total Fulfillable Value</p>
                <p id="fulfillableGrandTotal" class="text-2xl font-black text-emerald-600 tabular-nums">₱0.00</p>
            </div>
            <button id="refreshFulfillableBtn" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:border-emerald-400 hover:text-emerald-600 text-slate-600 font-bold text-sm rounded-xl transition-all shadow-sm">
                <svg id="refreshFulfillableIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card p-4">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-3.5 h-3.5 text-[#6B7280]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            <span class="text-[10px] font-black text-[#6B7280] uppercase tracking-widest">Filters</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1">Month</label>
                <select id="fulfillableMonthFilter" class="glass-input !py-2 !text-sm w-full">
                    <option value="all">All Months</option>
                    <option value="01">January</option><option value="02">February</option>
                    <option value="03">March</option><option value="04">April</option>
                    <option value="05">May</option><option value="06">June</option>
                    <option value="07">July</option><option value="08">August</option>
                    <option value="09">September</option><option value="10">October</option>
                    <option value="11">November</option><option value="12">December</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1">Year</label>
                <select id="fulfillableYearFilter" class="glass-input !py-2 !text-sm w-full">
                    <option value="all">All Years</option>
                    <option value="2024">2024</option><option value="2025">2025</option>
                    <option value="2026">2026</option><option value="2027">2027</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1">Location</label>
                <select id="fulfillableLocFilter" class="glass-input !py-2 !text-sm w-full">
                    <option value="all">All Locations</option>
                    <option value="Davao">Davao</option>
                    <option value="Gensan">Gensan</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1">Business Unit</label>
                <select id="fulfillableBuFilter" class="glass-input !py-2 !text-sm w-full">
                    <option value="all">All BUs</option>
                    <option value="Health">Health</option>
                    <option value="Hygiene">Hygiene</option>
                    <option value="Nutri">Nutri</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider mb-1">Customer</label>
                <select id="fulfillableCustomerFilter" class="glass-input !py-2 !text-sm w-full">
                    <option value="all">All Customers</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70">
                        <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer / PO</th>
                        <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-widest text-slate-400">Item Details</th>
                        <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Qty Needed</th>
                        <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-widest text-slate-400">Available Stock</th>
                        <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="fulfillableList" class="divide-y divide-slate-50"></tbody>
            </table>
        </div>
        <div id="fulfillableEmptyState" class="hidden text-center py-16">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-3">
                <svg class="h-7 w-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-slate-700 font-bold text-sm">All caught up!</p>
            <p class="text-slate-400 text-xs mt-1">All priority orders are fulfilled.</p>
        </div>
    </div>

</div>
