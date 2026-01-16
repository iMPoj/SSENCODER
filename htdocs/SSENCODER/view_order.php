<?php
session_start();
require 'db_connect.php';

$user_role = $_SESSION['role'] ?? 'viewer';
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    header('Location: index.php');
    exit;
}

// Fetch the main order details
$orderStmt = $pdo->prepare("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch all items for this order
$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();

// Get SO Numbers and decode them from JSON. Default to an empty array.
$so_numbers = json_decode($order['so_number'] ?? '[]', true);
if (!is_array($so_numbers)) {
    $so_numbers = [];
}

// Define the BU to BU Code mapping
$bu_map = [
    'Nutri' => 'ifcn',
    'Health' => 'rw',
    'Hygiene' => 'hygiene'
];
$bu_code = $bu_map[$order['bu']] ?? 'N/A';


// --- PRODUCT FAMILY & SKU LOOKUP ---
$products_by_sku = [];
if (!empty($items)) {
    // Get all unique SKUs from the current order items
    $itemSkus = array_unique(array_column($items, 'sku'));
    if (!empty($itemSkus)) {
        // Find all product families associated with these SKUs
        $placeholders = implode(',', array_fill(0, count($itemSkus), '?'));
        $sql = "SELECT DISTINCT product_id FROM product_codes WHERE code IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($itemSkus);
        $productIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($productIds)) {
            // Fetch all related codes for those product families, INCLUDING the sales_price
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $sql = "SELECT p.id, p.description, p.bu, pc.code, pc.type, pc.sales_price 
                    FROM products p JOIN product_codes pc ON p.id = pc.product_id 
                    WHERE p.id IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($productIds);
            $related_products_data = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            // Structure the data for easy lookup
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
                }
            }
        }
    }
}

// --- CALCULATE TOTALS ---
$totalServedValue = 0;
$totalUnservedValue = 0;
$totalUnservedCount = 0;
$totalPristineValue = 0;
$totalServedQty = 0;
$totalItemQty = 0;
$totalGrossServedValue = 0; 

