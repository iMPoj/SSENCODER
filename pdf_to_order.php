<?php
session_start();
// Redirect if not logged in or not an admin/encoder
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || !in_array($_SESSION['role'], ['admin', 'encoder'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Parsed Order | Reckitt Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Specific adjustments for PDF review page */
        .drill-enter {
            animation: drillEnter 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes drillEnter {
            from { 
                opacity: 0; 
                transform: translateX(40px) scale(0.98); 
                filter: blur(4px);
            }
            to { 
                opacity: 1; 
                transform: translateX(0) scale(1); 
                filter: blur(0);
            }
        }
    </style>
</head>
<body class="bg-white min-h-screen pb-12">
    <div id="loading-overlay" class="fixed inset-0 bg-white/95 backdrop-blur-xl z-[9999] flex items-center justify-center">
        <div class="relative">
            <div class="w-16 h-16 rounded-full border-4 border-[#F5D3DD] border-t-[#E42278] animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-8 h-8 bg-gradient-to-br from-[#E42278] to-[#ED7BAB] rounded-full opacity-20 animate-pulse"></div>
            </div>
        </div>
    </div>

    <main class="container mx-auto pt-8 px-4 drill-enter">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gradient">Review Parsed Order</h1>
                    <p class="text-[#6B7280] mt-1">Verify and process PDF order data</p>
                </div>
                <a href="index.php#admin" class="btn-secondary">Back to Admin</a>
            </div>

            <div id="queueStatusBanner" class="hidden mb-6 bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-100 rounded-xl p-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-indigo-900">Multi-Order Batch Processing</p>
                        <p class="text-xs text-indigo-700">Reviewing Order <span id="currentQueueIndex" class="font-bold text-lg">1</span> of <span id="totalQueueCount" class="font-bold">X</span></p>
                    </div>
                </div>
                <button id="skipOrderBtn" class="px-4 py-2 bg-white text-indigo-600 border border-indigo-200 text-xs font-bold rounded-lg shadow-sm hover:bg-indigo-50 transition-colors">
                    Skip This Order ⏭
                </button>
            </div>
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gradient">Review Parsed Order</h1>
                    <p class="text-[#6B7280] mt-1">Verify and process PDF order data</p>
                </div>
                <a href="index.php#admin" class="btn-secondary">Back to Admin</a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column: Customer & Items (8 cols) -->
                <div class="lg:col-span-8 space-y-6">
                    
                    <!-- Customer Info Card -->
                    <div class="glass-card p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-bold shadow-lg shadow-pink-200">1</div>
                            <div>
                                <h3 class="text-lg font-semibold text-[#0D111A]">Order Configuration</h3>
                                <p class="text-xs text-[#6B7280]">Review customer and warehouse details</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="orderLocation" class="block text-xs font-bold text-[#0D111A] uppercase tracking-wider mb-2">Warehouse Location</label>
                                <select id="orderLocation" class="glass-input bg-[#F9FAFB]">
                                    <option value="Davao">Davao</option>
                                    <option value="Gensan">Gensan</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="orderBu" class="block text-xs font-bold text-[#0D111A] uppercase tracking-wider mb-2">Business Unit</label>
                                <select id="orderBu" class="glass-input">
                                    <option value="">-- Select BU --</option>
                                    <option value="Health">Health</option>
                                    <option value="Hygiene">Hygiene</option>
                                    <option value="Nutri">Nutri</option>
                                </select>
                            </div>

                            <div>
                                <label for="customerName" class="block text-xs font-bold text-[#0D111A] uppercase tracking-wider mb-2">Customer Name</label>
                                <input type="text" id="customerName" class="glass-input bg-[#F9FAFB] text-[#6B7280]" readonly>
                            </div>

                            <div>
                                <label for="discountPercentage" class="block text-xs font-bold text-[#0D111A] uppercase tracking-wider mb-2">Discount (%)</label>
                                <input type="number" id="discountPercentage" class="glass-input" placeholder="0">
                            </div>

                            <div class="md:col-span-2">
                                <label for="customerAddress" class="block text-xs font-bold text-[#0D111A] uppercase tracking-wider mb-2">Delivery Address</label>
                                <input type="text" id="customerAddress" class="glass-input bg-[#F9FAFB] text-[#6B7280]" readonly>
                            </div>

                            <div class="md:col-span-2">
                                <label for="poNumber" class="block text-xs font-bold text-[#0D111A] uppercase tracking-wider mb-2">PO Number</label>
                                <input type="text" id="poNumber" class="glass-input bg-[#F9FAFB] font-mono text-[#0D111A]" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Add New Item Card -->
                    <div class="glass-card p-6 border-l-4 border-l-[#E42278]">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-bold shadow-lg shadow-pink-200">+</div>
                            <div>
                                <h3 class="text-lg font-semibold text-[#0D111A]">Add Additional Item</h3>
                                <p class="text-xs text-[#6B7280]">Search and add missing products</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-end">
                            <div class="sm:col-span-2 relative">
                                <label for="itemDescription" class="block text-xs font-bold text-[#E42278] uppercase tracking-wider mb-2">Search Product</label>
                                <input type="text" id="itemDescription" placeholder="Type description or SKU..." class="glass-input" autocomplete="off">
                                <div id="descriptionSuggestions" class="absolute z-50 w-full bg-white/95 backdrop-blur-xl rounded-xl shadow-2xl mt-2 hidden border border-[rgba(13,17,26,0.08)] max-h-64 overflow-y-auto suggestions-scroll"></div>
                            </div>
                            
                            <div>
                                <label for="itemQuantity" class="block text-xs font-bold text-[#0D111A] uppercase tracking-wider mb-2">Quantity</label>
                                <input type="number" id="itemQuantity" value="1" min="1" class="glass-input text-center font-bold text-lg text-[#0D111A]">
                            </div>
                            
                            <div>
                                <button id="addItemBtn" class="w-full btn btn-secondary">Add to End of List</button>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table Card -->
                    <div class="glass-card p-6 min-h-[400px]">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-bold shadow-lg shadow-pink-200">2</div>
                                <div>
                                    <h3 class="text-lg font-semibold text-[#0D111A]">Parsed Items</h3>
                                    <p class="text-xs text-[#6B7280]">Review and adjust quantities</p>
                                </div>
                            </div>
                            <span id="summaryItemCount" class="text-sm font-bold text-[#E42278] bg-pink-50 px-3 py-1 rounded-full">(0 items)</span>
                        </div>

                        <div class="overflow-x-auto -mx-2">
                            <table class="w-full text-sm data-table">
                                <thead>
                                    <tr class="text-left text-xs font-bold text-[#6B7280] uppercase tracking-wider border-b border-[rgba(13,17,26,0.08)]">
                                        <th class="pb-3 pl-2 w-10"></th>
                                        <th class="pb-3 w-12 text-center">#</th>
                                        <th class="pb-3">Description</th>
                                        <th class="pb-3 w-32">Select SKU</th>
                                        <th class="pb-3 w-20 text-center">Qty</th>
                                        <th class="pb-3 w-16">Unit</th>
                                        <th class="pb-3 w-24 text-right">Price</th>
                                        <th class="pb-3 w-24 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="orderItemsList" class="stagger-children">
                                    <!-- Dynamic rows inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Actions (4 cols) -->
                <div class="lg:col-span-4">
                    <div class="glass-card p-6 sticky top-24">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-bold shadow-lg shadow-pink-200">✓</div>
                            <div>
                                <h3 class="text-lg font-semibold text-[#0D111A]">Final Review</h3>
                                <p class="text-xs text-[#6B7280]">Process or cancel order</p>
                            </div>
                        </div>

                        <!-- Summary Stats -->
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between items-center p-4 bg-[#F9FAFB] rounded-xl border border-[rgba(13,17,26,0.08)]">
                                <span class="text-sm font-medium text-[#6B7280]">Total Items</span>
                                <span id="poItemCount" class="text-lg font-bold text-[#0D111A]">0</span>
                            </div>
                            
                            <div class="flex justify-between items-center p-4 bg-gradient-to-br from-pink-50 to-white rounded-xl border border-[#F5D3DD]">
                                <span class="text-sm font-bold text-[#E42278]">Total Amount</span>
                                <span id="orderTotalDisplay" class="text-2xl font-black text-gradient">₱0.00</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                         <div class="space-y-3">
                            <button id="processOrderBtn" class="w-full btn-primary py-3.5 text-base shadow-lg shadow-pink-200">
                                Process This Order
                            </button>
                            <a id="cancelOrderBtn" href="index.php#admin" class="block w-full btn-secondary py-3.5 text-center">
                                Cancel Batch
                            </a>
                        </div>

                        <div class="mt-6 pt-6 border-t border-[rgba(13,17,26,0.08)]">
                            <p class="text-xs text-[#6B7280] text-center leading-relaxed">
                                Drag items using the handle to reorder. 
                                <br>Click the ✖ to remove items.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'components/modals.php'; ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script type="module" src="js/pdf_to_order.js" defer></script>
</body>
</html>