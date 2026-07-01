<?php
session_start();
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
    <title>Create New Order | Reckitt Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .drill-enter {
            animation: drillEnter 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        @keyframes drillEnter {
            from { opacity: 0; transform: translateX(40px) scale(0.98); filter: blur(4px); }
            to { opacity: 1; transform: translateX(0) scale(1); filter: blur(0); }
        }
        .glass-input:focus {
            box-shadow: 0 0 0 3px rgba(228, 34, 120, 0.15), 0 4px 12px rgba(228, 34, 120, 0.1);
        }
        .suggestions-scroll::-webkit-scrollbar { width: 6px; }
        .suggestions-scroll::-webkit-scrollbar-thumb {
            background: var(--accent);
            border-radius: 3px;
        }
    </style>
</head>
<body class="min-h-screen pb-12">
    
    <div id="loading-overlay" class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-white/90 backdrop-blur-md hidden transition-opacity duration-300">
        <div class="relative flex flex-col items-center gap-6">
            <div class="absolute w-32 h-32 rounded-full border-2 border-[#E42278]/20 animate-ping"></div>
            <div class="absolute w-24 h-24 rounded-full border-2 border-[#E42278]/10 animate-ping" style="animation-delay:0.3s"></div>
            <img src="reckitt-logo.png" class="loader-logo w-24 h-24 object-contain relative z-10" alt="Loading...">
            <div class="flex flex-col items-center gap-2">
                <div class="loader-text tracking-[0.3em] uppercase text-[10px]">Updating Inventory</div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/header.php'; ?>

    <main class="w-full max-w-[1450px] mx-auto pt-24 px-4 drill-enter pb-12">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                
                <div class="glass-card p-5 lg:p-6 hover-lift">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-bold shadow-lg shadow-pink-200">1</div>
                        <div>
                            <h3 class="text-base font-bold text-[#0D111A]">Customer Details</h3>
                            <p class="text-[11px] font-medium text-[#6B7280]">Enter order header information</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-x-4 gap-y-3.5">
                        <div class="md:col-span-8 relative">
                            <label class="block text-[9px] font-bold text-[#0D111A] uppercase tracking-wider mb-1">Customer Name</label>
                            <input type="text" id="customerName" class="glass-input text-xs py-1.5 font-medium" placeholder="Search customer..." autocomplete="off">
                            <div id="customerSuggestions" class="absolute z-50 w-full bg-white/95 backdrop-blur-xl rounded-xl shadow-2xl mt-1 hidden border border-[rgba(13,17,26,0.08)] max-h-60 overflow-y-auto custom-scrollbar"></div>
                        </div>
                        
                        <div class="md:col-span-4 relative">
                            <label class="block text-[9px] font-bold text-[#0D111A] uppercase tracking-wider mb-1">Customer Code</label>
                            <input type="text" id="customerCode" class="glass-input bg-gray-50 text-gray-500 text-xs py-1.5 font-mono" readonly>
                        </div>

                        <div class="md:col-span-8 relative">
                            <label class="block text-[9px] font-bold text-[#0D111A] uppercase tracking-wider mb-1">Delivery Address</label>
                            <input type="text" id="customerAddress" class="glass-input text-xs py-1.5 font-medium" placeholder="Search address..." autocomplete="off">
                            <div id="addressSuggestions" class="absolute z-50 w-full bg-white/95 backdrop-blur-xl rounded-xl shadow-2xl mt-1 hidden border border-[rgba(13,17,26,0.08)] max-h-60 overflow-y-auto custom-scrollbar"></div>
                        </div>

                        <div class="md:col-span-4 relative">
                            <label class="block text-[9px] font-bold text-[#0D111A] uppercase tracking-wider mb-1">PO Number</label>
                            <input type="text" id="poNumber" class="glass-input font-mono text-xs py-1.5 font-bold text-gray-900" placeholder="PO-XXXX">
                        </div>

                        <div class="md:col-span-4 relative">
                            <label class="block text-[9px] font-bold text-[#0D111A] uppercase tracking-wider mb-1">Location</label>
                            <select id="orderLocation" class="glass-input text-xs py-1.5 font-medium">
                                <option value="">Select...</option>
                                <option value="Davao">Davao</option>
                                <option value="Gensan">Gensan</option>
                            </select>
                        </div>

                        <div class="md:col-span-4 relative">
                            <label class="block text-[9px] font-bold text-[#0D111A] uppercase tracking-wider mb-1">BU</label>
                            <select id="orderBu" class="glass-input text-xs py-1.5 font-medium">
                                <option value="">Select...</option>
                                <option value="Health">Health</option>
                                <option value="Hygiene">Hygiene</option>
                                <option value="Nutri">Nutri</option>
                            </select>
                        </div>

                        <div class="md:col-span-4 relative">
                            <label class="block text-[9px] font-bold text-[#0D111A] uppercase tracking-wider mb-1">Discount %</label>
                            <div class="relative">
                                <input type="number" id="discountPercentage" class="glass-input text-xs py-1.5 font-mono font-bold pr-6" placeholder="0" min="0" max="100" value="0">
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 font-bold">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-5 lg:p-6 min-h-[350px] hover-lift">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-bold shadow-lg shadow-pink-200">2</div>
                            <div>
                                <h3 class="text-base font-bold text-[#0D111A]">Order Items</h3>
                                <p class="text-[11px] font-medium text-[#6B7280]">Add products to this order</p>
                            </div>
                        </div>
                        <span id="totalItemsCount" class="text-xs font-bold text-[#E42278] bg-pink-50 px-3 py-1 rounded-full border border-pink-100 shadow-sm">0 items</span>
                    </div>

                    <div id="emptyState" class="text-center py-12 text-[#6B7280]">
                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-pink-50 to-white flex items-center justify-center border border-pink-100 shadow-sm">
                            <svg class="w-8 h-8 text-[#E42278] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="font-bold text-sm text-gray-800">No items added yet</p>
                        <p class="text-xs mt-1 text-gray-400 font-medium">Use the panel on the right to scan or search.</p>
                    </div>

                    <table id="itemsTable" class="premium-table hidden w-full">
                            <thead>
                                <tr>
                                    <th class="text-left w-1/3">Item Description</th>
                                    <th class="text-center w-1/12">Qty</th>
                                    <th class="text-right w-1/6">Unit Price</th>
                                    <th class="text-right w-1/6">Gross Total</th>
                                    <th class="text-right w-1/6">Net Total</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                        <tbody id="orderItemsList" class="stagger-children"></tbody>
                    </table>
                </div>

                <div class="flex gap-4 pb-8">
                    <button id="cancelOrderBtn" class="btn-secondary flex-1 py-2.5 text-sm shadow-sm">Reset Form</button>
                    <button id="submitOrderBtn" class="btn-primary flex-[3] py-2.5 text-sm shadow-lg shadow-pink-200">Submit Order</button>
                </div>
            </div>

            <div class="lg:col-span-4 xl:col-span-3">
                <div class="glass-card p-4 sticky top-24 max-h-[calc(100vh-6rem)] overflow-y-auto custom-scrollbar hover-lift" style="animation-delay: 0.1s">
                    <div class="flex items-center gap-2.5 mb-4 border-b border-[rgba(13,17,26,0.06)] pb-3">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center text-white font-bold text-sm shadow-sm">+</div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 leading-tight">Add Item</h3>
                            <p class="text-[9px] font-medium text-gray-500">Scan or search product</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="relative">
                            <label class="block text-[9px] font-bold text-[#E42278] uppercase tracking-widest mb-1.5">Scan Barcode</label>
                            <div class="relative">
                                <input type="text" id="itemBarcode" class="glass-input py-1.5 pl-8 text-xs border-pink-100 bg-pink-50/30" placeholder="Scan or type SKU..." autocomplete="off">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[#E42278]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <div id="barcodeSuggestions" class="absolute z-50 w-full bg-white/95 backdrop-blur-xl rounded-xl shadow-xl mt-1 hidden border border-gray-100 max-h-48 overflow-y-auto custom-scrollbar"></div>
                        </div>

                        <div class="relative">
                            <label class="block text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1.5">Or Search Product</label>
                            <input type="text" id="itemDescription" class="glass-input py-1.5 text-xs" placeholder="Type description..." autocomplete="off">
                            <div id="descriptionSuggestions" class="absolute z-50 w-full bg-white/95 backdrop-blur-xl rounded-xl shadow-xl mt-1 hidden border border-gray-100 max-h-48 overflow-y-auto custom-scrollbar"></div>
                        </div>

                        <div id="skuSelectionContainer" class="hidden bg-gradient-to-br from-pink-50/40 to-white rounded-lg p-2.5 border border-pink-100">
                            <label class="block text-[9px] font-bold text-[#E42278] uppercase tracking-widest mb-1">Select SKU</label>
                            <select id="itemSkuSelect" class="glass-input py-1.5 text-xs mb-1.5 font-semibold text-gray-800"></select>
                            <div class="flex justify-between items-center text-[9px] font-bold">
                                <span id="skuStockDisplay" class="text-gray-800 bg-white px-1.5 py-0.5 rounded shadow-sm border border-gray-100"></span>
                                <span id="caseInfoDisplay" class="text-gray-500"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1.5">Quantity</label>
                                <input type="number" id="itemQuantity" value="1" min="1" class="glass-input text-center font-black text-sm text-gray-900 py-1.5 h-auto">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1.5">Unit</label>
                                <select id="itemUnit" class="glass-input py-1.5 text-xs font-bold text-gray-800 h-auto">
                                    <option value="pcs">Pieces</option>
                                    <option value="case">Case</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1.5">Estimated Price</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">₱</span>
                                <input type="text" id="itemPrice" class="glass-input py-1.5 pl-6 text-right font-mono font-bold text-gray-900 bg-gray-50/50" readonly>
                            </div>
                        </div>

                        <button id="addItemBtn" class="btn-primary w-full py-2 mt-1 text-xs shadow-md shadow-pink-200 hover:-translate-y-0.5 transition-transform">
                            Add to Order
                        </button>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#F5D3DD]/80">
                        <div class="glass-card bg-gradient-to-br from-pink-50/40 to-white p-3 border-none">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-bold text-gray-500">Total Items</span>
                                <span id="sideItemCount" class="text-sm font-black text-[#E42278]">0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-900 font-black text-xs">Grand Total</span>
                                <span id="orderTotalDisplay" class="text-lg font-black text-transparent bg-clip-text bg-gradient-to-r from-[#E42278] to-[#ED7BAB] tracking-tight">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2.5 text-center">
                        <p class="text-[9px] text-gray-400 font-medium">Press <kbd class="bg-gray-100 text-gray-600 px-1 py-0.5 rounded font-mono border border-gray-200">Tab</kbd> to select</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script type="module" src="js/encoder.js"></script>
</body>
</html>