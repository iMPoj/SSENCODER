<div id="adminPage" class="hidden">
    <div class="mb-6 flex flex-wrap gap-2 border-b border-slate-300 bg-white p-2 rounded-t-lg shadow-sm">
        <button id="inventoryAdminBtn" class="admin-tab-btn active py-2 px-4 border-b-2 border-indigo-500 font-bold text-indigo-600 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            Inventory
        </button>
        <button id="bulkOrderAdminBtn" class="admin-tab-btn py-2 px-4 text-slate-500 hover:border-slate-300 hover:text-slate-700 border-b-2 border-transparent flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            PDF Order Entry
        </button>
        <button id="customerAdminBtn" class="admin-tab-btn py-2 px-4 text-slate-500 hover:border-slate-300 hover:text-slate-700 border-b-2 border-transparent flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            Customers
        </button>
        <button id="exportAdminBtn" class="admin-tab-btn py-2 px-4 text-slate-500 hover:border-slate-300 hover:text-slate-700 border-b-2 border-transparent flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export
        </button>
        <button id="targetsAdminBtn" class="admin-tab-btn py-2 px-4 text-slate-500 hover:border-slate-300 hover:text-slate-700 border-b-2 border-transparent flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            Settings & Targets
        </button>
    </div>

    <div id="adminInventorySection" class="section-content space-y-6">
        
        <div class="content-card flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Inventory Operations</h2>
                <p class="text-sm text-slate-500">Select a location and use bulk tools to manage stock.</p>
            </div>
            <div>
                 <label for="adminLocFilter" class="block text-xs font-bold text-slate-500 uppercase mb-1">Target Location</label>
                 <select id="adminLocFilter" class="block w-40 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="Davao">Davao</option>
                    <option value="Gensan">Gensan</option>
                </select>
            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="space-y-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider border-b pb-2">Stock Management</h3>
                    
                    <div class="content-card border-l-4 border-l-blue-500 relative overflow-hidden">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-blue-700">Bulk Add (Qty Only)</h2>
                                <p class="text-sm text-slate-600">Adds stock. Ignores descriptions. Keeps old price.</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <textarea id="bulkAddStockNoPriceInput" rows="8" class="block w-full rounded-md border-slate-300 shadow-sm font-mono text-xs" placeholder="2078496    ENFAMIL A+ ONE PWD    60
