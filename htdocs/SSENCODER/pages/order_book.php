<div id="orderBookPage" class="hidden">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Order Book</h1>
                <p class="text-sm text-slate-500">Master record of all processed purchase orders.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="relative mb-4">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                    </div>
                    <input type="text" id="obPoFilter" placeholder="Search by PO Number..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg leading-5 bg-slate-50 placeholder-slate-500 focus:outline-none focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                </div>

                <details class="group">
                    <summary class="flex items-center gap-2 text-sm font-semibold text-indigo-600 cursor-pointer hover:text-indigo-800 transition-colors select-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        Show Advanced Filters
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label for="obMonthFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">Month</label>
                            <select id="obMonthFilter" class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all">All Months</option>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m == date('m')) ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label for="obYearFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">Year</label>
                            <select id="obYearFilter" class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <?php 
                                $currentYear = date('Y');
                                for ($y = $currentYear + 1; $y >= $currentYear - 2; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y == $currentYear) ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label for="obLocFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">Location</label>
                            <select id="obLocFilter" class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all">All Locations</option>
                                <option value="Davao">Davao</option>
                                <option value="Gensan">Gensan</option>
                            </select>
                        </div>
                        <div>
                            <label for="obBuFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">Business Unit</label>
                            <select id="obBuFilter" class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all">All BUs</option>
                                <option value="Health">Health</option>
                                <option value="Hygiene">Hygiene</option>
                                <option value="Nutri">Nutri</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="obCustomerFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">Customer</label>
                            <select id="obCustomerFilter" class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all">All Customers</option>
                            </select>
                        </div>
                        <div>
                            <label for="obSoFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">SO Number</label>
                            <input type="text" id="obSoFilter" placeholder="Search SO..." class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="obAddressFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">Address</label>
                            <input type="text" id="obAddressFilter" placeholder="Search Address..." class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </details>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-center gap-4">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Visible Value</p>
                    <p id="orderBookGrandTotal" class="text-2xl font-bold text-emerald-600 truncate">₱0.00</p>
                </div>
                <div class="border-t border-slate-100 pt-4">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Orders Found</p>
                    <p id="orderBookTotalCount" class="text-xl font-bold text-slate-700">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Customer / PO</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Location / BU</th>
                            <th class="px-6 py-4">Total Value</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orderBookList" class="divide-y divide-slate-100">
                        </tbody>
                </table>
            </div>
            
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                <button id="obPrevBtn" class="btn bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-50 shadow-sm text-sm py-1">Previous</button>
                <span id="obPageInfo" class="text-sm font-medium text-slate-600">Page 1 of 1</span>
                <button id="obNextBtn" class="btn bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-50 shadow-sm text-sm py-1">Next</button>
            </div>
        </div>

    </div>
</div>