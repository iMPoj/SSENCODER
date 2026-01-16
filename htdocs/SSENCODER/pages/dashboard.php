<div id="dashboardPage">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Executive Dashboard</h1>
                <p class="text-slate-500">Performance overview and inventory metrics.</p>
            </div>
            
            <div class="flex flex-wrap gap-4">
                <div class="w-full md:w-40">
                    <label for="locFilterDashboard" class="block text-xs font-bold text-slate-500 uppercase mb-1">Location</label>
                    <select id="locFilterDashboard" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="all">All Locations</option>
                        <option value="Davao">Davao</option>
                        <option value="Gensan">Gensan</option>
                    </select>
                </div>
                <div class="w-full md:w-40">
                    <label for="buFilterDashboard" class="block text-xs font-bold text-slate-500 uppercase mb-1">Business Unit</label>
                    <select id="buFilterDashboard" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="all">All BUs</option>
                        <option value="Health">Health</option>
                        <option value="Hygiene">Hygiene</option>
                        <option value="Nutri">Nutri</option>
                    </select>
                </div>
                <div class="w-full md:w-48">
                    <label for="customerFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">Customer</label>
                    <select id="customerFilter" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="all">All Customers</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 h-full border-l-4 border-l-blue-600">
                <div><p class="text-xs font-bold text-blue-600 uppercase tracking-wider">Total PO Amount</p><h3 id="stat-total-po-amount" class="text-lg font-bold text-slate-800 mt-1 truncate">₱0.00</h3></div>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 h-full border-l-4 border-l-emerald-500">
                <div><p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Total Served</p><h3 id="stat-total-served" class="text-lg font-bold text-slate-800 mt-1 truncate">₱0.00</h3></div>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 h-full border-l-4 border-l-emerald-500">
                <div><p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Served Qty</p><h3 id="stat-total-qty" class="text-lg font-bold text-slate-800 mt-1 truncate">0</h3></div>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 h-full border-l-4 border-l-indigo-500">
                <div><p class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Fill Rate (PO)</p><h3 id="stat-qty-fill-rate-by-po" class="text-lg font-bold text-slate-800 mt-1 truncate">0.0%</h3></div>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 h-full border-l-4 border-l-amber-500">
                <div><p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Unserved SKUs</p><h3 id="stat-unserved-skus" class="text-lg font-bold text-slate-800 mt-1 truncate">0</h3></div>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 h-full border-l-4 border-l-red-500">
                <div><p class="text-xs font-bold text-red-600 uppercase tracking-wider">Unserved Value</p><h3 id="stat-total-unserved-value" class="text-lg font-bold text-slate-800 mt-1 truncate">₱0.00</h3></div>
            </div>
        </div>
        
        <div>
            <h2 class="text-lg font-bold text-slate-700 mb-4">Sales Performance</h2>
            <div id="sales-summary-container" class="grid grid-cols-1 lg:grid-cols-3 gap-6"></div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 mb-1">Monthly Sales Trend</h3>
                <p class="text-sm text-slate-500 mb-6">Revenue comparison by Business Unit over the selected year.</p>
                <div class="h-80 relative">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-1/2">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 text-center">Fulfillment by Price</h3>
                    <div class="h-32 relative">
                        <canvas id="fulfillmentChartPrice"></canvas>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-1/2">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 text-center">Fulfillment by Quantity</h3>
                    <div class="h-32 relative">
                        <canvas id="fulfillmentChartQty"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Top Products Intelligence</h2>
                    <p class="text-sm text-slate-500">Comparing top performing SKUs across Business Units.</p>
                </div>
                
                <div class="flex bg-slate-100 rounded-lg p-1 gap-1">
                    <button id="btn-mode-price" class="px-4 py-2 text-xs font-bold rounded-md bg-white shadow-sm text-indigo-600 transition-all">By Sales ₱</button>
                    <button id="btn-mode-qty" class="px-4 py-2 text-xs font-bold rounded-md text-slate-500 hover:text-slate-700 transition-all">By Qty #</button>
                </div>
            </div>
            
            <div class="h-96 relative mb-8">
                <canvas id="topProductsChart-Merged"></canvas>
            </div>
            
            <div id="topProductsLegend-Merged" class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-6 border-t border-slate-100"></div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
             <h3 class="text-lg font-bold text-slate-800 mb-4">Top 5 Customers (Served Value)</h3>
             <div class="overflow-y-auto">
                 <table class="w-full text-sm">
                     <thead class="bg-slate-50">
                         <tr>
                             <th class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Customer</th>
                             <th class="py-3 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Value</th>
                         </tr>
                     </thead>
                     <tbody id="topCustomerList" class="divide-y divide-slate-100"></tbody>
                 </table>
             </div>
        </div>

        <div>
            <h2 class="text-xl font-bold text-slate-800 mb-4">Unserved Items Breakdown</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col h-96">
                    <h3 class="text-sm font-bold text-indigo-600 uppercase mb-3 pb-2 border-b border-indigo-100">Health</h3>
                    <div class="overflow-y-auto flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr><th class="py-2 px-2">Item</th><th class="py-2 px-2 text-right">Val</th></tr>
                            </thead>
                            <tbody id="unservedListHealth" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col h-96">
                    <h3 class="text-sm font-bold text-emerald-600 uppercase mb-3 pb-2 border-b border-emerald-100">Hygiene</h3>
                    <div class="overflow-y-auto flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr><th class="py-2 px-2">Item</th><th class="py-2 px-2 text-right">Val</th></tr>
                            </thead>
                            <tbody id="unservedListHygiene" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col h-96">
                    <h3 class="text-sm font-bold text-amber-600 uppercase mb-3 pb-2 border-b border-amber-100">Nutri</h3>
                    <div class="overflow-y-auto flex-1">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr><th class="py-2 px-2">Item</th><th class="py-2 px-2 text-right">Val</th></tr>
                            </thead>
                            <tbody id="unservedListNutri" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="mt-8 space-y-6">
            <h2 class="text-2xl font-bold text-slate-800 border-b pb-3">Individual Customer Analysis</h2>
            <div id="customerDashboards" class="space-y-6"></div>
        </div>

    </div>
</div>