foreach ($items as $item) {
    $totalItemQty += $item['quantity'];

    $unit_sales_price = $products_by_sku[$item['sku']]['sales_price'] ?? 0;
    $totalPristineValue += $unit_sales_price * $item['quantity'];

    if ($item['status'] === 'served') {
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

// --- Calculate Vatable Values ---
$totalVatablePurchases = ($totalPristineValue > 0) ? $totalPristineValue / 1.12 : 0;
$totalVatableServed = ($totalGrossServedValue > 0) ? $totalGrossServedValue / 1.12 : 0;

// Split items into pages of 12
$item_pages = array_chunk($items, 12);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - PO #<?php echo htmlspecialchars($order['po_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .summary-card { transition: all 0.2s ease-in-out; }
        .summary-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            /* Ensure page breaks work correctly */
            .page-container { page-break-after: always; box-shadow: none !important; border: none !important; }
            /* Hide URL headers/footers in modern browsers if possible (user setting dependent) */
            @page { margin: 0.5cm; }
        }
    </style>
</head>
<body class="text-slate-800">
    <div id="loading-overlay" class="modal-backdrop" style="display: none; z-index: 9999;">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-white"></div>
    </div>
    
    <div class="container mx-auto p-4 md:p-8 max-w-7xl">
        
        <header class="sticky top-0 z-40 bg-[#f8fafc]/90 backdrop-blur-sm py-4 mb-6 flex justify-end items-center no-print border-b border-slate-200">
            <div class="flex gap-3 items-center"> 
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                    <label for="orderDiscountInput" class="text-xs font-bold text-slate-500 uppercase">Discount</label>
                    <input type="number" id="orderDiscountInput" step="0.1" value="<?php echo htmlspecialchars($order['discount_percentage']); ?>" class="w-16 text-right font-bold text-slate-800 border-none focus:ring-0 p-0 text-sm bg-transparent" disabled>
                    <span class="text-sm font-bold text-slate-400">%</span>
                </div>
                
                <button onclick="window.print()" class="btn bg-slate-800 hover:bg-slate-900 text-white text-sm py-2 px-4 shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Print
                </button>

                <?php if ($user_role === 'admin' || $user_role === 'encoder'):
                    $pristine_class = $order['is_pristine_checked'] ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-400 hover:bg-slate-500';
                    $pristine_text = $order['is_pristine_checked'] ? '✓ Pristine' : 'Mark Pristine';
                ?>
                    <button id="pristineCheckBtn" data-status="<?php echo $order['is_pristine_checked']; ?>" class="btn <?php echo $pristine_class; ?> text-white text-sm py-2 px-4 shadow-sm"><?php echo $pristine_text; ?></button>
                    
                    <button id="editOrderBtn" class="btn bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm py-2 px-4 shadow-sm">Edit Order</button>
                    <button id="repairDescriptionsBtn" class="btn bg-amber-500 hover:bg-amber-600 text-white text-sm py-2 px-4 shadow-sm hidden">Repair Text</button>
                    <button id="recalculatePricesBtn" class="btn bg-blue-500 hover:bg-blue-600 text-white text-sm py-2 px-4 shadow-sm hidden">Recalc Prices</button>
                    <button id="saveChangesBtn" class="btn bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 px-4 shadow-sm hidden">Save Changes</button>
                    <button id="cancelChangesBtn" class="btn bg-white text-red-600 border border-red-200 hover:bg-red-50 text-sm py-2 px-4 shadow-sm hidden">Cancel</button>
                <?php endif; ?>
                <?php if ($user_role === 'admin'): ?>
                    <button id="deleteOrderBtn" class="btn bg-red-100 text-red-700 hover:bg-red-200 text-sm py-2 px-4">Delete</button>
                <?php endif; ?>
            </div>
        </header>

        <?php foreach ($item_pages as $page_index => $page_items): ?>

            <?php if ($page_index === 0): ?>
                <div class="mb-8 no-print">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        Performance Overview
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                        
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 summary-card">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total PO Amount</p>
                            <p class="text-lg font-black text-slate-800 truncate" title="<?php echo '₱' . number_format($totalPoValue, 2); ?>"><?php echo '₱' . number_format($totalPoValue, 0); ?></p>
                        </div>
                        
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 summary-card relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-slate-50 rounded-bl-full -mr-8 -mt-8"></div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 relative z-10">Vatable Purchase</p>
                            <p class="text-lg font-bold text-slate-600 truncate relative z-10" title="<?php echo '₱' . number_format($totalVatablePurchases, 2); ?>"><?php echo '₱' . number_format($totalVatablePurchases, 0); ?></p>
                        </div>
                        
                        <div class="bg-emerald-50 p-4 rounded-xl shadow-sm border border-emerald-100 summary-card">
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Served Value</p>
                            <p class="text-lg font-black text-emerald-700 truncate" title="<?php echo '₱' . number_format($totalServedValue, 2); ?>"><?php echo '₱' . number_format($totalServedValue, 0); ?></p>
                        </div>

                        <div class="bg-emerald-50 p-4 rounded-xl shadow-sm border border-emerald-100 summary-card">
                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Vatable Served</p>
                            <p class="text-lg font-black text-emerald-700 truncate" title="<?php echo '₱' . number_format($totalVatableServed, 2); ?>"><?php echo '₱' . number_format($totalVatableServed, 0); ?></p>
                        </div>
                        
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 summary-card">
                            <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mb-1">Unserved Value</p>
                            <p class="text-lg font-bold text-red-600 truncate" title="<?php echo '₱' . number_format($totalUnservedValue, 2); ?>"><?php echo '₱' . number_format($totalUnservedValue, 0); ?></p>
                        </div>
                        
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 summary-card">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Missed Items</p>
                            <p class="text-lg font-bold text-slate-700 truncate"><?php echo $totalUnservedCount; ?> <span class="text-xs font-normal text-slate-400">SKUs</span></p>
                        </div>
                        
                        <div class="bg-indigo-50 p-4 rounded-xl shadow-sm border border-indigo-100 summary-card">
                            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider mb-1">Qty Fill Rate</p>
                            <p class="text-lg font-black text-indigo-700 truncate"><?php echo number_format($qtyFillRate, 1); ?>%</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-lg border border-slate-200 mb-8 page-container overflow-hidden" style="page-break-after: always;">
                
                <div class="bg-slate-50 p-6 border-b border-slate-200">
                    <div class="flex flex-col md:flex-row justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown Customer'); ?></h1>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 font-mono">
                                        <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200">Code: <?php echo htmlspecialchars($order['customer_code'] ?? 'N/A'); ?></span>
                                        <span class="bg-white px-1.5 py-0.5 rounded border border-slate-200">BU: <?php echo htmlspecialchars($bu_code); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-start gap-2 text-sm text-slate-600 mt-3 pl-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span class="leading-tight"><?php echo htmlspecialchars($order['customer_address'] ?? 'No Address'); ?></span>
                            </div>
                        </div>

                        <div class="md:text-right flex flex-col md:items-end justify-center">
                            
                            <div class="mb-1 relative text-right">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Purchase Order</span>
                                
                                <p class="view-mode-element text-2xl font-mono font-bold text-slate-800 tracking-tight" id="currentPoDisplay">
                                    <?php echo htmlspecialchars($order['po_number']); ?>
                                </p>

                                <div class="edit-mode-element hidden">
                                    <input type="text" id="orderPoInput" 
                                           value="<?php echo htmlspecialchars($order['po_number']); ?>" 
                                           class="text-xl font-mono font-bold border border-slate-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full text-right px-2 py-1">
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4 text-sm text-slate-600 relative">
                                
                                <div class="flex items-center gap-1 group relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    
                                    <span class="view-mode-element" id="currentDateDisplay"><?php echo date("M j, Y", strtotime($order['order_date'])); ?></span>
                                    
                                    <input type="date" id="orderDateInput" 
                                           value="<?php echo date('Y-m-d', strtotime($order['order_date'])); ?>" 
                                           class="edit-mode-element hidden text-xs border border-slate-300 rounded px-2 py-1 shadow-sm">
                                </div>

                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    
                                    <span class="view-mode-element"><?php echo htmlspecialchars($order['location']); ?></span>
                                    
                                    <select id="orderLocationInput" class="edit-mode-element hidden text-xs border border-slate-300 rounded px-2 py-1 shadow-sm bg-white">
                                        <option value="Davao" <?php echo $order['location'] === 'Davao' ? 'selected' : ''; ?>>Davao</option>
                                        <option value="Gensan" <?php echo $order['location'] === 'Gensan' ? 'selected' : ''; ?>>Gensan</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-3 w-full md:w-64">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">SO Number</span>
                                </div>
                                <span class="soNumberText block font-mono text-sm bg-white border border-slate-200 rounded px-2 py-1"><?php echo htmlspecialchars($so_numbers[$page_index] ?? 'N/A'); ?></span>
                                <textarea class="soNumberInput hidden mt-1 block w-full rounded-md border-slate-300 shadow-sm text-xs font-mono" data-page-index="<?php echo $page_index; ?>" rows="1"><?php echo htmlspecialchars($so_numbers[$page_index] ?? ''); ?></textarea>
                            </div>

                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                  <table class="min-w-full">
                    <thead class="bg-white border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider w-1/3">Description / SKU</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unit Price (Pristine)</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Qty</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Price</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider no-print">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($page_items as $item_index => $item): 
                            $global_index = ($page_index * 12) + $item_index;
                            $pristine_unit_price = $products_by_sku[$item['sku']]['sales_price'] ?? 0;

                            // --- SMART STOCK CHECKER ---
                            $stock_alert_html = '';
                            if ($item['status'] === 'unserved') {
                                $related_skus = [];
                                if (isset($products_by_sku[$item['sku']]['allSkus'])) {
                                    foreach ($products_by_sku[$item['sku']]['allSkus'] as $codeData) {
                                        if ($codeData['type'] === 'sku') {
                                            $related_skus[] = $codeData['code'];
                                        }
                                    }
                                } else {
                                    $related_skus[] = $item['sku'];
                                }

                                if (!empty($related_skus)) {
                                    $placeholders = implode(',', array_fill(0, count($related_skus), '?'));
                                    $sql = "SELECT product_code, stock FROM inventory_levels 
                                            WHERE product_code IN ($placeholders) 
                                              AND location = ? 
                                              AND stock >= ? 
                                            ORDER BY stock DESC LIMIT 1";
                                    $params = $related_skus;
                                    $params[] = $order['location'];
                                    $params[] = $item['quantity']; 

                                    $stockCheckStmt = $pdo->prepare($sql);
                                    $stockCheckStmt->execute($params);
                                    $stock_result = $stockCheckStmt->fetch();

                                    if ($stock_result) {
                                        $avail_sku = $stock_result['product_code'];
                                        $avail_stock = $stock_result['stock'];
                                        $sku_hint = ($avail_sku != $item['sku']) ? " (Use: <strong>$avail_sku</strong>)" : "";

                                        $stock_alert_html = '
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200 animate-pulse whitespace-nowrap shadow-sm ml-2 no-print" title="Stock Available: ' . $avail_stock . ' (' . $avail_sku . ')">
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                                Stock Ready' . $sku_hint . '
                                            </span>';
                                    }
                                }
                            }
                        ?>
                            <tr class="item-row text-sm hover:bg-slate-50 transition-colors" data-item-id="<?php echo $item['id']; ?>" data-original-sku="<?php echo htmlspecialchars($item['sku']); ?>" data-original-status="<?php echo htmlspecialchars($item['status']); ?>">

                                <td data-label="#" class="px-2 py-3 text-center font-mono text-xs text-slate-400"><?php echo $global_index + 1; ?></td>
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
                                            <div class="flex-shrink-0 pt-0.5 view-mode-element">
                                                <?php echo $stock_alert_html; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Pristine Unit Price" class="px-4 py-3 font-mono text-xs text-slate-500">
                                    <span class="pristine-price-display font-medium"><?php echo '₱' . number_format($pristine_unit_price, 2); ?></span>
                                </td>
                                <td data-label="Quantity" class="px-4 py-3">
                                    <input type="number" value="<?php echo htmlspecialchars($item['quantity']); ?>" class="quantity-input w-16 rounded-md border-slate-200 shadow-sm text-xs py-1 text-center font-bold text-slate-700 bg-white" disabled>
                                </td>
                                <td data-label="Price" class="px-4 py-3">
                                    <input type="number" step="0.01" value="<?php echo htmlspecialchars($item['price']); ?>" class="price-input w-24 rounded-md border-slate-200 shadow-sm text-xs py-1 text-right font-mono font-medium text-slate-800 bg-slate-50" readonly>
                                </td>
                                <td data-label="Status" class="px-4 py-3 text-center no-print">
                                    <button class="status-toggle-btn px-3 py-1 text-[10px] uppercase font-bold rounded-full shadow-sm transition-all" disabled></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <?php
                $page_served_total = 0;
                $page_gross_served_total = 0;
                foreach ($page_items as $item) :
                    if ($item['status'] === 'served') :
                        $page_served_total += $item['price'];
                        $unit_sales_price = $products_by_sku[$item['sku']]['sales_price'] ?? 0;
                        $page_gross_served_total += $unit_sales_price * $item['quantity'];
                    endif;
                endforeach;

                // --- NEW: Calculate Vatable Purchases for this page ---
                $page_vatable_purchases = ($page_gross_served_total > 0) ? $page_gross_served_total / 1.12 : 0;
                ?>
                <div class="flex justify-end mt-0 px-6 py-4 bg-slate-50 border-t border-slate-200">
                    <div class="w-full sm:w-auto sm:min-w-[280px] space-y-2">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Vatable Purchases (Page):</span>
                            <span class="font-mono"><?php echo '₱' . number_format($page_vatable_purchases, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Gross Total (Page):</span>
                            <span class="font-mono"><?php echo '₱' . number_format($page_gross_served_total, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-slate-800 border-t border-slate-200 pt-2">
                            <span>Served Total (Page):</span>
                            <span class="text-emerald-600"><?php echo '₱' . number_format($page_served_total, 2); ?></span>
                        </div>
                    </div>
                </div>
                
            </div>
        <?php endforeach; ?>
        
    </div>
    
    <?php include 'components/modals.php'; ?>

    <script>
        window.orderId = <?php echo json_encode($orderId); ?>;
        window.orderLocation = <?php echo json_encode($order['location']); ?>;
        window.productsBySku = <?php echo json_encode($products_by_sku); ?>;
    </script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script type="module" src="js/view_order.js"></script>
</body>
</html>