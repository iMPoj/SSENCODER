<div id="adminPage" class="hidden drill-enter">
    <div class="mb-8 flex flex-nowrap overflow-x-auto gap-2 border-b border-[rgba(13,17,26,0.08)] bg-white/50 backdrop-blur-md p-2 rounded-xl shadow-sm custom-scrollbar">
        <button id="inventoryAdminBtn" class="admin-tab-btn active py-3 px-6 border-b-2 border-[#E42278] font-bold text-[#E42278] flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            Inventory
        </button>
        <button id="translatorAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg" data-target="admin-tab-translator">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Translator
        </button>
        <button id="pdfAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            PDF Entry
        </button>
        <button id="customerAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            Customers
        </button>
        <button id="exportAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export
        </button>
        <button id="targetsAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            Settings
        </button>
        <!-- NEW PRODUCTS TAB BUTTON -->
        <button id="productsAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            Products
        </button>
        <button id="customerLvsAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            Store LVs
        </button>
        <button id="converterAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Converter
        </button>
        <button id="mergerAdminBtn" class="admin-tab-btn py-3 px-6 text-[#6B7280] hover:text-[#E42278] border-b-2 border-transparent flex items-center gap-2 transition-all hover:bg-pink-50 rounded-t-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Merger
        </button>
    </div>

