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
        <header class="mb-6">
            <h1 class="text-3xl font-bold text-slate-800">Product Purchase Order Search</h1>
            <p class="text-slate-500 mt-1">Search for any SKU, Barcode, or Description to find all POs containing that product.</p>
        </header>

        <main class="space-y-6">
            <div class="content-card">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="psMonthFilter" class="block text-sm font-medium text-slate-700">Month</label>
                        <select id="psMonthFilter" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                            <option value="all">All Months</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($m == date('m')) ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label for="psYearFilter" class="block text-sm font-medium text-slate-700">Year</label>
                        <select id="psYearFilter" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
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
                        <label for="psCustomerFilter" class="block text-sm font-medium text-slate-700">Customer</label>
                        <select id="psCustomerFilter" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                            <option value="all">All Customers</option>
                            </select>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <div class="relative">
                    <input type="text" id="product-search-input" placeholder="Enter SKU, Barcode, or Description..." class="w-full p-3 pl-10 border border-slate-300 rounded-md shadow-sm text-lg">
                    <svg class="absolute top-1/2 left-3 -translate-y-1/2 w-6 h-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                </div>
            </div>
            
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-5 rounded-lg shadow-md">
        <div>
            <h3 class="text-sm font-medium text-slate-500 uppercase">Total Served Qty</h3>
            <p id="psTotalServedQty" class="text-xl md:text-2xl xl:text-3xl font-bold text-slate-900 mt-1">0</p>
        </div>
    </div>
    <div class="bg-white p-5 rounded-lg shadow-md">
        <div>
            <h3 class="text-sm font-medium text-slate-500 uppercase">Total Served Amount</h3>
            <p id="psTotalServedAmount" class="text-xl md:text-2xl xl:text-3xl font-bold text-green-600 mt-1">₱0.00</p>
        </div>
    </div>
    <div class="bg-white p-5 rounded-lg shadow-md">
        <div>
            <h3 class="text-sm font-medium text-slate-500 uppercase">Total Unserved Amount</h3>
            <p id="psTotalUnservedAmount" class="text-xl md:text-2xl xl:text-3xl font-bold text-red-600 mt-1">₱0.00</p>
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
                                <th>Matched Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="search-results-body">
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-500">Please enter a search term above.</td>
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