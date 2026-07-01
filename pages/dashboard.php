<div id="dashboardPage" class="page-section">
    <div class="max-w-[95rem] mx-auto space-y-6 pb-12 pt-4">
        
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#E42278] via-[#D11A6B] to-[#9B1259] shadow-xl shadow-pink-500/20 p-6 mb-2">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 22px 22px;"></div>
            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-44 h-44 rounded-full bg-pink-300/10 blur-2xl"></div>
            <div class="relative flex flex-col md:flex-row justify-between md:items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 shadow-inner">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <p class="text-pink-100 text-[10px] font-black uppercase tracking-[0.2em] mb-0.5">SSENCODER · Executive</p>
                        <h1 class="text-3xl font-black text-white tracking-tight leading-none">Performance Dashboard</h1>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <div class="flex bg-black/20 p-1 rounded-lg border border-white/10 backdrop-blur-md">
                        <button id="btn-vat-in" class="px-4 py-1.5 text-xs font-bold rounded shadow-sm bg-white text-[#E42278] transition-all">VAT IN</button>
                        <button id="btn-vat-ex" class="px-4 py-1.5 text-xs font-bold rounded text-white/70 hover:text-white transition-all">VAT EX</button>
                    </div>
                    <div class="flex bg-black/20 p-1 rounded-lg border border-white/10 backdrop-blur-md">
                        <button id="btn-disc-without" class="px-4 py-1.5 text-xs font-bold rounded shadow-sm bg-white text-[#E42278] transition-all">BASE (W/O DISC)</button>
                        <button id="btn-disc-with" class="px-4 py-1.5 text-xs font-bold rounded text-white/70 hover:text-white transition-all">NET (W/ DISC)</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col lg:flex-row justify-between gap-4 items-center z-[60] relative">
            <div class="flex items-center gap-2">
                <span class="inline-block w-1.5 h-5 bg-gradient-to-b from-[#E42278] to-[#ED7BAB] rounded-full"></span>
                <h2 class="text-sm font-black text-gray-700 uppercase tracking-widest">Global Filters</h2>
            </div>
            <div class="flex flex-wrap gap-3 items-center w-full lg:w-auto">
                <div class="flex bg-gray-100/80 p-1 rounded-lg border border-gray-200">
                    <button class="global-loc-btn px-4 py-1.5 text-xs font-bold rounded bg-white shadow-sm text-[#E42278] transition-all" data-val="all">All Loc</button>
                    <button class="global-loc-btn px-4 py-1.5 text-xs font-bold rounded text-gray-500 hover:text-gray-900 transition-all" data-val="Davao">Davao</button>
                    <button class="global-loc-btn px-4 py-1.5 text-xs font-bold rounded text-gray-500 hover:text-gray-900 transition-all" data-val="Gensan">Gensan</button>
                </div>
                <div class="flex bg-gray-100/80 p-1 rounded-lg border border-gray-200">
                    <button class="global-bu-btn px-4 py-1.5 text-xs font-bold rounded bg-white shadow-sm text-[#E42278] transition-all" data-val="all">All BU</button>
                    <button class="global-bu-btn px-4 py-1.5 text-xs font-bold rounded text-gray-500 hover:text-gray-900 transition-all" data-val="Nutri">Nutri</button>
                    <button class="global-bu-btn px-4 py-1.5 text-xs font-bold rounded text-gray-500 hover:text-gray-900 transition-all" data-val="Health">Health</button>
                    <button class="global-bu-btn px-4 py-1.5 text-xs font-bold rounded text-gray-500 hover:text-gray-900 transition-all" data-val="Hygiene">Hygiene</button>
                </div>
                <div class="relative w-full sm:w-56 z-[100]" id="customerFilterContainer">
                    <div id="customerFilterBtn" class="bg-gray-100/80 text-xs py-2 px-3 w-full rounded-lg border border-gray-200 hover:bg-gray-200 cursor-pointer flex justify-between items-center transition-colors font-bold text-gray-600">
                        <span id="customerFilterLabel" class="truncate">All Customers</span>
                        <svg class="w-3 h-3 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <div id="customerFilterDropdown" class="absolute right-0 z-50 w-[250px] mt-1 bg-white border border-gray-200 rounded-xl shadow-xl hidden max-h-72 flex flex-col overflow-hidden">
                        <div class="p-2 border-b border-gray-100 bg-gray-50">
                            <input type="text" id="customerFilterSearch" class="w-full text-xs p-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#E42278]" placeholder="Search...">
                        </div>
                        <div id="customerFilterList" class="p-2 overflow-y-auto space-y-1 custom-scrollbar text-sm flex-1"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="group relative overflow-hidden bg-white/80 backdrop-blur-md p-5 rounded-2xl border border-gray-100 shadow-sm border-b-4 border-b-emerald-500 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-emerald-500/10 group-hover:bg-emerald-500/20 transition-colors"></div>
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Total Sales (Served)</p>
                <h3 id="kpi-served" class="text-2xl font-black text-gray-800 tabular-nums">₱0.00</h3>
            </div>
            <div class="group relative overflow-hidden bg-white/80 backdrop-blur-md p-5 rounded-2xl border border-gray-100 shadow-sm border-b-4 border-b-rose-500 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-rose-500/10 group-hover:bg-rose-500/20 transition-colors"></div>
                <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-1">Total Unserved</p>
                <h3 id="kpi-unserved" class="text-2xl font-black text-gray-800 tabular-nums">₱0.00</h3>
            </div>
            <div class="group relative overflow-hidden bg-white/80 backdrop-blur-md p-5 rounded-2xl border border-gray-100 shadow-sm border-b-4 border-b-amber-500 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-amber-500/10 group-hover:bg-amber-500/20 transition-colors"></div>
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Total Cancelled</p>
                <h3 id="kpi-cancelled" class="text-2xl font-black text-gray-800 tabular-nums">₱0.00</h3>
            </div>
            <div class="group relative overflow-hidden bg-white/80 backdrop-blur-md p-5 rounded-2xl border border-gray-100 shadow-sm border-b-4 border-b-indigo-500 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-indigo-500/10 group-hover:bg-indigo-500/20 transition-colors"></div>
                <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1">Prev Month Fulfilled</p>
                <h3 id="kpi-prev" class="text-2xl font-black text-gray-800 tabular-nums">₱0.00</h3>
            </div>
            <div class="group relative overflow-hidden bg-white/80 backdrop-blur-md p-5 rounded-2xl border border-gray-100 shadow-sm border-b-4 border-b-blue-500 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-blue-500/10 group-hover:bg-blue-500/20 transition-colors"></div>
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">Total Units Sold</p>
                <h3 id="kpi-qty" class="text-2xl font-black text-gray-800 tabular-nums">0</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            
            <div class="xl:col-span-1 flex flex-col gap-4">
                <div class="bg-white/90 backdrop-blur-md border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-black text-amber-600 uppercase tracking-widest mb-1">Nutri Fulfill</h3>
                        <p class="text-xs text-gray-400 font-medium">Service level</p>
                    </div>
                    <div class="relative w-20 h-20"><canvas id="donutNutri"></canvas></div>
                </div>
                <div class="bg-white/90 backdrop-blur-md border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-1">Health Fulfill</h3>
                        <p class="text-xs text-gray-400 font-medium">Service level</p>
                    </div>
                    <div class="relative w-20 h-20"><canvas id="donutHealth"></canvas></div>
                </div>
                <div class="bg-white/90 backdrop-blur-md border border-gray-100 p-5 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-black text-emerald-600 uppercase tracking-widest mb-1">Hygiene Fulfill</h3>
                        <p class="text-xs text-gray-400 font-medium">Service level</p>
                    </div>
                    <div class="relative w-20 h-20"><canvas id="donutHygiene"></canvas></div>
                </div>

                <div class="bg-white/90 backdrop-blur-md border border-gray-100 p-5 rounded-2xl shadow-sm flex flex-col min-h-[250px]">
                    <h3 class="text-xs font-black text-gray-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Recent Sales
                    </h3>
                    <div id="recentSalesFeed" class="space-y-2 overflow-y-auto max-h-[300px] custom-scrollbar pr-1"></div>
                </div>

                <div class="bg-white/90 backdrop-blur-md border border-gray-100 p-6 rounded-2xl shadow-sm flex flex-col">
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest mb-6 flex items-center gap-3">
                        <div class="w-1 h-5 bg-gradient-to-b from-[#E42278] to-[#ED7BAB] rounded-full"></div>
                        BU Breakdown Matrix
                    </h3>
                    <div id="buBreakdownTable" class="flex flex-col gap-3"></div>
                </div>
            </div>

            <div class="xl:col-span-2 flex flex-col gap-6 h-full xl:min-h-0">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <div class="bg-white/90 backdrop-blur-md border border-gray-100 p-6 rounded-2xl shadow-sm flex flex-col h-fit">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-1 h-5 bg-[#E42278] rounded-full"></div>
                            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Top 5 Salesmen</h3>
                        </div>
                        <div class="relative w-full h-[240px]">
                            <canvas id="salesmanBarChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white/90 backdrop-blur-md border border-gray-100 p-6 rounded-2xl shadow-sm flex flex-col h-fit">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-1 h-5 bg-blue-500 rounded-full"></div>
                            <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Top 5 Customers</h3>
                        </div>
                        <div class="relative w-full h-[240px]">
                            <canvas id="customerBarChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white/90 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm overflow-hidden flex flex-col flex-1 min-h-[500px] xl:min-h-0">

                    <div class="flex flex-wrap border-b border-gray-100 bg-gray-50/50">
                        <button class="data-tab-btn active px-4 py-4 text-xs font-black uppercase tracking-wider text-[#E42278] border-b-2 border-[#E42278] hover:bg-gray-50 transition-colors flex-1 text-center" data-target="tab-products">Top Products</button>
                        <button class="data-tab-btn px-4 py-4 text-xs font-black uppercase tracking-wider text-gray-500 border-b-2 border-transparent hover:text-gray-800 hover:bg-gray-50 transition-colors flex-1 text-center" data-target="tab-unserved">Unserved</button>
                        <button class="data-tab-btn px-4 py-4 text-xs font-black uppercase tracking-wider text-gray-500 border-b-2 border-transparent hover:text-gray-800 hover:bg-gray-50 transition-colors flex-1 text-center" data-target="tab-cancelled">Cancelled</button>
                        <button class="data-tab-btn px-4 py-4 text-xs font-black uppercase tracking-wider text-gray-500 border-b-2 border-transparent hover:text-gray-800 hover:bg-gray-50 transition-colors flex-1 text-center" data-target="tab-prev">Prev Fulfilled</button>
                        <button class="data-tab-btn px-4 py-4 text-xs font-black uppercase tracking-wider text-blue-500 border-b-2 border-transparent hover:text-blue-800 hover:bg-blue-50 transition-colors flex-1 text-center" data-target="tab-lvs">Store LVs</button>
                    </div>

                    <div class="p-4 border-b border-gray-100 flex justify-end bg-white">
                        <div class="flex bg-gray-100/80 p-1 rounded-lg border border-gray-200">
                            <button class="filter-btn-tbl px-3 py-1 text-[10px] font-bold rounded bg-white shadow-sm text-indigo-600 transition-all" data-bu="all">ALL</button>
                            <button class="filter-btn-tbl px-3 py-1 text-[10px] font-bold rounded text-gray-500 hover:text-gray-800 transition-all" data-bu="Nutri">NUTRI</button>
                            <button class="filter-btn-tbl px-3 py-1 text-[10px] font-bold rounded text-gray-500 hover:text-gray-800 transition-all" data-bu="Health">HEALTH</button>
                            <button class="filter-btn-tbl px-3 py-1 text-[10px] font-bold rounded text-gray-500 hover:text-gray-800 transition-all" data-bu="Hygiene">HYGIENE</button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-auto custom-scrollbar bg-white min-h-[400px] max-h-[900px]">
                        
                        <table id="tab-products" class="data-tab-content w-full text-sm text-left">
                            <thead class="bg-gray-50/80 border-b border-gray-100 sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">SKU / Product</th>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">Category</th>
                                    <th class="py-3 px-4 text-center font-bold text-gray-500 uppercase tracking-wider text-[10px]">Qty Sold</th>
                                    <th class="py-3 px-4 text-right font-bold text-gray-500 uppercase tracking-wider text-[10px]">Total Value</th>
                                </tr>
                            </thead>
                            <tbody id="topProductsTable" class="divide-y divide-gray-50"></tbody>
                        </table>

                        <table id="tab-unserved" class="data-tab-content hidden w-full text-sm text-left">
                            <thead class="bg-gray-50/80 border-b border-gray-100 sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">SKU / Product</th>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">Category</th>
                                    <th class="py-3 px-4 text-center font-bold text-gray-500 uppercase tracking-wider text-[10px]">Qty Missed</th>
                                    <th class="py-3 px-4 text-right font-bold text-gray-500 uppercase tracking-wider text-[10px]">Lost Value</th>
                                </tr>
                            </thead>
                            <tbody id="unservedItemsTable" class="divide-y divide-gray-50"></tbody>
                        </table>

                        <table id="tab-cancelled" class="data-tab-content hidden w-full text-sm text-left">
                            <thead class="bg-gray-50/80 border-b border-gray-100 sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">PO & Customer</th>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">Category</th>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">Reason</th>
                                    <th class="py-3 px-4 text-right font-bold text-gray-500 uppercase tracking-wider text-[10px]">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="cancelledOrdersTable" class="divide-y divide-gray-50"></tbody>
                        </table>

                        <table id="tab-prev" class="data-tab-content hidden w-full text-sm text-left">
                            <thead class="bg-gray-50/80 border-b border-gray-100 sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">SKU / Product</th>
                                    <th class="py-3 px-4 font-bold text-gray-500 uppercase tracking-wider text-[10px]">Customer</th>
                                    <th class="py-3 px-4 text-center font-bold text-gray-500 uppercase tracking-wider text-[10px]">Qty Fulfilled</th>
                                    <th class="py-3 px-4 text-right font-bold text-gray-500 uppercase tracking-wider text-[10px]">Value</th>
                                </tr>
                            </thead>
                            <tbody id="prevFulfilledTable" class="divide-y divide-gray-50"></tbody>
                        </table>

                        <table id="tab-lvs" class="data-tab-content hidden w-full text-sm text-left">
                            <thead class="bg-blue-50/80 border-b border-blue-100 sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-4 font-black text-blue-800 uppercase tracking-wider text-[10px]">Customer / Store Name</th>
                                    <th class="py-3 px-4 font-black text-blue-800 uppercase tracking-wider text-[10px] text-right">Current Sales</th>
                                    <th class="py-3 px-4 font-black text-blue-800 uppercase tracking-wider text-[10px] text-right">LV Limit</th>
                                    <th class="py-3 px-4 font-black text-blue-800 uppercase tracking-wider text-[10px]">Utilization</th>
                                </tr>
                            </thead>
                            <tbody id="storeLvsTable" class="divide-y divide-gray-50"></tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>