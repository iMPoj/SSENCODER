<?php
session_start();
require 'db_connect.php';

$user_role = $_SESSION['role'] ?? 'viewer';
$raw_bulk = $_GET['bulk_ids'] ?? '';
$raw_id = $_GET['id'] ?? '';
$orderIds = [];
if (!empty($raw_bulk)) {
    $orderIds = array_filter(array_map('intval', explode(',', $raw_bulk)));
} elseif (!empty($raw_id)) {
    $orderIds[] = intval($raw_id);
}

if (empty($orderIds)) {
    header('Location: index.php');
    exit;
}

$is_bulk_print = count($orderIds) > 1;

// Fetch all customers for the edit dropdown ONCE
$customersStmt = $pdo->query("SELECT id, name FROM customers ORDER BY name ASC");
$all_customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);

$all_orders_data = [];
$global_products_by_sku = []; // Merged catalog for JS if needed

foreach ($orderIds as $currentOrderId) {
        // Generate Current URL for QR Code (Dynamically handles subfolders)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $script_path = str_replace(' ', '%20', $_SERVER['SCRIPT_NAME']);
        $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $script_path . "?id=" . $currentOrderId;
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($currentUrl);
        
    // Fetch the main order details
    $orderStmt = $pdo->prepare("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
    $orderStmt->execute([$currentOrderId]);
    $order = $orderStmt->fetch();

    if (!$order) continue; // Skip if deleted or missing

    // --- NEW: Auto-sync customer code and salesman details from latest mapping ---
    if (!empty($order['customer_address'])) {
        $syncStmt = $pdo->prepare("SELECT customer_code, salesman_name, salesman_code FROM customer_address_codes WHERE address = ?");
        $syncStmt->execute([$order['customer_address']]);
        $latestMapping = $syncStmt->fetch(PDO::FETCH_ASSOC);

        if ($latestMapping) {
            if (($order['customer_code'] ?? '') !== ($latestMapping['customer_code'] ?? '') ||
                ($order['salesman_name'] ?? '') !== ($latestMapping['salesman_name'] ?? '') ||
                ($order['salesman_code'] ?? '') !== ($latestMapping['salesman_code'] ?? '')) {
                
                $updateSync = $pdo->prepare("UPDATE orders SET customer_code = ?, salesman_name = ?, salesman_code = ? WHERE id = ?");
                $updateSync->execute([$latestMapping['customer_code'], $latestMapping['salesman_name'], $latestMapping['salesman_code'], $currentOrderId]);
                
                $order['customer_code'] = $latestMapping['customer_code'];
                $order['salesman_name'] = $latestMapping['salesman_name'];
                $order['salesman_code'] = $latestMapping['salesman_code'];
            }
        }
    }

    // Fetch all items for this order
    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
    $itemStmt->execute([$currentOrderId]);
    $items = $itemStmt->fetchAll();

    $so_numbers = json_decode($order['so_number'] ?? '[]', true);
    if (!is_array($so_numbers)) $so_numbers = [];

    $page_remarks = json_decode($order['remarks'] ?? '[]', true);
    if (!is_array($page_remarks)) $page_remarks = [];

    $bu_map = ['Nutri' => 'ifcn', 'Health' => 'rw', 'Hygiene' => 'hygiene'];
    $bu_code = $bu_map[$order['bu']] ?? 'N/A';

    // --- GENERATE CUSTOM FILENAME ---
    $filename_parts = ['PO ' . $order['po_number'], $order['customer_address'] ?? '', $order['customer_name'] ?? '', $order['location'] ?? ''];
    $clean_parts = [];
    foreach ($filename_parts as $part) {
        $clean = trim($part);
        if ($clean !== '') {
            $clean = preg_replace('/[^a-zA-Z0-9\-]/', '_', $clean);
            $clean = preg_replace('/_+/', '_', $clean);
            $clean_parts[] = trim($clean, '_');
        }
    }
    $export_filename = implode('_', $clean_parts);

    // --- PRODUCT FAMILY & SKU LOOKUP ---
    $products_by_sku = [];
    if (!empty($items)) {
        $itemSkus = array_values(array_unique(array_column($items, 'sku')));
        if (!empty($itemSkus)) {
            $placeholders = implode(',', array_fill(0, count($itemSkus), '?'));
            $sql = "SELECT DISTINCT product_id FROM product_codes WHERE code IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($itemSkus);
            $productIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($productIds)) {
                $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                $sql = "SELECT p.id, p.description, p.bu, pc.code, pc.type, pc.sales_price FROM products p JOIN product_codes pc ON p.id = pc.product_id WHERE p.id IN ($placeholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($productIds);
                $related_products_data = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

                foreach ($related_products_data as $productId => $codes) {
                    $product_info = ['description' => $codes[0]['description'], 'bu' => $codes[0]['bu'], 'codes' => $codes];
                    foreach ($product_info['codes'] as $code) {
                        $products_by_sku[$code['code']] = [
                            'productId' => $productId,
                            'description' => $product_info['description'],
                            'bu' => $product_info['bu'],
                            'sales_price' => (float)$code['sales_price'],
                            'allSkus' => $product_info['codes']
                        ];
                        $global_products_by_sku[$code['code']] = $products_by_sku[$code['code']];
                    }
                }
            }
        }
    }

    // --- CALCULATE GLOBAL TOTALS ---
    $totalServedValue = 0; $totalUnservedValue = 0; $totalUnservedCount = 0;
    $totalPristineValue = 0; $totalServedQty = 0; $totalItemQty = 0; $totalGrossServedValue = 0;

    foreach ($items as $item) {
        $totalItemQty += $item['quantity'];
        $unit_sales_price = $products_by_sku[$item['sku']]['sales_price'] ?? 0;
        $totalPristineValue += $unit_sales_price * $item['quantity'];

        if ($item['status'] === 'served' || $item['status'] === 'fulfilled') {
            $totalServedValue += $item['price'];
            $totalServedQty += $item['quantity'];
            $totalGrossServedValue += $unit_sales_price * $item['quantity'];
        } else {
            $totalUnservedValue += $item['price'];
            $totalUnservedCount++;
        }
    }
    $totalPoValue = $totalServedValue + $totalUnservedValue;
    $qtyFillRate = ($totalItemQty > 0) ? ($totalServedQty / $totalItemQty) * 100 : 0;
    $totalVatablePurchases = ($totalPristineValue > 0) ? $totalPristineValue / 1.12 : 0;
    $totalVatableServed = ($totalGrossServedValue > 0) ? $totalGrossServedValue / 1.12 : 0;

    $original_pages = array_chunk($items, 12);
    $standard_pages = []; $all_fulfilled_items = [];

    foreach ($original_pages as $page_index => $page_items) {
        $current_standard_page = [];
        foreach ($page_items as $item) {
            if ($item['status'] === 'fulfilled') {
                $all_fulfilled_items[] = $item;
            } else {
                $current_standard_page[] = $item;
            }
        }
        $standard_pages[] = $current_standard_page; 
    }

    $fulfilled_pages = !empty($all_fulfilled_items) ? array_chunk($all_fulfilled_items, 12) : [];
    $item_pages = array_merge($standard_pages, $fulfilled_pages);

    // Save everything for this order into the master array
    $all_orders_data[] = [
        'id' => $currentOrderId,
        'order' => $order,
        'qrCodeUrl' => $qrCodeUrl,
        'bu_code' => $bu_code,
        'export_filename' => $export_filename,
        'products_by_sku' => $products_by_sku,
        'totalPoValue' => $totalPoValue,
        'totalVatablePurchases' => $totalVatablePurchases,
        'totalServedValue' => $totalServedValue,
        'totalVatableServed' => $totalVatableServed,
        'totalUnservedValue' => $totalUnservedValue,
        'totalUnservedCount' => $totalUnservedCount,
        'qtyFillRate' => $qtyFillRate,
        'item_pages' => $item_pages,
        'standard_page_count' => count($standard_pages),
        'fulfilled_pages' => $fulfilled_pages,
        'so_numbers' => $so_numbers,
        'page_remarks' => $page_remarks
    ];
}