3314719    LACTUM 0-6 MTHS       720"></textarea>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-400">Format: <strong>SKU</strong> ... Description ... <strong>Qty</strong></span>
                                <button id="processBulkAddStockNoPriceBtn" class="btn bg-blue-600 hover:bg-blue-700 text-white shadow-md border-transparent">Add Stock</button>
                            </div>
                        </div>
                    </div>

                    <div class="content-card border-l-4 border-l-emerald-500 relative overflow-hidden">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-emerald-700">Bulk Add (With Price)</h2>
                                <p class="text-sm text-slate-600">Adds stock AND updates price.</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <textarea id="bulkAddStockInput" rows="5" class="block w-full rounded-md border-slate-300 shadow-sm font-mono text-xs" placeholder="1558051 50 120.50..."></textarea>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-400">Format: <strong>SKU</strong> ... <strong>Qty</strong> <strong>Price</strong></span>
                                <button id="processBulkAddStockBtn" class="btn bg-emerald-600 hover:bg-emerald-700 text-white shadow-md border-transparent">Add Stock & Price</button>
                            </div>
                        </div>
                    </div>

                    <div class="content-card border-l-4 border-l-amber-500 relative overflow-hidden">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-amber-700">Bulk Reset & Update</h2>
                                <p class="text-sm text-slate-600"><strong>WARNING:</strong> Resets stock to exact numbers.</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <textarea id="bulkUpdateStockInput" rows="5" class="block w-full rounded-md border-slate-300 shadow-sm font-mono text-xs" placeholder="1558051 1075 120.50..."></textarea>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-400">Format: <strong>SKU</strong> ... <strong>Qty</strong> <strong>Price</strong></span>
                                <button id="processBulkUpdateStockBtn" class="btn bg-amber-600 hover:bg-amber-700 text-white shadow-md border-transparent">Process Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider border-b pb-2">Product Data</h3>

                    <div class="content-card">
                        <h2 class="text-xl font-bold mb-2 text-slate-800">Register New Products</h2>
                        <p class="text-sm text-slate-500 mb-3">Create new product entries.</p>
                        <div class="space-y-3">
                            <textarea id="bulkAddProductsInput" rows="5" class="block w-full rounded-md border-slate-300 shadow-sm font-mono text-xs" placeholder="Nutri 8712045039953 3286526..."></textarea>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-400">Format: BU Barcode SKU Desc...</span>
                                <button id="processBulkAddProductsBtn" class="btn btn-primary shadow-md">Register</button>
                            </div>
                        </div>
                    </div>

                    <div class="content-card">
                        <h2 class="text-xl font-bold mb-2 text-slate-800">Link SKU Aliases</h2>
                        <p class="text-sm text-slate-500 mb-3">Link customer SKUs to master barcodes.</p>
                        <div class="space-y-3">
                            <textarea id="bulkAddAliasInput" rows="5" class="block w-full rounded-md border-slate-300 shadow-sm font-mono text-xs" placeholder="69276 DUREX ... 480123456"></textarea>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-400">Format: CustomerSKU ... MasterBarcode</span>
                                <button id="processBulkAddAliasBtn" class="btn btn-secondary shadow-md">Link</button>
                            </div>
                        </div>
                    </div>

                    <div class="content-card border-l-4 border-l-slate-500">
                        <div class="flex items-start gap-4">
                            <div class="bg-slate-100 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            </div>
                            <div class="flex-grow">
                                <h2 class="text-xl font-semibold mb-2">Data Quality Audit</h2>
                                <p class="text-sm text-slate-600 mb-4">Scan database for SKUs that are not linked to a master barcode.</p>
                                <button id="runUnlinkedSkuReportBtn" class="btn bg-slate-600 hover:bg-slate-700 text-white shadow-sm">Run Audit</button>
                                <div id="unlinkedSkuResults" class="hidden mt-6">
                                    <h3 class="text-lg font-semibold mb-2 border-b pb-2">Results</h3>
                                    <table class="data-table"><thead><tr><th>SKU</th><th>Description</th><th>Current Stock</th></tr></thead><tbody id="unlinkedSkuList"></tbody></table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div id="adminBulkOrderSection" class="hidden">
        <div class="content-card max-w-4xl mx-auto">
            <div class="bg-indigo-50 border border-indigo-100 p-6 rounded-lg shadow-sm mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                         <h2 class="text-lg font-bold text-indigo-900">Step 1: Configuration</h2>
                         <p class="text-sm text-indigo-600">Select where this order is going.</p>
                    </div>
                    <div class="flex gap-4">
                        <div>
                            <label for="pdfParseLocation" class="block text-xs font-semibold text-indigo-800 uppercase">Location</label>
                            <select id="pdfParseLocation" class="mt-1 block w-40 rounded-md border-indigo-300 shadow-sm font-medium text-sm"><option value="">Select...</option><option value="Davao">Davao</option><option value="Gensan">Gensan</option></select>
                        </div>
                        <div>
                            <label for="pdfParseBu" class="block text-xs font-semibold text-indigo-800 uppercase">Business Unit</label>
                            <select id="pdfParseBu" class="mt-1 block w-40 rounded-md border-indigo-300 shadow-sm font-medium text-sm"><option value="">Select...</option><option value="Health">Health</option><option value="Hygiene">Hygiene</option><option value="Nutri">Nutri</option></select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label for="rawPdfInput" class="block text-lg font-bold text-slate-800 mb-2">Step 2: Input Data</label>
                    <p class="text-sm text-slate-500 mb-2">Copy all text from the PDF file and paste it here.</p>
                    <textarea id="rawPdfInput" rows="15" class="w-full p-4 rounded-lg border-slate-300 shadow-sm font-mono text-xs bg-slate-50 focus:bg-white transition-colors" placeholder="Paste full PDF text..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button id="parsePdfTextBtn" class="btn btn-primary shadow-lg text-lg px-8">Parse Text & Review Order</button>
                </div>
            </div>
        </div>
    </div>

    <div id="adminCustomerSection" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
            
            <div class="content-card lg:col-span-1 h-fit">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Customer Database
                </h2>
                <form id="addCustomerForm" class="flex gap-2 mb-6">
                    <input type="text" id="newCustomerName" placeholder="New Customer Name" class="flex-grow block w-full rounded-md border-slate-300 shadow-sm text-sm">
                    <button type="submit" class="btn btn-primary py-1">Add</button>
                </form>
                <div class="overflow-y-auto max-h-[500px] pr-2">
                     <div id="customerManagementList" class="space-y-2"></div>
                </div>
            </div>

            <div class="content-card lg:col-span-2">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Address Mappings
                    </h2>
                    <button id="addAddressCodeBtn" class="btn btn-secondary text-sm">Add New Mapping</button>
                </div>
                <p class="text-sm text-slate-500 mb-4">Maps PO addresses to Customer Codes.</p>
                <div class="overflow-x-auto max-h-[600px]">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Address</th>
                                <th>Customer Code</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="addressCodeList"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="adminExportSection" class="hidden">
        <div class="content-card max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Data Export Center</h2>
            <p class="text-sm text-slate-600 mb-6">Download your order history for analysis.</p>
            
            <div class="bg-slate-50 p-6 rounded-lg border border-slate-200 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="exportLoc" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Location</label>
                        <select id="exportLoc" class="block w-full rounded-md border-slate-300 shadow-sm"><option value="all">All Locations</option><option value="Davao">Davao</option><option value="Gensan">Gensan</option></select>
                    </div>
                    <div>
                        <label for="exportMonth" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Month</label>
                        <select id="exportMonth" class="block w-full rounded-md border-slate-300 shadow-sm"><option value="1">January</option><option value="2">February</option><option value="3">March</option><option value="4">April</option><option value="5">May</option><option value="6">June</option><option value="7">July</option><option value="8">August</option><option value="9">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select>
                    </div>
                    <div>
                        <label for="exportYear" class="block text-xs font-semibold text-slate-500 uppercase mb-1">Year</label>
                        <input type="number" id="exportYear" placeholder="e.g., 2025" class="block w-full rounded-md border-slate-300 shadow-sm">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button id="exportCsvBtn" class="btn bg-emerald-600 hover:bg-emerald-700 text-white flex justify-center items-center gap-2 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Download CSV
                </button>
                <button id="exportTsvBtn" class="btn bg-indigo-600 hover:bg-indigo-700 text-white flex justify-center items-center gap-2 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Download for Excel
                </button>
            </div>
        </div>
    </div>

    <div id="adminTargetsSection" class="hidden space-y-8">
        
        <div class="content-card max-w-5xl mx-auto border-t-4 border-indigo-500">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Global Dashboard Date</h2>
                    <p class="text-sm text-slate-600">Controls the displayed month for all users on the main dashboard.</p>
                </div>
                <div class="flex items-end gap-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div>
                        <label for="displayMonth" class="block text-xs font-bold text-slate-500 uppercase mb-1">Month</label>
                        <select id="displayMonth" class="block w-32 rounded-md border-slate-300 shadow-sm text-sm">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 10)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label for="displayYear" class="block text-xs font-bold text-slate-500 uppercase mb-1">Year</label>
                        <select id="displayYear" class="block w-24 rounded-md border-slate-300 shadow-sm text-sm">
                            <?php 
                            $currentYear = date('Y');
                            for ($y = $currentYear + 1; $y >= $currentYear - 3; $y--): ?>
                                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button id="setDisplayMonthBtn" class="btn btn-primary text-sm">Save</button>
                </div>
            </div>
        </div>

        <div class="content-card max-w-5xl mx-auto">
            <div class="flex items-center gap-2 mb-6">
                <div class="bg-amber-100 p-2 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg></div>
                <h2 class="text-2xl font-bold text-slate-800">Monthly Sales Targets</h2>
            </div>
            
            <form id="targetsForm">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <div class="space-y-4">
                        <h3 class="font-bold text-lg text-indigo-700 border-b border-indigo-100 pb-2">Davao Targets</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="flex items-center gap-4">
                                <label class="w-24 font-medium text-slate-600">Health</label>
                                <input type="number" id="target-davao-health" data-location="Davao" data-bu="Health" class="target-input flex-grow block rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00" step="any">
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="w-24 font-medium text-slate-600">Hygiene</label>
                                <input type="number" id="target-davao-hygiene" data-location="Davao" data-bu="Hygiene" class="target-input flex-grow block rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00" step="any">
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="w-24 font-medium text-slate-600">Nutri</label>
                                <input type="number" id="target-davao-nutri" data-location="Davao" data-bu="Nutri" class="target-input flex-grow block rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00" step="any">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="font-bold text-lg text-emerald-700 border-b border-emerald-100 pb-2">Gensan Targets</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="flex items-center gap-4">
                                <label class="w-24 font-medium text-slate-600">Health</label>
                                <input type="number" id="target-gensan-health" data-location="Gensan" data-bu="Health" class="target-input flex-grow block rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="0.00" step="any">
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="w-24 font-medium text-slate-600">Hygiene</label>
                                <input type="number" id="target-gensan-hygiene" data-location="Gensan" data-bu="Hygiene" class="target-input flex-grow block rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="0.00" step="any">
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="w-24 font-medium text-slate-600">Nutri</label>
                                <input type="number" id="target-gensan-nutri" data-location="Gensan" data-bu="Nutri" class="target-input flex-grow block rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="0.00" step="any">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end border-t pt-6">
                    <button type="submit" class="btn btn-primary text-lg px-8 shadow-lg">Save All Targets</button>
                </div>
            </form>
        </div>
    </div>
</div>