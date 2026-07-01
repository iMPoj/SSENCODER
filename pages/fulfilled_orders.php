<div id="fulfilled_ordersPage" class="page-section hidden animate-fade-in">
    <div class="max-w-7xl mx-auto space-y-6 pb-12">

        <!-- ★ NEW: Hero header (amber/yellow gradient) -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-yellow-500 to-orange-500 shadow-xl shadow-amber-500/25 p-6 md:p-8">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-8 -left-8 w-36 h-36 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-amber-100 text-xs font-bold uppercase tracking-widest mb-0.5">Operations</p>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight leading-none">Fulfilled Orders</h1>
                        <p class="text-amber-100 text-sm mt-1">POs containing items marked as Fulfilled — ready for invoicing.</p>
                    </div>
                </div>
                <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 flex flex-col items-start md:items-end gap-1">
                    <span class="text-amber-100 text-[10px] font-black uppercase tracking-widest">Total Fulfilled Amount</span>
                    <p id="fulfilledHeroTotal" class="text-3xl font-black text-white font-mono tracking-tight">₱0.00</p>
                    <span class="text-amber-100/80 text-[10px] font-bold"><span id="fulfilledHeroPOCount">0</span> POs · <span id="fulfilledHeroQty">0</span> units</span>
                </div>
            </div>
        </div>

        <!-- ★ NEW: 9 Totals KPI grid -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <h2 class="text-xs font-black text-slate-500 uppercase tracking-widest">Totals Summary</h2>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            <!-- Row 1: PO counts -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-cyan-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-cyan-700 uppercase tracking-widest mb-1">Total POs</p>
                            <p id="fulTotalPos" class="text-2xl font-black text-slate-800 tabular-nums">0</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center text-cyan-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                    </div>
                </div>
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-emerald-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-1">Total Served POs</p>
                            <p id="fulTotalServedPos" class="text-2xl font-black text-emerald-700 tabular-nums">0</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                    </div>
                </div>
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-rose-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-rose-700 uppercase tracking-widest mb-1">Total Unserved POs</p>
                            <p id="fulTotalUnservedPos" class="text-2xl font-black text-rose-700 tabular-nums">0</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg></div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Qty -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-indigo-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-indigo-700 uppercase tracking-widest mb-1">Total Qty</p>
                            <p id="fulTotalQty" class="text-2xl font-black text-slate-800 tabular-nums">0</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                    </div>
                </div>
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-emerald-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-1">Total Served Qty</p>
                            <p id="fulTotalServedQty" class="text-2xl font-black text-emerald-700 tabular-nums">0</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
                    </div>
                </div>
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-rose-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-rose-700 uppercase tracking-widest mb-1">Total Unserved Qty</p>
                            <p id="fulTotalUnservedQty" class="text-2xl font-black text-rose-700 tabular-nums">0</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Amounts -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-purple-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-purple-700 uppercase tracking-widest mb-1">Total Amount</p>
                            <p id="fulTotalAmount" class="text-xl font-black text-purple-700 tabular-nums">₱0.00</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8c1.11 0 2.08.4 2.6 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.4-2.6-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    </div>
                </div>
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-emerald-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-1">Total Served Amount</p>
                            <p id="fulTotalServedAmount" class="text-xl font-black text-emerald-700 tabular-nums">₱0.00</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                    </div>
                </div>
                <div class="glass-card p-4 rounded-xl border-l-4 border-l-red-500 hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-black text-red-700 uppercase tracking-widest mb-1">Total Unserved Amount</p>
                            <p id="fulTotalUnservedAmount" class="text-xl font-black text-red-700 tabular-nums">₱0.00</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ★ Reorganized Filters card -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Filter Fulfilled POs</span>
                </div>
                <button id="refreshFulfilledBtn" title="Refresh" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-[11px] font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-sm shadow-amber-200 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </button>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Month</label>
                    <select id="fulfilledMonthFilter" class="glass-input">
                        <option value="all">All Months</option>
                        <?php for($m=1; $m<=12; ++$m): ?>
                            <option value="<?= sprintf('%02d', $m) ?>" <?= date('m') == sprintf('%02d', $m) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Business Unit</label>
                    <select id="fulfilledBuFilter" class="glass-input">
                        <option value="all">All BUs</option>
                        <option value="Health">Health</option>
                        <option value="Hygiene">Hygiene</option>
                        <option value="Nutri">Nutri</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Customer</label>
                    <select id="fulfilledCustomerFilter" class="glass-input">
                        <option value="all">All Customers</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Location</label>
                    <select id="fulfilledLocationFilter" class="glass-input">
                        <option value="all">All Locations</option>
                        <option value="Davao">Davao</option>
                        <option value="Gensan">Gensan</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- PO list -->
        <div id="fulfilledListContainer" class="flex flex-col gap-4"></div>
    </div>
</div>
