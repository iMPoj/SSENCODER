<?php
session_start();
// Redirect if not logged in or not an admin/encoder
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || !in_array($_SESSION['role'], ['admin', 'encoder'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Order</title>
    <script>
        window.userRole = <?php echo json_encode($_SESSION['role'] ?? 'viewer'); ?>;
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body id="app-body" class="bg-slate-100">

    <div id="loading-overlay" class="modal-backdrop" style="display: none; z-index: 9999;">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-white"></div>
    </div>

    <?php include __DIR__ . '/header.php'; ?>

    <main id="main-content" class="flex-1 p-4 sm:p-6 lg:p-8">
        <div id="page-content-wrapper" class="max-w-7xl mx-auto">
            
            <div id="encoderPage" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6 relative z-0">
                    
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">New Order</h1>
                            <p class="text-sm text-slate-500">Drafting & Entry</p>
                        </div>
                        <div class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                            Draft Mode
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 relative z-40">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 border-b border-slate-100 pb-6">
                            <div>
                                <label for="orderLocation" class="block text-xs font-bold text-slate-500 uppercase mb-1">Location</label>
                                <select id="orderLocation" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 text-sm py-2">
                                    <option value="">-- Select --</option>
                                    <option value="Davao">Davao</option>
                                    <option value="Gensan">Gensan</option>
                                </select>
                            </div>
                            <div>
                                <label for="orderBu" class="block text-xs font-bold text-slate-500 uppercase mb-1">Business Unit</label>
                                <select id="orderBu" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 text-sm py-2">
                                    <option value="">-- Select --</option>
                                    <option value="Health">Health</option>
                                    <option value="Hygiene">Hygiene</option>
                                    <option value="Nutri">Nutri</option>
                                </select>
                            </div>
                        </div>

                        <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Customer Details
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                            <div class="sm:col-span-8 relative">
                                <label for="customerName" class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Customer Name</label>
                                <input type="text" id="customerName" placeholder="Search customer..." class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <div id="customerSuggestions" class="absolute z-50 w-full bg-white border border-slate-300 rounded-lg mt-1 max-h-60 overflow-y-auto hidden shadow-lg"></div>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="poNumber" class="block text-[10px] font-bold text-slate-400 uppercase mb-1">PO Number</label>
                                <input type="text" id="poNumber" placeholder="PO-XXXX" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm">
                            </div>
                            <div class="sm:col-span-8 relative">
                                <label for="customerAddress" class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Delivery Address</label>
                                <input type="text" id="customerAddress" placeholder="Search address..." class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <div id="addressSuggestions" class="absolute z-50 w-full bg-white border border-slate-300 rounded-lg mt-1 max-h-60 overflow-y-auto hidden shadow-lg"></div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="discountPercentage" class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Disc %</label>
                                <input type="number" id="discountPercentage" placeholder="0" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-right text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="customerCode" class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Code</label>
                                <input type="text" id="customerCode" class="block w-full rounded-md border-slate-200 bg-slate-50 text-slate-500 shadow-sm font-mono text-xs" readonly>
                            </div>
                        </div>
                    </div>

                    <fieldset id="summaryFieldset" disabled class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden flex flex-col min-h-[400px] relative z-10">
                        <div class="bg-slate-800 text-white p-4 flex justify-between items-center">
                            <h2 class="text-lg font-bold flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                Order Items
                            </h2>
                            <div class="text-sm text-slate-300">Total Items: <span id="totalItemsCount" class="text-white font-bold">0</span></div>
                        </div>

                        <div id="summaryOverlay" class="absolute inset-0 bg-white/90 z-20 flex items-center justify-center mt-14">
                            <span class="text-lg font-semibold text-slate-400">Setup order to enable</span>
                        </div>

                        <div class="overflow-x-auto flex-1 relative z-0">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase text-center w-12">#</th>
                                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase">Item Description / SKU</th>
                                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase text-right w-24">Unit Price</th>
                                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase text-center w-24">Qty</th>
                                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase text-right w-32">Total</th>
                                        <th class="py-3 px-4 text-xs font-bold text-slate-500 uppercase text-right w-20"></th>
                                    </tr>
                                </thead>
                                <tbody id="orderItemsList" class="divide-y divide-slate-100 text-sm">
                                    </tbody>
                            </table>
                            <div id="emptyState" class="text-center py-12 text-slate-400 italic">
                                No items added yet.
                            </div>
                        </div>

                        <div class="bg-slate-50 p-6 border-t border-slate-200 relative z-30">
                            <div class="flex justify-between items-end mb-4">
                                <span class="text-sm font-bold text-slate-500 uppercase tracking-wider">Grand Total</span>
                                <span id="orderTotalDisplay" class="text-4xl font-black text-indigo-700 tracking-tight">₱0.00</span>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" id="cancelOrderBtn" class="btn bg-white border border-slate-300 text-slate-600 hover:bg-red-50 hover:text-red-600 flex-1 py-3 font-bold shadow-sm">Reset</button>
                                
                                <button type="button" id="saveDraftBtn" class="btn bg-amber-500 hover:bg-amber-600 text-white flex-1 py-3 font-bold text-lg shadow-sm">Save Draft</button>
                                
                                <button type="button" id="submitOrderBtn" class="btn bg-indigo-600 hover:bg-indigo-700 text-white flex-[2] py-3 font-bold text-lg shadow-lg">Submit Order</button>
                            </div>
                        </div>
                    </fieldset>

                </div>

                <div class="lg:col-span-1 relative z-30">
                    <div class="sticky top-24">
                        
                        <fieldset id="itemEntryFieldset" disabled class="bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border-t-4 border-emerald-500 overflow-visible relative">
                            
                            <div id="itemEntryOverlay" class="absolute inset-0 bg-white/90 z-20 flex items-center justify-center text-center px-6 rounded-xl">
                                <span class="font-bold text-slate-400">Please select Location & BU first</span>
                            </div>

                            <div class="p-6 space-y-5">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="bg-emerald-100 p-1.5 rounded text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    </div>
                                    <h2 class="text-lg font-bold text-slate-800">Add Item</h2>
                                </div>

                                <div class="relative">
                                    <label class="block text-[10px] font-bold text-emerald-700 uppercase mb-1 tracking-wide">1. Scan Barcode</label>
                                    <input type="text" id="itemBarcode" placeholder="Scan SKU/Barcode..." class="block w-full rounded border-emerald-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono text-sm py-2.5 pl-3">
                                    <div id="barcodeSuggestions" class="absolute top-full left-0 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden z-50"></div>
                                </div>

                                <div class="relative">
                                    <label class="block text-[10px] font-bold text-emerald-700 uppercase mb-1 tracking-wide">2. Search Product</label>
                                    <input type="text" id="itemDescription" placeholder="Type description..." class="block w-full rounded border-emerald-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 pl-3">
                                    <div id="descriptionSuggestions" class="absolute top-full left-0 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-xl max-h-60 overflow-y-auto hidden z-50"></div>
                                </div>

                                <div id="skuSelectionContainer" class="hidden bg-emerald-50 p-3 rounded border border-emerald-200">
                                     <label class="block text-[10px] font-bold text-emerald-800 uppercase mb-1">Select SKU</label>
                                     <select id="itemSkuSelect" class="block w-full rounded border-emerald-300 text-xs py-1.5 mb-1"></select>
                                     <div class="text-right text-xs font-bold text-emerald-700" id="skuStockDisplay"></div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Quantity</label>
                                        <input type="number" id="itemQuantity" value="1" class="block w-full rounded border-slate-300 text-center font-bold text-slate-800 py-2 text-lg">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Unit</label>
                                        <select id="itemUnit" class="block w-full rounded border-slate-300 text-sm py-2.5">
                                            <option value="pcs">Pcs</option>
                                            <option value="case">Case</option>
                                        </select>
                                        <span id="caseInfoDisplay" class="text-[10px] text-slate-400 mt-1 block truncate"></span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Estimated Price</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">₱</span>
                                        <input type="text" id="itemPrice" placeholder="0.00" class="block w-full pl-8 rounded bg-slate-50 border-slate-200 text-right text-sm py-2 font-mono text-slate-600" readonly>
                                    </div>
                                </div>

                                <button type="button" id="addItemBtn" class="w-full btn bg-emerald-600 hover:bg-emerald-700 text-white shadow-md py-3 text-lg font-bold flex items-center justify-center gap-2 mt-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                                    ADD TO ORDER
                                </button>

                            </div>
                        </fieldset>
                        
                        <div class="mt-4 text-center text-xs text-slate-400">
                            <p>Press <span class="font-mono bg-slate-200 px-1 rounded text-slate-600">Tab</span> to select suggestion</p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include __DIR__ . '/components/modals.php'; ?>
    
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script type="module" src="js/encoder.js"></script>
    <script src="js/global.js" defer></script>
</body>
</html>