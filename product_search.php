<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product PO Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="js/global.js" defer></script>
</head>
<body class="bg-slate-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto p-4 md:p-8">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#E42278] via-[#c91d68] to-[#9e1350] shadow-xl shadow-[#E42278]/25 p-6 md:p-8 mb-8">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="absolute -top-8 -right-8 w-36 h-36 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/10 blur-xl"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-pink-100 text-xs font-bold uppercase tracking-widest mb-0.5">Research Tool</p>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight leading-none">Product PO Search</h1>
                        <p class="text-pink-100 text-sm mt-1">Find all Purchase Orders by SKU, Barcode, or Description.</p>
                    </div>
                </div>
                <a href="index.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white font-bold text-sm rounded-xl border border-white/20 transition-all backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <main class="space-y-8">
           <!-- ★ TOTALS — moved ABOVE the search/filter card per request -->
           <div class="space-y-4 relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <h2 class="text-xs font-black text-slate-500 uppercase tracking-widest">Totals Summary</h2>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="glass-card p-4 border-l-4 border-l-cyan-500 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-cyan-50 rounded-md text-cyan-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total POs</h3>
                        </div>
                        <p id="psTotalPO" class="text-2xl font-black text-slate-800 pl-8">0</p>
                    </div>

                    <div class="glass-card p-4 border-l-4 border-l-teal-500 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-teal-50 rounded-md text-teal-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Served POs</h3>
                        </div>
                        <p id="psTotalServedPO" class="text-2xl font-black text-slate-800 pl-8">0</p>
                    </div>
                    
                    <div class="glass-card p-4 border-l-4 border-l-rose-400 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-rose-50 rounded-md text-rose-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Unserved POs</h3>
                        </div>
                        <p id="psTotalUnservedPO" class="text-2xl font-black text-slate-800 pl-8">0</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="glass-card p-4 border-l-4 border-l-indigo-500 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-indigo-50 rounded-md text-indigo-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-4m3 4v-6m3 6v-8"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Qty</h3>
                        </div>
                        <p id="psTotalQty" class="text-2xl font-black text-slate-800 pl-8">0</p>
                    </div>

                    <div class="glass-card p-4 border-l-4 border-l-blue-500 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-blue-50 rounded-md text-blue-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Served Qty</h3>
                        </div>
                        <p id="psTotalServedQty" class="text-2xl font-black text-slate-800 pl-8">0</p>
                    </div>
                    
                    <div class="glass-card p-4 border-l-4 border-l-orange-500 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-orange-50 rounded-md text-orange-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Unserved Qty</h3>
                        </div>
                        <p id="psTotalUnservedQty" class="text-2xl font-black text-slate-800 pl-8">0</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="glass-card p-4 border-l-4 border-l-purple-500 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-purple-50 rounded-md text-purple-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Amount</h3>
                        </div>
                        <p id="psTotalAmount" class="text-2xl font-black text-purple-700 pl-8">₱0.00</p>
                    </div>

                    <div class="glass-card p-4 border-l-4 border-l-emerald-500 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-emerald-50 rounded-md text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Served Amount</h3>
                        </div>
                        <p id="psTotalServedAmount" class="text-2xl font-black text-emerald-600 pl-8">₱0.00</p>
                    </div>

                    <div class="glass-card p-4 border-l-4 border-l-red-500 hover:-translate-y-1 transition-transform duration-200 flex flex-col justify-center">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1.5 bg-red-50 rounded-md text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Unserved Amount</h3>
                        </div>
                        <p id="psTotalUnservedAmount" class="text-2xl font-black text-red-600 pl-8">₱0.00</p>
                    </div>
                </div>
            </div>

            <div class="glass-card p-6 md:p-8 relative z-20 border-t-4 border-t-pink-500">
                
                <div class="relative mb-8">
                    <input type="text" id="product-search-input" placeholder="Search by Product Name, SKU, or Barcode..." class="w-full p-4 pl-14 bg-white border border-slate-200 rounded-2xl shadow-sm text-lg focus:ring-4 focus:ring-pink-500/20 focus:border-pink-500 transition-all font-medium text-slate-800 placeholder:text-slate-400" autocomplete="off">
                    <svg class="absolute top-1/2 left-5 -translate-y-1/2 w-6 h-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    
                    <div id="search-suggestions" class="absolute z-50 w-full bg-white/95 backdrop-blur-md mt-2 rounded-xl shadow-2xl border border-slate-100 hidden overflow-hidden max-h-60 overflow-y-auto">
                        </div>
                </div>

                <div class="flex flex-col xl:flex-row gap-6 items-end justify-between bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                    
                    <div class="w-full xl:w-auto flex flex-col gap-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider pl-1">Location</label>
                        <div class="inline-flex bg-slate-200/80 p-1.5 rounded-xl shadow-inner w-full sm:w-auto" id="psLocationToggle">
                            <button class="loc-btn flex-1 sm:flex-none px-6 py-2.5 rounded-lg text-sm font-bold transition-all active-loc bg-white shadow-sm text-pink-600" data-loc="all">All</button>
                            <button class="loc-btn flex-1 sm:flex-none px-6 py-2.5 rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-slate-800" data-loc="Davao">Davao</button>
                            <button class="loc-btn flex-1 sm:flex-none px-6 py-2.5 rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-slate-800" data-loc="Gensan">Gensan</button>
                        </div>
                    </div>

                    <div class="w-full xl:w-auto flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="psCustomerFilter" class="block text-xs font-bold text-slate-500 uppercase tracking-wider pl-1 mb-2">Customer</label>
                            <select id="psCustomerFilter" class="w-full rounded-xl border-slate-200 bg-white focus:ring-pink-500 focus:border-pink-500 shadow-sm text-sm py-3 cursor-pointer">
                                <option value="all">All Customers</option>
                            </select>
                        </div>
                        <div class="relative">
                            <label for="psAddressFilter" class="block text-xs font-bold text-slate-500 uppercase tracking-wider pl-1 mb-2">Address</label>
                            <input type="text" id="psAddressFilter" placeholder="Search address..." class="w-full rounded-xl border-slate-200 bg-white focus:ring-pink-500 focus:border-pink-500 shadow-sm text-sm py-3 px-4 placeholder:text-slate-400" autocomplete="off">
                            
                            <div id="address-suggestions" class="absolute z-50 w-full bg-white mt-1 rounded-xl shadow-xl border border-slate-100 hidden overflow-hidden max-h-60 overflow-y-auto">
                                </div>
                        </div>
                        <div>
                            <label for="psMonthFilter" class="block text-xs font-bold text-slate-500 uppercase tracking-wider pl-1 mb-2">Month</label>
                            <select id="psMonthFilter" class="w-full rounded-xl border-slate-200 bg-white focus:ring-pink-500 focus:border-pink-500 shadow-sm text-sm py-3 cursor-pointer">
                                <option value="all">All Months</option>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m == date('m')) ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label for="psYearFilter" class="block text-xs font-bold text-slate-500 uppercase tracking-wider pl-1 mb-2">Year</label>
                            <select id="psYearFilter" class="w-full rounded-xl border-slate-200 bg-white focus:ring-pink-500 focus:border-pink-500 shadow-sm text-sm py-3 cursor-pointer">
                                <?php 
                                $currentYear = date('Y');
                                for ($y = $currentYear + 1; $y >= $currentYear - 2; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y == $currentYear) ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Search Results</h2>
                <div class="overflow-x-auto">
                    <table class="data-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>Order Date</th>
                                <th>Customer / PO</th>
                                <th>Location</th>
                                <th>Matched Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="search-results-body">
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-500">Please enter a search term above.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script type="module" src="js/product_search.js" defer></script>
</body>
</html>