<!-- INVENTORY SECTION -->
    <div id="adminInventorySection" class="section-content space-y-6 drill-enter">
        <div class="glass-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-l-4 border-l-[#E42278]">
            <div>
                <h2 class="text-2xl font-bold text-[#0D111A]">Inventory Operations</h2>
                <p class="text-sm text-[#6B7280]">Select a location and use bulk tools to manage stock.</p>
            </div>
          </div>

        <div class="max-w-7xl mx-auto space-y-8 mt-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="glass-card p-6 border-l-4 border-l-blue-500 ring-1 ring-blue-200 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-xl font-bold text-blue-800">Bulk Add (Qty Only)</h2>
                                <span class="text-[9px] font-black bg-blue-500 text-white px-2 py-0.5 rounded-full uppercase tracking-wider">Most Used</span>
                            </div>
                            <p class="text-xs text-[#6B7280]">Adds stock. Ignores descriptions. Keeps current price.</p>
                        </div>
                    </div>
                    <div class="space-y-4 flex-grow flex flex-col">
                        <textarea id="bulkAddStockNoPriceInput" rows="6" class="glass-input font-mono text-xs flex-grow" placeholder="2078496 ENFAMIL A+ ONE PWD 60&#10;3314719 LACTUM 0-6 MTHS 720"></textarea>
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-blue-50/50 p-3 rounded-xl border border-blue-100 mt-auto gap-3">
                            <span class="text-[10px] text-blue-600 font-medium whitespace-nowrap">Format: SKU ... Description ... Qty</span>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button id="processBulkAddStockNoPriceDavaoBtn" class="flex-1 sm:flex-none btn-primary !bg-blue-600 hover:!bg-blue-700 !shadow-sm !py-2 !px-3 text-xs">Add to Davao</button>
                                <button id="processBulkAddStockNoPriceGensanBtn" class="flex-1 sm:flex-none btn-primary !bg-emerald-600 hover:!bg-emerald-700 !shadow-sm !py-2 !px-3 text-xs">Add to Gensan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Reset & Update -->
                <div class="glass-card p-6 border-l-4 border-l-amber-500 flex flex-col">
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-amber-800">Bulk Reset & Update</h2>
                        <p class="text-xs text-amber-600 font-medium">WARNING: Resets stock to exact numbers provided.</p>
                    </div>
                    <div class="space-y-4 flex-grow flex flex-col">
                        <textarea id="bulkUpdateStockInput" rows="6" class="glass-input font-mono text-xs flex-grow" placeholder="1558051 ENFAMIL A+ 1075 120.50&#10;3314719 LACTUM 0-6 500 89.25"></textarea>
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-amber-50/50 p-3 rounded-xl border border-amber-100 mt-auto gap-3">
                            <span class="text-[10px] text-amber-600 font-medium whitespace-nowrap">Format: SKU ... Description ... Qty ... Price</span>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button id="processBulkUpdateStockDavaoBtn" class="flex-1 sm:flex-none btn-primary !bg-amber-600 hover:!bg-amber-700 !shadow-sm !py-2 !px-3 text-xs">Reset Davao</button>
                                <button id="processBulkUpdateStockGensanBtn" class="flex-1 sm:flex-none btn-primary !bg-emerald-600 hover:!bg-emerald-700 !shadow-sm !py-2 !px-3 text-xs">Reset Gensan</button>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Bottom Row: Audit -->
            <div class="glass-card p-6 border-l-4 border-l-gray-800">
                <div class="flex items-start gap-4">
                    <div class="bg-gray-100 p-3 rounded-xl border border-gray-200 hidden sm:block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    </div>
                    <div class="flex-grow">
                        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-xl font-bold mb-1 text-[#0D111A]">Data Quality Audit</h2>
                                <p class="text-xs text-[#6B7280]">Identify SKUs missing master barcode links.</p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <button id="runUnlinkedSkuReportDavaoBtn" class="btn-secondary !py-2 !px-4 text-xs font-bold whitespace-nowrap">Audit Davao</button>
                                <button id="runUnlinkedSkuReportGensanBtn" class="btn-secondary !py-2 !px-4 text-xs font-bold whitespace-nowrap">Audit Gensan</button>
                            </div>
                        </div>
                        
                        <div id="unlinkedSkuResults" class="hidden mt-4 animate-fadeIn">
                            <h3 class="text-sm font-bold mb-3 border-b border-[rgba(13,17,26,0.08)] pb-2 uppercase tracking-wider">Unlinked Results</h3>
                            <div class="overflow-hidden rounded-xl border border-[rgba(13,17,26,0.08)]">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-[#F9FAFB]">
                                        <tr><th class="p-3">SKU</th><th class="p-3">Description</th><th class="p-3">Stock</th></tr>
                                    </thead>
                                    <tbody id="unlinkedSkuList" class="divide-y divide-[rgba(13,17,26,0.08)] bg-white"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- BULK ORDER SECTION -->
    <div id="adminBulkOrderSection" class="hidden drill-enter">
        <div class="glass-card max-w-4xl mx-auto overflow-hidden">
            <div class="bg-gradient-to-br from-pink-50 to-white p-8 border-b border-pink-100">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h2 class="text-2xl font-black text-gradient uppercase tracking-tight">PDF Order Parser</h2>
                        <p class="text-sm text-[#E42278] font-medium">Step 1: Configure Order Destination</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1 min-w-[140px]">
                            <label for="pdfParseBu" class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Business Unit</label>
                            <select id="pdfParseBu" class="glass-input !py-2 text-sm">
                                <option value="">Select...</option>
                                <option value="Health">Health</option>
                                <option value="Hygiene">Hygiene</option>
                                <option value="Nutri">Nutri</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-6">
                <div>
                    <label for="rawPdfInput" class="block text-lg font-bold text-[#0D111A] mb-2">Step 2: Paste Raw Data</label>
                    <p class="text-xs text-[#6B7280] mb-4">Copy all content from the PO PDF (Ctrl+A) and paste it below.</p>
                    <textarea id="rawPdfInput" rows="12" class="glass-input font-mono text-xs bg-[#FDFDFD] placeholder:italic" placeholder="Paste full PDF text here..."></textarea>
                </div>
                <div class="flex justify-end pt-4">
                    <button id="parsePdfTextBtn" class="btn-primary px-10 py-4 text-lg font-black shadow-xl hover:scale-105 transition-transform">
                        Parse & Review Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CUSTOMER SECTION -->
    <div id="adminCustomerSection" class="hidden drill-enter">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
            <div class="glass-card lg:col-span-1 h-fit p-6 border-t-4 border-t-[#E42278]">
                <h2 class="text-xl font-black text-[#0D111A] mb-6 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#E42278]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    Customer List
                </h2>
                <form id="addCustomerForm" class="flex gap-3 mb-6">
                    <input type="text" id="newCustomerName" placeholder="New Customer Name" class="glass-input !py-2 text-sm">
                    <button type="submit" class="btn-primary !py-2 px-6">Add</button>
                </form>
                <div class="overflow-y-auto max-h-[500px] -mx-2 px-2 suggestions-scroll">
                    <div id="customerManagementList" class="space-y-2"></div>
                </div>
            </div>

            <div class="glass-card lg:col-span-2 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-black text-[#0D111A] flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        Address Mappings
                    </h2>
                    <div class="flex gap-2">
                        <button onclick="document.getElementById('bulkSalesmanModal').classList.remove('hidden')" class="btn-primary !bg-blue-600 !py-2 px-4 shadow-sm text-sm">Bulk Salesmen</button>
                        <button id="addAddressCodeBtn" class="btn-secondary !py-2 px-6 shadow-sm">Add New Mapping</button>
                    </div>
                </div>
                <p class="text-xs text-[#6B7280] mb-4">Associate shipping addresses to internal SAP customer codes.</p>
                <div class="overflow-hidden rounded-xl border border-[rgba(13,17,26,0.08)]">
                    <div class="overflow-x-auto max-h-[600px] suggestions-scroll">
                        <table class="w-full text-sm text-left">
                           <thead class="bg-[#F9FAFB] sticky top-0 z-10">
                                <tr class="text-[10px] font-black uppercase text-[#6B7280] tracking-widest border-b border-[rgba(13,17,26,0.08)]">
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Address</th>
                                    <th class="px-6 py-4">Customer Code</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="addressCodeList" class="divide-y divide-[rgba(13,17,26,0.08)]"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> <div id="adminConverterSection" class="hidden drill-enter w-full h-[85vh] flex flex-col rounded-xl overflow-hidden shadow-lg border border-slate-200">
        <div class="bg-indigo-50 px-6 py-3 flex items-center justify-between border-b border-indigo-100">
            <span class="text-sm font-bold text-indigo-800">If the "Copy" button fails to copy the text, please open the converter securely:</span>
            <a href="https://impoj.pythonanywhere.com/" target="_blank" class="btn-primary !bg-indigo-600 hover:!bg-indigo-700 !py-1.5 !px-4 text-xs font-bold shadow-sm">
                Open in Secure Tab
            </a>
        </div>
        <iframe src="https://impoj.pythonanywhere.com/" allow="clipboard-read; clipboard-write" class="w-full h-full border-none bg-white flex-1"></iframe>
    </div>

    <div id="adminMergerSection" class="hidden drill-enter w-full h-screen">
        <iframe src="pages/merger.html" class="w-full h-full border-none bg-white"></iframe>
    </div>

<div id="adminExportSection" class="hidden drill-enter">
        <div class="max-w-4xl mx-auto space-y-8">
            <div class="glass-card p-8 border-t-4 border-t-emerald-500 shadow-emerald-50/50">
                <h2 class="text-3xl font-black text-[#0D111A] mb-2 tracking-tight">Extract sales data</h2>
                <p class="text-sm text-[#6B7280] mb-8">Generate CSV files for monthly performance audits.</p>
                
                <div class="bg-[#F9FAFB] p-6 rounded-2xl border border-[rgba(13,17,26,0.08)] mb-8">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Location</label>
                            <select id="exportLoc" class="glass-input !py-2 text-sm">
                                <option value="all">All Locations</option>
                                <option value="Davao">Davao</option>
                                <option value="Gensan">Gensan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Month</label>
                            <select id="exportMonth" class="glass-input !py-2 text-sm">
                                <option value="1">January</option><option value="2">February</option><option value="3">March</option>
                                <option value="4">April</option><option value="5">May</option><option value="6">June</option>
                                <option value="7">July</option><option value="8">August</option><option value="9">September</option>
                                <option value="10">October</option><option value="11">November</option><option value="12">December</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Year</label>
                            <select id="exportYear" class="glass-input !py-2 text-sm font-bold">
                                <?php 
                                $currentYear = date('Y');
                                for ($y = $currentYear; $y >= 2024; $y--): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="relative z-[100]" id="exportCustomerContainer">
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Customers</label>
                            <div id="exportCustomerBtn" class="glass-input !py-2 px-3 w-full text-sm cursor-pointer flex justify-between items-center bg-white">
                                <span id="exportCustomerLabel" class="truncate text-gray-700">All Customers</span>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                            <div id="exportCustomerDropdown" class="absolute right-0 z-50 w-64 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-72 flex flex-col overflow-hidden">
                                <div class="p-2 border-b border-gray-100 bg-gray-50">
                                    <input type="text" id="exportCustomerSearch" class="w-full text-xs p-2 border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-[#E42278]" placeholder="Search customers...">
                                </div>
                                <div id="exportCustomerList" class="p-2 overflow-y-auto space-y-1 custom-scrollbar text-sm flex-1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 mt-6">
                    <button id="exportCsvBtn" class="btn-primary !bg-gradient-to-r !from-emerald-600 !to-emerald-400 !shadow-emerald-100 flex justify-center items-center gap-2 py-3 px-6 w-full sm:w-auto text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Sales CSV
                    </button>
                    <button id="exportIssuesBtn" class="btn-primary !bg-gradient-to-r !from-red-600 !to-red-400 !shadow-red-100 flex justify-center items-center gap-2 py-3 px-6 w-full sm:w-auto text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Cancelled & Deleted
                    </button>
                    <button id="exportUnservedBtn" class="btn-primary !bg-gradient-to-r !from-orange-600 !to-orange-400 !shadow-orange-100 flex justify-center items-center gap-2 py-3 px-6 w-full sm:w-auto text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                        Unserved Items
                    </button>
                    <button id="exportFulfillableBtn" class="btn-primary !bg-gradient-to-r !from-blue-600 !to-blue-400 !shadow-blue-100 flex justify-center items-center gap-2 py-3 px-6 w-full sm:w-auto text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Fulfillable Items
                    </button>
                </div>
            </div>

            <div class="glass-card p-8 border-t-4 border-t-blue-500 shadow-blue-50/50">
                <h2 class="text-2xl font-black text-[#0D111A] mb-2 tracking-tight">Extract Customers data</h2>
                <p class="text-sm text-[#6B7280] mb-6">Export address mappings, customer codes, and assigned salesmen.</p>
                <button id="exportCustomersCsvBtn" class="btn-primary !bg-gradient-to-r !from-blue-600 !to-blue-400 !shadow-blue-100 flex justify-center items-center gap-3 py-3 px-8 w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Download Customers CSV
                </button>
            </div>

            <div class="glass-card p-8 border-t-4 border-t-purple-500 shadow-purple-50/50">
                <h2 class="text-2xl font-black text-[#0D111A] mb-2 tracking-tight">Extract Product list data</h2>
                <p class="text-sm text-[#6B7280] mb-6">Export full product catalog, SKUs, Barcodes, and pricing.</p>
                <button id="exportProductsCsvBtn" class="btn-primary !bg-gradient-to-r !from-purple-600 !to-purple-400 !shadow-purple-100 flex justify-center items-center gap-3 py-3 px-8 w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    Download Products CSV
                </button>
            </div>

            <div class="glass-card p-8 border-t-4 border-t-[#E42278] shadow-pink-50/50 mt-8">
                <h2 class="text-2xl font-black text-[#0D111A] mb-2 tracking-tight">Export to NP Import</h2>
                <p class="text-sm text-[#6B7280] mb-6">Generate chunked NP Import CSV files (Max 12 items per order per file).</p>
                
                <div class="bg-[#F9FAFB] p-6 rounded-2xl border border-[rgba(13,17,26,0.08)] mb-8 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Location</label>
                            <select id="npExportLoc" class="glass-input !py-2 !w-full text-xs">
                                <option value="all">All Locations</option>
                                <option value="Davao">Davao</option>
                                <option value="Gensan">Gensan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Status</label>
                            <select id="npExportStatus" class="glass-input !py-2 !w-full text-xs">
                                <option value="served" selected>Served</option>
                                <option value="fulfilled">Fulfilled</option>
                                <option value="all">All Statuses</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">BU</label>
                            <select id="npExportBu" class="glass-input !py-2 !w-full text-xs">
                                <option value="all">All BUs</option>
                                <option value="Health">Health</option>
                                <option value="Hygiene">Hygiene</option>
                                <option value="Nutri">Nutri</option>
                            </select>
                        </div>
                        <div class="relative z-[90]" id="npExportCustomerContainer">
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Customers</label>
                            <div id="npExportCustomerBtn" class="glass-input !py-2 px-3 w-full text-xs cursor-pointer flex justify-between items-center bg-white">
                                <span id="npExportCustomerLabel" class="truncate text-gray-700">All Customers</span>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                            <div id="npExportCustomerDropdown" class="absolute right-0 z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-72 flex flex-col overflow-hidden">
                                <div class="p-2 border-b border-gray-100 bg-gray-50">
                                    <input type="text" id="npExportCustomerSearch" class="w-full text-xs p-2 border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-[#E42278]" placeholder="Search customers...">
                                </div>
                                <div id="npExportCustomerList" class="p-2 overflow-y-auto space-y-1 custom-scrollbar text-xs flex-1">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Start Date & Time</label>
                            <input type="datetime-local" id="npExportStart" class="glass-input !py-2 !w-full text-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">End Date & Time</label>
                            <input type="datetime-local" id="npExportEnd" class="glass-input !py-2 !w-full text-xs">
                        </div>
                    </div>
                </div>

                <button id="exportNpImportBtn" class="btn-primary !bg-gradient-to-r !from-pink-600 !to-pink-400 !shadow-pink-100 py-3 px-8 w-full sm:w-auto">
                    Download NP Import Files
                </button>
            </div>
        </div>
    </div>

    <!-- TARGETS SECTION -->
    <div id="adminTargetsSection" class="hidden space-y-8 drill-enter">
        
        <div class="glass-card max-w-5xl mx-auto border-t-4 border-red-500 p-8 shadow-red-50/50">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <h2 class="text-2xl font-black text-[#0D111A]">Database Refresh / Import</h2>
                    <p class="text-sm text-[#6B7280]">Upload a .sql file to reset and update the live database from your local host.</p>
                </div>
                <form id="importDatabaseForm" class="flex items-end gap-4 bg-red-50/50 p-6 rounded-2xl border border-red-100 w-full md:w-auto">
                    <div class="flex-grow">
                        <label for="sqlFile" class="block text-[10px] font-bold text-red-600 uppercase tracking-widest mb-1">Select .sql file</label>
                        <input type="file" id="sqlFile" name="sql_file" accept=".sql" class="glass-input !py-2 !w-full text-sm font-bold bg-white" required>
                    </div>
                    <button type="submit" id="importDbBtn" class="btn-primary !bg-red-600 hover:!bg-red-700 !py-2 px-8">Refresh DB</button>
                </form>
            </div>
        </div>

        <div class="glass-card max-w-5xl mx-auto border-t-4 border-[#E42278] p-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <h2 class="text-2xl font-black text-[#0D111A]">Global Dashboard Date</h2>
                    <p class="text-sm text-[#6B7280]">Controls the default reporting month for all users.</p>
                </div>
                <div class="flex items-end gap-4 bg-pink-50/50 p-6 rounded-2xl border border-pink-100">
                    <div>
                        <label for="displayMonth" class="block text-[10px] font-bold text-[#E42278] uppercase tracking-widest mb-1">Month</label>
                        <select id="displayMonth" class="glass-input !py-2 !w-40 text-sm font-bold">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($m == date('n')) ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label for="displayYear" class="block text-[10px] font-bold text-[#E42278] uppercase tracking-widest mb-1">Year</label>
                        <select id="displayYear" class="glass-input !py-2 !w-28 text-sm font-bold">
                            <?php $currentYear = date('Y');
                            for ($y = $currentYear + 1; $y >= $currentYear - 3; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($y == $currentYear) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button id="setDisplayMonthBtn" class="btn-primary !py-2 px-8">Save Date</button>
                </div>
            </div>
        </div>

        <div class="glass-card max-w-5xl mx-auto p-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center border border-amber-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </div>
                <h2 class="text-3xl font-black text-[#0D111A]">Monthly Sales Targets</h2>
            </div>
            
            <form id="targetsForm">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <h3 class="font-black text-lg text-indigo-700 border-b border-indigo-50 pb-2 uppercase tracking-tight flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span> Davao Region
                        </h3>
                        <div class="space-y-4">
                            <?php foreach (['Health', 'Hygiene', 'Nutri'] as $bu): ?>
                                <div class="flex items-center gap-4 group">
                                    <label class="w-24 font-bold text-xs text-[#6B7280] uppercase tracking-widest"><?php echo $bu; ?></label>
                                    <div class="relative flex-grow">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-[#6B7280]">₱</span>
                                        <input type="number" id="target-davao-<?php echo strtolower($bu); ?>" data-location="Davao" data-bu="<?php echo $bu; ?>" 
                                            class="target-input glass-input !pl-8 text-right font-mono font-bold text-[#0D111A]" placeholder="0.00" step="any">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="font-black text-lg text-emerald-700 border-b border-emerald-50 pb-2 uppercase tracking-tight flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-emerald-500 rounded-full"></span> Gensan Region
                        </h3>
                        <div class="space-y-4">
                            <?php foreach (['Health', 'Hygiene', 'Nutri'] as $bu): ?>
                                <div class="flex items-center gap-4 group">
                                    <label class="w-24 font-bold text-xs text-[#6B7280] uppercase tracking-widest"><?php echo $bu; ?></label>
                                    <div class="relative flex-grow">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-[#6B7280]">₱</span>
                                        <input type="number" id="target-gensan-<?php echo strtolower($bu); ?>" data-location="Gensan" data-bu="<?php echo $bu; ?>" 
                                            class="target-input glass-input !pl-8 text-right font-mono font-bold text-[#0D111A]" placeholder="0.00" step="any">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex justify-end border-t border-[rgba(13,17,26,0.08)] pt-8">
                    <button type="submit" class="btn-primary !px-12 !py-4 text-lg font-black shadow-xl transform active:scale-95 transition-transform">
                        Save Regional Targets
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- NEW PRODUCT MANAGEMENT SECTION -->
    <div id="customerLvsSection" class="admin-section hidden">
        <div class="glass-card p-6 md:p-8 rounded-2xl mb-8">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-black text-[#0D111A]">Store LV Limits (Per BU)</h2>
                    <p class="text-sm text-gray-500">Set the maximum monthly LV quota for each customer by Business Unit.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-[rgba(13,17,26,0.08)]">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#F9FAFB] sticky top-0 z-10">
                        <tr class="text-[10px] font-black uppercase text-[#6B7280] tracking-widest border-b border-[rgba(13,17,26,0.08)]">
                            <th class="px-6 py-4">Customer Name</th>
                            <th class="px-6 py-4">Nutri Limit</th>
                            <th class="px-6 py-4">Health Limit</th>
                            <th class="px-6 py-4">Hygiene Limit</th>
                        </tr>
                    </thead>
                    <tbody id="customerLvsTableBody" class="divide-y divide-[rgba(13,17,26,0.08)] bg-white">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="productsSection" class="admin-section hidden">
        <div class="glass-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-l-4 border-l-purple-500">
            <div>
                <h2 class="text-2xl font-bold text-[#0D111A]">Product Management</h2>
                <p class="text-sm text-[#6B7280]">Create products, edit descriptions, and manage SKUs/Barcodes.</p>
            </div>
            <button onclick="openProductModal()" class="btn-primary !bg-purple-600 hover:!bg-purple-700 !shadow-purple-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Product
            </button>
        </div>

        <div class="glass-card p-0 overflow-hidden">
            <div class="p-4 border-b border-[rgba(13,17,26,0.08)] bg-gray-50/50">
                <input type="text" id="productSearchInput" placeholder="Search by description, SKU, or Barcode..." class="glass-input !w-full md:!w-96 text-sm">
            </div>
            <div class="overflow-x-auto max-h-[600px]">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-100 text-[#6B7280] uppercase text-xs font-bold sticky top-0 z-10">
                        <tr>
                            <th class="p-4 border-b">Description</th>
                            <th class="p-4 border-b">BU</th>
                            <th class="p-4 border-b">Linked Codes</th>
                            <th class="p-4 border-b text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminProductTableBody" class="divide-y divide-[rgba(13,17,26,0.08)] bg-white text-gray-700">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- PRODUCT MODAL -->
<div id="productModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[60] backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl transform transition-all scale-100">
        <h3 id="productModalTitle" class="text-xl font-black text-[#0D111A] mb-6">Add Product</h3>
        <form id="productForm" onsubmit="handleProductSubmit(event)" class="space-y-4">
            <input type="hidden" id="productId" name="id">
            
            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Description</label>
                <input type="text" id="productDescription" name="description" class="glass-input !w-full" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Business Unit</label>
                <select id="productBu" name="bu" class="glass-input !w-full">
                    <option value="Health">Health</option>
                    <option value="Hygiene">Hygiene</option>
                    <option value="Nutri">Nutri</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeProductModal()" class="px-4 py-2 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="btn-primary !bg-purple-600 hover:!bg-purple-700 !py-2 px-6">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- SKU MANAGER MODAL -->
<div id="skuManagerModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[60] backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-5xl h-[80vh] flex flex-col shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="text-xl font-black text-[#0D111A]">Manage Codes</h3>
                <p id="skuManagerProductName" class="text-sm font-bold text-purple-600 mt-1">Product Name</p>
            </div>
            <button onclick="closeSkuManager()" class="text-gray-400 hover:text-gray-600 bg-white p-2 rounded-full shadow-sm hover:shadow-md transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="flex flex-col md:flex-row h-full overflow-hidden">
            <!-- Left: Form -->
            <div class="w-full md:w-1/3 bg-gray-50 p-6 border-r border-gray-100 overflow-y-auto">
                <h4 class="text-xs font-black text-[#6B7280] uppercase tracking-widest mb-4">Add / Edit Code</h4>
                <form id="skuForm" onsubmit="handleSkuSubmit(event)" class="space-y-4">
                    <input type="hidden" id="skuId" name="id">
                    <input type="hidden" id="skuProductId" name="product_id">
                    
                    <div>
                        <label class="block text-[10px] font-bold text-[#6B7280] uppercase mb-1">Code (SKU/Barcode)</label>
                        <input type="text" id="skuCode" name="code" class="glass-input !w-full font-mono text-sm" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase mb-1">Type</label>
                            <select id="skuType" name="type" class="glass-input !w-full text-xs">
                                <option value="sku">SKU (Main)</option>
                                <option value="barcode">Barcode</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#6B7280] uppercase mb-1">Price</label>
                            <input type="number" step="0.01" id="skuPrice" name="sales_price" class="glass-input !w-full text-xs text-right" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-[#6B7280] uppercase mb-1">Pieces Per Case</label>
                        <input type="number" id="skuPcs" name="pieces_per_case" value="1" class="glass-input !w-full text-xs">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-[#E42278] uppercase mb-1">Davao Stock</label>
                            <input type="number" id="skuStockDavao" class="glass-input !w-full text-xs font-bold text-blue-700" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[#E42278] uppercase mb-1">Gensan Stock</label>
                            <input type="number" id="skuStockGensan" class="glass-input !w-full text-xs font-bold text-emerald-700" placeholder="0">
                        </div>
                    </div>

                    <div class="pt-4 flex gap-2">
                        <button type="submit" id="skuSubmitBtn" class="flex-1 btn-primary !bg-emerald-500 hover:!bg-emerald-600 !py-2 text-sm shadow-emerald-200">Add Code</button>
                        <button type="button" id="cancelSkuEditBtn" onclick="resetSkuForm()" class="hidden px-3 py-2 bg-gray-200 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-300">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Right: List -->
            <div class="flex-1 bg-white overflow-y-auto p-0">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-[#6B7280] text-[10px] font-bold uppercase sticky top-0">
                        <tr>
                            <th class="p-4 border-b">Code</th>
                            <th class="p-4 border-b">Type</th>
                            <th class="p-4 border-b">Price</th>
                            <th class="p-4 border-b">Stock (D/G)</th>
                            <th class="p-4 border-b text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="skuTableBody" class="divide-y divide-gray-50 text-sm text-gray-600">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="addressMappingModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[60] backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl transform transition-all scale-100">
        <h3 id="addressMappingModalTitle" class="text-xl font-black text-[#0D111A] mb-6">Add Address Mapping</h3>
        <form id="addressMappingForm" onsubmit="handleAddressMappingSubmit(event)" class="space-y-4">
            <input type="hidden" id="mappingId" name="id">
            
            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Customer</label>
                <select id="mappingCustomer" name="customer_id" class="glass-input !w-full" required>
                    <option value="">Select a customer...</option>
                    </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Address</label>
                <input type="text" id="mappingAddress" name="address" class="glass-input !w-full" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Customer Code (SAP)</label>
                <input type="text" id="mappingCode" name="customer_code" class="glass-input !w-full" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Salesman Name</label>
                <select id="mappingSalesmanName" name="salesman_name" class="glass-input !w-full">
                    <option value="">No Salesman</option>
                    <option value="GLENN BUCAG (KAS 101)" data-code="SSDB07">GLENN BUCAG (KAS 101)</option>
                    <option value="JOSE PEPITO ORTEGA (MAS 104) GS" data-code="SSGB01">JOSE PEPITO ORTEGA (MAS 104) GS</option>
                    <option value="NORMAN SAMODIO (MAS 105)" data-code="SSDB05">NORMAN SAMODIO (MAS 105)</option>
                    <option value="REDEMSON DULAY (MAS 102)" data-code="SSDB02">REDEMSON DULAY (MAS 102)</option>
                    <option value="RONALD LOPEZ (MAS 106)" data-code="SSDB06">RONALD LOPEZ (MAS 106)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Salesman Code</label>
                <input type="text" id="mappingSalesmanCode" name="salesman_code" class="glass-input !w-full" placeholder="e.g. S-001">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Warehouse Location</label>
                <select id="customerLocationInput" name="location" class="glass-input !w-full">
                    <option value="Davao">Davao</option>
                    <option value="Gensan">Gensan</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                
                <button type="button" onclick="closeAddressMappingModal()" class="px-4 py-2 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="btn-primary !bg-blue-600 hover:!bg-blue-700 !py-2 px-6">Save Mapping</button>
            </div>
        </form>
    </div>
</div>
<div id="admin-tab-translator" class="admin-tab-content hidden">
    <div id="invoiceTranslatorWrapper" class="max-w-[95rem] mx-auto space-y-6">
        
        <div class="bg-white/90 backdrop-blur-md border border-gray-100 p-6 rounded-2xl shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Invoice Translator</h1>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mt-1">Sure-Spot DS Data Parser</p>
                </div>
            </div>
        </div>

       <div class="flex flex-col gap-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:h-[340px]">
                
                <div class="lg:col-span-2 bg-white/90 backdrop-blur-md border border-gray-100 p-6 rounded-2xl shadow-sm flex flex-col h-full">
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center gap-3 mb-6 shrink-0">
                        <div class="w-1 h-5 bg-indigo-500 rounded-full"></div>
                        Input Data
                    </h3>

                    <div class="flex flex-col gap-6 flex-1 justify-center">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Loc</label>
                                <select id="transLocSelect" class="w-full text-sm p-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50/50 text-gray-700 font-bold">
                                    <option value="Davao">Davao</option>
                                    <option value="Gensan">Gensan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">BU</label>
                                <select id="transBuSelect" class="w-full text-sm p-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50/50 text-gray-700 font-bold">
                                    <option value="Health">Health</option>
                                    <option value="Hygiene">Hygiene</option>
                                    <option value="Nutri">Nutri</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Delivery</label>
                                <input type="date" id="transDeliveryDate" class="w-full text-sm p-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50/50 text-gray-700 font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Import File (.txt) <span class="text-indigo-500 normal-case tracking-normal ml-2 font-medium">- Parses Automatically</span></label>
                            <input type="file" id="transFileInput" accept=".txt" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <input type="hidden" id="transInvoiceData">
                        </div>

                        <div class="mt-auto pt-4 border-t border-gray-100">
                            <button onclick="saveTranslatorInvoice()" class="w-full sm:w-auto bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs py-3 px-8 rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Save Invoice to Database
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 bg-white/90 backdrop-blur-md border border-gray-100 p-5 rounded-2xl shadow-sm flex flex-col h-[300px] lg:h-full min-h-0">
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center gap-3 mb-4 shrink-0">
                        <div class="w-1 h-5 bg-emerald-500 rounded-full"></div>
                        Saved Invoices
                    </h3>
                    <div class="mb-3 relative shrink-0">
                        <input type="text" id="transSavedSearch" placeholder="Search invoices or BU..." onkeyup="searchSavedInvoices()" class="w-full text-xs font-bold p-2.5 pl-8 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-gray-50/50 text-gray-700">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <ul id="transSavedInvoices" class="space-y-2 flex-1 overflow-y-auto custom-scrollbar pr-2 min-h-0">
                    </ul>
                </div>
            </div>

            <div class="w-full bg-white/90 backdrop-blur-md border border-gray-100 p-6 rounded-2xl shadow-sm flex flex-col min-h-[500px]">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest flex items-center gap-3 mb-2">
                            <div class="w-1 h-5 bg-[#E42278] rounded-full"></div>
                            Parsed Results
                        </h3>
                        <div class="flex items-baseline gap-2">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Amount:</span>
                            <span id="transNetDisplay" class="text-2xl font-black text-emerald-600 tabular-nums">₱0.00</span>
                        </div>
                    </div>
                   <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <div class="w-full sm:w-64 relative">
                            <input type="text" id="transSearchInput" placeholder="Search code or description..." onkeyup="searchTranslatorTable()" class="w-full text-xs font-bold p-2.5 pl-8 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50/50 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button id="addInvoiceStocksBtn" onclick="addInvoiceToInventory()" class="hidden items-center justify-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs py-2 px-4 rounded-xl transition-colors shadow-sm whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add to Stocks
                        </button>
                        <button id="copyTransTableBtn" onclick="copyTranslatorTable()" class="flex items-center justify-center gap-1.5 bg-white border border-indigo-200 hover:bg-indigo-50 hover:border-indigo-300 text-indigo-600 font-bold text-xs py-2 px-4 rounded-xl transition-colors shadow-sm whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy Table
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-x-auto overflow-y-auto custom-scrollbar border border-gray-100 rounded-xl bg-white max-h-[600px]">
                    <table id="transInvoiceTable" class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-gray-50/90 backdrop-blur-sm sticky top-0 z-10 border-b border-gray-100">
                            <tr>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px]">Item Code</th>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px]">Description</th>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px] text-center">Unit</th>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px] text-right">Qty(U)</th>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px] text-right">Qty(C)</th>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px] text-right">Price</th>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px] text-right">Net</th>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px] text-right">VAT</th>
                                <th class="p-3 font-black text-gray-500 uppercase tracking-wider text-[10px] text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="transInvoiceTbody" class="divide-y divide-gray-50">
                            <tr><td colspan="9" class="p-8 text-center text-xs text-gray-400 font-medium italic">Paste data and click "Parse Invoice" to view items.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="bulkSalesmanModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-[#0D111A]">Bulk Update Salesmen</h3>
            <button onclick="document.getElementById('bulkSalesmanModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl">✕</button>
        </div>
        <div class="p-6 overflow-y-auto">
            <p class="text-sm text-gray-500 mb-2">Paste Excel data here. Expected columns: <b>Customer Code</b> | <b>Salesman Name</b> | <b>Salesman Code</b></p>
            <textarea id="bulkSalesmanData" rows="10" class="glass-input !w-full font-mono text-sm" placeholder="100456    John Doe    S-001&#10;100789    Jane Smith  S-002"></textarea>
        </div>
        <div class="p-6 bg-gray-50 flex justify-end">
            <button onclick="submitBulkSalesman()" class="btn-primary !bg-blue-600 hover:!bg-blue-700 !py-2 px-6">Update Salesmen</button>
        </div>
    </div>
</div>

<script src="js/admin.js?v=<?php echo time(); ?>"></script>