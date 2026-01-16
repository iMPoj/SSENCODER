<div id="stocksDashboardPage" class="hidden">
    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Inventory & Stocks</h1>
                <p class="text-sm text-slate-500">Real-time visibility into product stock levels and pricing.</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div class="flex flex-col gap-6">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" id="stockSearchInput" placeholder="Search by SKU, Barcode, or Description..." class="block w-full pl-12 pr-4 py-4 border border-slate-300 rounded-lg leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg transition duration-150 ease-in-out shadow-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                    <div>
                        <label for="locFilterStocks" class="block text-xs font-bold text-slate-500 uppercase mb-2">Location</label>
                        <select id="locFilterStocks" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            <option value="Davao">Davao</option>
                            <option value="Gensan">Gensan</option>
                        </select>
                    </div>
                    <div>
                        <label for="buFilter" class="block text-xs font-bold text-slate-500 uppercase mb-2">Business Unit</label>
                        <select id="buFilter" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            <option value="all">All BUs</option>
                            <option value="Health">Health</option>
                            <option value="Hygiene">Hygiene</option>
                            <option value="Nutri">Nutri</option>
                        </select>
                    </div>
                    <div>
                        <label for="stockStatusFilter" class="block text-xs font-bold text-slate-500 uppercase mb-2">Stock Status</label>
                        <select id="stockStatusFilter" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            <option value="all">All Stocks</option>
                            <option value="in_stock">In Stock (>10)</option>
                            <option value="low_stock">Low Stock (1-10)</option>
                            <option value="no_stock">Out of Stock (0)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
             <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-600 uppercase tracking-wider">Inventory List</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-white text-slate-500 font-semibold border-b border-slate-200 sticky top-0 z-10">
                        <tr>
                            <th class="py-4 px-6 w-1/4">Barcode / SKU</th>
                            <th class="py-4 px-6 w-1/3">Description</th>
                            <th class="py-4 px-6">Stock on Hand</th>
                            <th class="py-4 px-6 text-right">Price (per Piece)</th>
                        </tr>
                    </thead>
                    <tbody id="stocksDashboardList" class="divide-y divide-slate-100">
                        </tbody>
                </table>
            </div>
        </div>

    </div>
</div>