$first_order = $all_orders_data[0] ?? null;
$page_title = $is_bulk_print ? "Bulk Print (" . count($all_orders_data) . " Orders)" : ($first_order['export_filename'] ?? 'Order');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    

    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .summary-card { transition: all 0.2s ease-in-out; }
        .summary-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        
        /* Utility for text selection */
        .select-all { user-select: all; -webkit-user-select: all; }
        .select-none { user-select: none; -webkit-user-select: none; }

        /* Print Specific Styles */
        @media print {
            body { background-color: white; font-size: 12px; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .page-container { 
                box-shadow: none !important; 
                border: none !important; 
                margin: 0 !important; 
                padding: 0 !important;
                page-break-after: always;
            }
            .bg-slate-50 { background-color: white !important; }
            /* Hide standard pages when 'Print Fulfilled' is clicked */
            body.print-fulfilled-only .standard-page {
                display: none !important;
            }
            
            /* Hide the performance overview on fulfilled prints to save paper */
            body.print-fulfilled-only .performance-overview {
                display: none !important;
            }
            
            /* Visual marking for unserved items in print */
            tr[data-status="unserved"] td {
                color: #ef4444 !important; /* Tailwind red-500 */
                font-style: italic;
                background-color: #fef2f2 !important; /* Very light red */
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tr[data-status="unserved"] td[data-label="Description"] span.item-description-text {
                text-decoration: line-through;
                color: #ef4444 !important;
            }
            
            /* Ensure inputs look like text */
            input, select, textarea { 
                border: none !important; 
                background: transparent !important; 
                padding: 0 !important; 
                box-shadow: none !important;
                resize: none;
            }
            input, select { text-align: right; font-weight: bold; }
            .quantity-input { text-align: center; }
            
            /* Hide URL headers/footers */
        @page { margin: 0.5cm; }
        tr.print-highlight td {
        background-color: #fef08a !important; /* Tailwind yellow-200 */
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

        }
        
        .print-only { display: none; }
        
        /* Smooth Fade-in Animation */
        @keyframes subtleFade {
            0% { opacity: 0; transform: translateY(5px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: subtleFade 0.4s ease-out forwards;
        }
    </style>
</head>
<body class="text-slate-800">
    <div id="loading-overlay" class="modal-backdrop" style="display: none; z-index: 9999;">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-white"></div>
    </div>
    
    <div class="container mx-auto p-4 md:p-6 max-w-7xl min-w-[1024px] overflow-x-auto">
        
        <header class="fixed top-0 left-0 w-full z-[100] bg-white/90 backdrop-blur-md py-3 px-4 md:px-8 flex justify-between items-center no-print border-b border-slate-300 shadow-md transition-all">
            
            <?php if ($user_role !== 'viewer'): ?>
                <a href="index.php" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                    <span class="font-medium">Back</span>
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <div class="flex gap-2 items-center flex-wrap">

                <!-- ★ PO/SO QUICK SWITCH: Type a PO# or SO# to jump to that order -->
                <div class="relative" id="quickSwitchWrapper">
                    <div class="flex items-center gap-2 bg-white pl-3 pr-1 py-1 rounded-lg border border-indigo-200 shadow-sm focus-within:ring-2 focus-within:ring-indigo-500/30 focus-within:border-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" id="quickSwitchInput" placeholder="Jump to PO# or SO#..." class="w-44 md:w-52 text-sm font-medium text-slate-800 border-none focus:ring-0 p-1 bg-transparent placeholder-slate-400" autocomplete="off">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1.5 py-0.5 bg-slate-100 rounded border border-slate-200 select-none hidden md:inline">↵</span>
                    </div>
                    <div id="quickSwitchResults" class="hidden absolute right-0 mt-1 w-80 max-h-96 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-2xl z-[200]"></div>
                </div>

                <?php if ($user_role !== 'viewer'): ?>
                    <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <label for="orderDiscountInput" class="text-xs font-bold text-slate-500 uppercase">Discount</label>
                        <input type="number" id="orderDiscountInput" step="0.1" value="<?php echo htmlspecialchars($first_order['order']['discount_percentage'] ?? '0'); ?>" class="w-16 text-right font-bold text-slate-800 border-none focus:ring-0 p-0 text-sm bg-transparent" disabled>
                        <span class="text-sm font-bold text-slate-400">%</span>
                    </div>
                <?php endif; ?>
                
                <button onclick="window.print()" class="btn bg-slate-800 hover:bg-slate-900 text-white text-sm py-2 px-4 shadow-sm flex items-center gap-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Print PO
                </button>

                <button id="printFulfilledBtn" class="btn bg-yellow-500 hover:bg-yellow-600 text-white text-sm py-2 px-4 shadow-sm flex items-center gap-2 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Print Fulfilled
                </button>

                <?php if (!$is_bulk_print): ?>
                    <?php if ($user_role === 'admin' || $user_role === 'encoder'): ?>
                        <button id="editSoBtn" class="btn bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm py-2 px-4 shadow-sm rounded-lg">Edit SO</button>
                        
                        <button id="editOrderBtn" class="btn bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm py-2 px-4 shadow-sm rounded-lg">Full Edit</button>
                        
                        <button id="repairDescriptionsBtn" class="hidden btn bg-amber-500 hover:bg-amber-600 text-white text-sm py-2 px-3 rounded-lg shadow-sm" title="Fix Text Encoding">Repair Txt</button>
                        <button id="recalculatePricesBtn" class="hidden btn bg-blue-500 hover:bg-blue-600 text-white text-sm py-2 px-3 rounded-lg shadow-sm" title="Recalculate Totals">Recalc</button>
                        <button id="saveChangesBtn" class="hidden btn bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 rounded-lg shadow-sm">Save</button>
                        <button id="cancelChangesBtn" class="hidden btn bg-white text-red-600 border border-red-200 hover:bg-red-50 text-sm py-2 px-4 rounded-lg shadow-sm">Cancel</button>
                    <?php endif; ?>
                    <?php if ($user_role === 'admin'): ?>
                        <button id="deleteOrderBtn" class="btn bg-red-50 text-red-600 hover:bg-red-100 border border-red-100 text-sm py-2 px-3 rounded-lg" title="Delete Order">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </header>

        <div id="printableArea" class="mt-20 print:mt-0 animate-fade-in">
        <?php foreach ($all_orders_data as $order_data): 
            extract($order_data); 
            $global_item_counter = 1;
        ?>
            <div class="order-section">
            <?php foreach ($item_pages as $page_index => $page_items): 
                // If the current page index is greater than or equal to the number of standard pages, it's a fulfilled page
                $is_fulfilled_page = ($page_index >= $standard_page_count && count($fulfilled_pages) > 0);
            ?>

            <?php if ($page_index === 0): ?>
                <div class="mb-8 no-print performance-overview" data-html2canvas-ignore="true">
                    <h2 class="text-lg font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <span class="w-1 h-6 bg-indigo-600 rounded-full"></span>
                        Performance Overview
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 summary-card">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total PO Amount</p>
                            <p class="text-base font-black text-slate-800 truncate"><?php echo '₱' . number_format($totalPoValue, 0); ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 summary-card">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Vatable Purchase</p>
                            <p class="text-base font-bold text-slate-600 truncate"><?php echo '₱' . number_format($totalVatablePurchases, 0); ?></p>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-lg shadow-sm border border-emerald-100 summary-card">
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Served Value</p>
                            <p class="text-base font-black text-emerald-700 truncate"><?php echo '₱' . number_format($totalServedValue, 0); ?></p>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-lg shadow-sm border border-emerald-100 summary-card">
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Vatable Served</p>
                            <p class="text-base font-black text-emerald-700 truncate"><?php echo '₱' . number_format($totalVatableServed, 0); ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 summary-card">
                            <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mb-1">Unserved Value</p>
                            <p class="text-base font-bold text-red-600 truncate"><?php echo '₱' . number_format($totalUnservedValue, 0); ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-200 summary-card">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Missed Items</p>
                            <p class="text-base font-bold text-slate-700 truncate"><?php echo $totalUnservedCount; ?> <span class="text-[10px] font-normal text-slate-400">SKUs</span></p>
                        </div>
                        <div class="bg-indigo-50 p-3 rounded-lg shadow-sm border border-indigo-100 summary-card">
                            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider mb-1">Qty Fill Rate</p>
                            <p class="text-base font-black text-indigo-700 truncate"><?php echo number_format($qtyFillRate, 1); ?>%</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-lg border border-slate-200 mb-8 page-container overflow-hidden relative <?php echo $is_fulfilled_page ? 'fulfilled-page' : 'standard-page'; ?>">
                
                <div class="p-6 md:p-8 border-b border-slate-100">
                    <div class="flex flex-row justify-between gap-6">
                        
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 border border-slate-200 rounded-lg p-1 bg-white">
                                <img src="<?php echo $qrCodeUrl; ?>" alt="PO Link QR" class="w-20 h-20 md:w-24 md:h-24 object-contain opacity-90">
                            </div>

                            <div class="flex flex-col justify-center h-full pt-1">
                               <h1 class="text-2xl font-bold text-slate-800 leading-tight mb-1 flex items-center gap-2 flex-wrap">
    <span class="view-mode-element"><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown Customer'); ?></span>
    <select id="orderCustomerInput" class="edit-mode-element hidden text-lg border border-slate-300 rounded px-2 py-1 shadow-sm font-sans w-64 max-w-full">
        <?php foreach ($all_customers as $cust): ?>
            <option value="<?php echo $cust['id']; ?>" <?php echo ($cust['id'] == $order['customer_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cust['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if ($is_fulfilled_page): ?>
        <span class="px-2 py-0.5 text-xs font-bold uppercase tracking-wider bg-yellow-100 text-yellow-800 rounded border border-yellow-300 shadow-sm">Fulfilled Items</span>
    <?php elseif ($order['status'] === 'cancelled'): ?>
        <span class="px-2 py-0.5 text-xs font-bold uppercase tracking-wider bg-red-100 text-red-800 rounded border border-red-300 shadow-sm inline-flex items-center gap-1 no-print" data-html2canvas-ignore="true">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            Cancelled
        </span>
    <?php elseif ($order['status'] === 'deleted'): ?>
        <span class="px-2 py-0.5 text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-800 rounded border border-gray-300 shadow-sm inline-flex items-center gap-1 no-print" data-html2canvas-ignore="true">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            Deleted
        </span>
    <?php else:
        // ★ Invoiced/Open Badge for this PO page
        $page_so_value = trim($so_numbers[$page_index] ?? '');
        $page_is_invoiced = ($page_so_value !== '' && strtolower($page_so_value) !== 'n/a');
    ?>
        <?php if ($page_is_invoiced): ?>
            <span class="px-2 py-0.5 text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 rounded border border-emerald-300 shadow-sm inline-flex items-center gap-1 no-print" data-html2canvas-ignore="true">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                Encoded
            </span>
        <?php else: ?>
            <span class="px-2 py-0.5 text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800 rounded border border-amber-300 shadow-sm inline-flex items-center gap-1 no-print" data-html2canvas-ignore="true">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                Open
            </span>
        <?php endif; ?>
    <?php endif; ?>
</h1>

<?php if (($order['status'] === 'cancelled' || $order['status'] === 'deleted') && !empty($order['cancel_reason'])): ?>
    <div class="mt-1 mb-2 no-print" data-html2canvas-ignore="true">
        <span class="text-xs font-bold text-red-600 bg-red-50 border border-red-100 px-2 py-1 rounded">Reason: <?php echo htmlspecialchars($order['cancel_reason']); ?></span>
    </div>
<?php endif; ?>
                                
                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 font-mono mb-2">
                                    <div class="bg-slate-50 px-2 py-0.5 rounded border border-slate-200 flex items-center gap-1">
                                        <span class="text-slate-400 select-none">Code:</span>
                                        <span class="text-slate-600 select-all font-medium"><?php echo htmlspecialchars($order['customer_code'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="bg-slate-50 px-2 py-0.5 rounded border border-slate-200 flex items-center gap-1">
                                        <span class="text-slate-400 select-none">BU:</span>
                                        <span class="text-slate-600 uppercase select-all font-medium"><?php echo htmlspecialchars($bu_code); ?></span>
                                    </div>
                                </div>
                                
                                <div class="flex items-start gap-1.5 text-sm text-slate-600 max-w-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    
                                    <div class="flex-1">
                                        <span class="view-mode-element leading-snug"><?php echo htmlspecialchars($order['customer_address'] ?? 'No Address Provided'); ?></span>
                                        <textarea id="orderAddressInput" rows="2" class="edit-mode-element hidden w-full text-xs border border-slate-300 rounded px-2 py-1 shadow-sm font-sans"><?php echo htmlspecialchars($order['customer_address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right flex flex-col items-end justify-start">
                            <div class="mb-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block mb-0.5">Purchase Order</span>
                                <div class="view-mode-element">
                                    <p class="text-3xl font-mono font-bold text-slate-800 tracking-tight" id="currentPoDisplay">
                                        <?php echo htmlspecialchars($order['po_number'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                                <div class="edit-mode-element hidden">
                                    <input type="text" id="orderPoInput" value="<?php echo htmlspecialchars($order['po_number'] ?? ''); ?>" class="text-xl font-mono font-bold border border-slate-300 rounded shadow-sm w-48 text-right px-2 py-1">
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4 text-sm text-slate-600 mb-2 no-print" data-html2canvas-ignore="true">
                                <div class="flex items-center gap-1 group relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span class="view-mode-element" id="currentDateDisplay"><?php echo date("M j, Y", strtotime($order['order_date'])); ?></span>
                                    <input type="date" id="orderDateInput" value="<?php echo date('Y-m-d', strtotime($order['order_date'])); ?>" class="edit-mode-element hidden text-xs border border-slate-300 rounded px-2 py-1">
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="view-mode-element"><?php echo htmlspecialchars($order['location']); ?></span>
                                    <select id="orderLocationInput" class="edit-mode-element hidden text-xs border border-slate-300 rounded px-2 py-1">
                                        <option value="Davao" <?php echo $order['location'] === 'Davao' ? 'selected' : ''; ?>>Davao</option>
                                        <option value="Gensan" <?php echo $order['location'] === 'Gensan' ? 'selected' : ''; ?>>Gensan</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-1 flex flex-col items-end w-full md:w-auto">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block mb-0.5">SO Number</span>
                                <span class="soNumberText block font-mono text-sm font-bold bg-slate-50 border border-slate-200 rounded px-2 py-1 text-right min-w-[150px] shadow-sm"><?php echo htmlspecialchars($so_numbers[$page_index] ?? 'N/A'); ?></span>
                                <textarea class="soNumberInput hidden mt-1 block rounded-md border-slate-300 shadow-sm text-sm font-mono text-right min-w-[150px] px-2 py-1" data-page-index="<?php echo $page_index; ?>" rows="1"><?php echo htmlspecialchars($so_numbers[$page_index] ?? ''); ?></textarea>
                            </div>

                            <div class="mt-2 flex flex-col items-end w-full md:w-auto">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block mb-0.5">Remarks</span>
                                <span class="remarksText block text-xs font-medium bg-slate-50 border border-slate-200 rounded px-2 py-1 text-right min-w-[150px] max-w-[250px] shadow-sm whitespace-pre-wrap break-words"><?php echo htmlspecialchars($page_remarks[$page_index] ?? 'N/A'); ?></span>
                                <textarea class="remarksInput hidden mt-1 block rounded-md border-slate-300 shadow-sm text-xs text-right min-w-[150px] max-w-[250px] px-2 py-1 resize-none overflow-hidden" data-page-index="<?php echo $page_index; ?>" rows="2" placeholder="Add remarks..."><?php echo htmlspecialchars($page_remarks[$page_index] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                  <table class="min-w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider w-10">#</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider w-1/3">Description / SKU</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider">Qty</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Price</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider no-print" data-html2canvas-ignore="true">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($page_items as $item_index => $item): 
    $pristine_unit_price = $products_by_sku[$item['sku']]['sales_price'] ?? 0;

                            // --- STOCK CHECKER LOGIC ---
                            $stock_alert_html = '';
                            if ($item['status'] === 'unserved') {
                                $related_skus = [];
                                if (isset($products_by_sku[$item['sku']]['allSkus'])) {
                                    foreach ($products_by_sku[$item['sku']]['allSkus'] as $codeData) {
                                        if ($codeData['type'] === 'sku') $related_skus[] = $codeData['code'];
                                    }
                                } else {
                                    $related_skus[] = $item['sku'];
                                }

                                if (!empty($related_skus)) {
                                    $placeholders = implode(',', array_fill(0, count($related_skus), '?'));
                                    $sql = "SELECT product_code, stock FROM inventory_levels WHERE product_code IN ($placeholders) AND location = ? AND stock >= ? ORDER BY stock DESC LIMIT 1";
                                    $params = array_merge($related_skus, [$order['location'], $item['quantity']]);
                                    $stockCheckStmt = $pdo->prepare($sql);
                                    $stockCheckStmt->execute($params);
                                    $stock_result = $stockCheckStmt->fetch();

                                    if ($stock_result) {
                                        $avail_sku = $stock_result['product_code'];
                                        $sku_hint = ($avail_sku != $item['sku']) ? " (Use: <strong>$avail_sku</strong>)" : "";
                                        $stock_alert_html = '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200 animate-pulse whitespace-nowrap shadow-sm ml-2 no-print" data-html2canvas-ignore="true"><span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>Stock Ready' . $sku_hint . '</span>';
                                    }
                                }
                            }
                        ?>
                            <tr class="item-row text-sm transition-colors <?php echo $item['status'] === 'fulfilled' ? 'bg-yellow-50 print-highlight' : 'hover:bg-slate-50'; ?>" 
                                data-item-id="<?php echo $item['id']; ?>" 
                                data-original-sku="<?php echo htmlspecialchars($item['sku']); ?>" 
                                data-original-status="<?php echo htmlspecialchars($item['status']); ?>"
                                data-status="<?php echo htmlspecialchars($item['status']); ?>"
                                data-pdiff="0">

                                <td class="px-2 py-3 text-center font-mono text-xs text-slate-400"><?php echo $global_item_counter++; ?></td>
                                
                                <td data-label="Description" class="px-4 py-3">
                                    <div class="flex justify-between items-start gap-3 relative">
                                        <div class="min-w-0 flex-grow">
                                            <div class="view-mode-element">
                                                <span class="item-description-text block font-semibold text-slate-700"><?php echo htmlspecialchars($item['description']); ?></span>
                                                <p class="sku-text-display mt-0.5 font-mono text-xs text-slate-400"><?php echo htmlspecialchars($item['sku']); ?></p>
                                            </div>
                                            <div class="edit-mode-element hidden w-full relative">
                                                <input type="text" class="item-search-input w-full rounded-md border-slate-300 shadow-sm text-xs py-1.5" value="<?php echo htmlspecialchars($item['description']); ?>" placeholder="Search item...">
                                                <div class="item-suggestions absolute z-50 w-full bg-white border border-slate-300 rounded-lg mt-1 max-h-60 overflow-y-auto hidden shadow-xl"></div>
                                                <select class="sku-select mt-1 block w-full rounded-md border-slate-300 shadow-sm text-xs py-1.5 bg-slate-50"></select>
                                            </div>
                                        </div>
                                        <?php if ($stock_alert_html): ?>
                                            <div class="flex-shrink-0 pt-0.5"><?php echo $stock_alert_html; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <td data-label="Unit Price" class="px-4 py-3 font-mono text-xs text-slate-600 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="pristine-price-display font-medium"><?php echo '₱' . number_format($pristine_unit_price, 2); ?></span>
                                        <button type="button" class="pdiff-btn edit-mode-element hidden text-blue-500 hover:text-blue-700 bg-blue-50 px-2 py-0.5 rounded text-[10px] font-bold uppercase" title="Price Diff per PC">
                                            PDiff
                                        </button>
                                    </div>
                                </td>
                                
                                <td data-label="Quantity" class="px-4 py-3 text-center">
                                    <input type="number" value="<?php echo htmlspecialchars($item['quantity']); ?>" class="quantity-input w-16 rounded-md border-slate-200 shadow-sm text-xs py-1 text-center font-bold text-slate-700 bg-white" disabled>
                                </td>
                                
                                <td data-label="Total Price" class="px-4 py-3 text-right">
                                    <input type="number" step="0.01" value="<?php echo htmlspecialchars($item['price']); ?>" class="price-input w-24 rounded-md border-slate-200 shadow-sm text-xs py-1 text-right font-mono font-medium text-slate-800 bg-slate-50" readonly>
                                </td>
                                
                                <td data-label="Status" class="px-4 py-3 text-center no-print" data-html2canvas-ignore="true">
                                    <button class="status-toggle-btn px-3 py-1 text-[10px] uppercase font-bold rounded-full shadow-sm transition-all" disabled></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <?php
                // --- TOTAL CALCULATIONS FOR FOOTER ---
                $page_served_total = 0;
                $page_gross_served_total = 0;
                $page_total_all_items = 0; // New variable for Print Mode (Served + Unserved)

                foreach ($page_items as $item) :
                    // Always add to the "Print Mode" total regardless of status
                    $page_total_all_items += $item['price'];

                    // Standard Logic for "Screen Mode" (Served only)
                    // UPDATE THIS IF STATEMENT
                if ($item['status'] === 'served' || $item['status'] === 'fulfilled') :
                    $page_served_total += $item['price'];
                    $unit_sales_price = $products_by_sku[$item['sku']]['sales_price'] ?? 0;
                    $page_gross_served_total += $unit_sales_price * $item['quantity'];
                endif;
                endforeach;

                $page_vatable_purchases = ($page_gross_served_total > 0) ? $page_gross_served_total / 1.12 : 0;
                ?>
                
                <div class="flex justify-end mt-0 px-8 py-6 bg-slate-50 border-t border-slate-200">
                    <div class="w-full sm:w-auto sm:min-w-[280px] space-y-3">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Vatable Purchases:</span>
                            <span class="font-mono"><?php echo '₱' . number_format($page_vatable_purchases, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Gross Total:</span>
                            <span class="font-mono"><?php echo '₱' . number_format($page_gross_served_total, 2); ?></span>
                        </div>
                        
                        <div class="flex justify-between text-base font-bold text-slate-800 border-t border-slate-300 pt-3 no-print" data-html2canvas-ignore="true">
                            <span>Served Total:</span>
                            <span class="text-emerald-600"><?php echo '₱' . number_format($page_served_total, 2); ?></span>
                        </div>

                        <div class="flex justify-between text-base font-bold text-slate-800 border-t border-slate-300 pt-3 print-only">
                            <span>Total:</span>
                            <span class="text-slate-800"><?php echo '₱' . number_format($page_total_all_items, 2); ?></span>
                        </div>
                    </div>
                </div>
                
            </div>
        <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div> </div>
    
    <?php include 'components/modals.php'; ?>

<script>
    window.isBulkPrint = <?php echo json_encode($is_bulk_print); ?>;
    window.orderId = <?php echo json_encode($first_order['id'] ?? null); ?>;
    window.orderLocation = <?php echo json_encode($first_order['order']['location'] ?? ''); ?>;
    window.productsBySku = <?php echo json_encode($global_products_by_sku); ?>;
    
    // ====== ★ PO/SO QUICK SWITCH ======
    (function setupQuickSwitch(){
        const input = document.getElementById('quickSwitchInput');
        const results = document.getElementById('quickSwitchResults');
        if (!input || !results) return;

        let debounceTimer = null;
        let lastQuery = '';

        function close() { results.classList.add('hidden'); results.innerHTML = ''; }

        function escapeHtml(s){return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}

        async function search(q) {
            const fd = new FormData();
            fd.append('action', 'get_orders');
            fd.append('page', '1');
            fd.append('limit', '8');
            fd.append('month', 'all');
            fd.append('status_filter', 'active'); // ★ Hides cancelled and deleted orders
            // Heuristic: try BOTH po and so search so either input matches
            fd.append('po_number', q);
            try {
                const r = await fetch('api.php', { method: 'POST', body: fd, credentials: 'same-origin' });
                const j = await r.json();
                let rows = (j.success && Array.isArray(j.data)) ? j.data : [];

                // If no PO matches, try SO search
                if (rows.length === 0) {
                    const fd2 = new FormData();
                    fd2.append('action', 'get_orders');
                    fd2.append('page', '1');
                    fd2.append('limit', '8');
                    fd2.append('month', 'all');
                    fd2.append('status_filter', 'active'); // ★ Hides cancelled and deleted orders
                    fd2.append('so_number', q);
                    const r2 = await fetch('api.php', { method: 'POST', body: fd2, credentials: 'same-origin' });
                    const j2 = await r2.json();
                    if (j2.success && Array.isArray(j2.data)) rows = j2.data;
                }
                render(rows, q);
            } catch (e) {
                results.innerHTML = '<div class="p-4 text-sm text-red-600">Could not search.</div>';
                results.classList.remove('hidden');
            }
        }

        function render(rows, q) {
            if (!rows.length) {
                results.innerHTML = '<div class="p-4 text-sm text-slate-500 text-center">No orders matching "<strong>'+escapeHtml(q)+'</strong>"</div>';
                results.classList.remove('hidden');
                return;
            }
            results.innerHTML = rows.map(o => {
                let isInvoiced = false;
                try { const arr = JSON.parse(o.so_number || '[]'); if (Array.isArray(arr)) isInvoiced = arr.some(s => s && String(s).trim()); } catch(e) {}
                const isCancelled = (o.status === 'cancelled');
                const badge = isCancelled
                    ? '<span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-red-100 text-red-700 border border-red-200">Cancelled</span>'
                    : (isInvoiced
                        ? '<span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-emerald-100 text-emerald-700 border border-emerald-200">Encoded</span>'
                        : '<span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-amber-100 text-amber-700 border border-amber-200">Open</span>');
                const dateStr = o.order_date ? new Date(o.order_date).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '';
                const isCurrent = (parseInt(o.id,10) === parseInt(window.orderId,10));
                return `
                    <a href="view_order.php?id=${o.id}" class="block px-3 py-2.5 border-b border-slate-100 hover:bg-indigo-50 transition-colors ${isCurrent ? 'bg-indigo-50/60' : ''}">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-800 truncate">${escapeHtml(o.po_number)} ${isCurrent ? '<span class="text-[9px] text-indigo-600 font-black ml-1">(current)</span>' : ''}</div>
                                <div class="text-xs text-slate-500 truncate">${escapeHtml(o.customer_name||'')}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">${escapeHtml(dateStr)} · ${escapeHtml(o.location||'')}</div>
                            </div>
                            <div class="flex-shrink-0">${badge}</div>
                        </div>
                    </a>`;
            }).join('');
            results.classList.remove('hidden');
        }

        input.addEventListener('input', () => {
            const q = input.value.trim();
            lastQuery = q;
            clearTimeout(debounceTimer);
            if (!q) { close(); return; }
            debounceTimer = setTimeout(() => { if (q === lastQuery) search(q); }, 300);
        });
        input.addEventListener('focus', () => { if (input.value.trim()) results.classList.remove('hidden'); });
        document.addEventListener('click', (e) => {
            if (!document.getElementById('quickSwitchWrapper').contains(e.target)) close();
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { close(); input.blur(); }
            if (e.key === 'Enter') {
                const first = results.querySelector('a');
                if (first) { window.location.href = first.getAttribute('href'); }
            }
        });
    })();

    // --- PRINT FULFILLED LOGIC ---
    const printFulfilledBtn = document.getElementById('printFulfilledBtn');
    if (printFulfilledBtn) {
        printFulfilledBtn.addEventListener('click', function() {
            // Check if there are actually any fulfilled pages to print
            const hasFulfilled = document.querySelector('.fulfilled-page');
            
            if (!hasFulfilled) {
                // If using Toastify for alerts:
                if (typeof Toastify !== 'undefined') {
                    Toastify({
                        text: "No fulfilled items to print yet.",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: { background: "#ef4444" }
                    }).showToast();
                } else {
                    alert("No fulfilled items to print yet.");
                }
                return;
            }

            // Add class to hide standard pages
            document.body.classList.add('print-fulfilled-only');
            
            // Trigger print prompt
            window.print();
            
            // Remove class immediately after so normal view is restored
            setTimeout(() => {
                document.body.classList.remove('print-fulfilled-only');
            }, 500);
        });
    }
</script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script type="module" src="js/view_order.js"></script>
</body>
</html>