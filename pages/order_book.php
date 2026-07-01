<div id="orderBookPage" class="hidden drill-enter">
    <div class="max-w-7xl mx-auto space-y-5 pb-12">

        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-indigo-600 to-indigo-500 shadow-xl shadow-indigo-500/25 p-6 md:p-8">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-8 -left-8 w-36 h-36 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 shadow-inner flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-widest mb-0.5">Master Records</p>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight leading-none">Order Book</h1>
                        <p class="text-indigo-200 text-sm mt-1">Complete record of all processed purchase orders.</p>
                    </div>
                </div>
                <!-- KPI Chips -->
                <div class="flex gap-3 flex-wrap">
                    <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 flex flex-col gap-0.5 min-w-[130px]">
                        <span class="text-indigo-200 text-[10px] font-black uppercase tracking-widest">Page Value</span>
                        <p id="orderBookGrandTotal" class="text-2xl font-black text-white font-mono tracking-tight">₱0.00</p>
                    </div>
                    <div class="bg-white/15 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 flex flex-col gap-0.5 min-w-[110px]">
                        <span class="text-indigo-200 text-[10px] font-black uppercase tracking-widest">Total Orders</span>
                        <p id="orderBookTotalCount" class="text-2xl font-black text-white font-mono tracking-tight">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Filter Orders</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-3">
                <div class="col-span-2 md:col-span-3 lg:col-span-2">
                    <label for="obPoFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">PO Number</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-indigo-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" id="obPoFilter" placeholder="Search PO number..." class="glass-input !pl-10">
                    </div>
                </div>
                <div>
                    <label for="obMonthFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Month</label>
                    <select id="obMonthFilter" class="glass-input">
                        <option value="all">All Months</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($m == date('m')) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="obYearFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Year</label>
                    <select id="obYearFilter" class="glass-input">
                        <?php $currentYear = date('Y'); for ($y = $currentYear + 1; $y >= $currentYear - 2; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($y == $currentYear) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="obDaysFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Day</label>
                    <select id="obDaysFilter" class="glass-input">
                        <option value="all">All Days</option>
                        <?php for ($d = 1; $d <= 31; $d++): ?>
                            <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="obLocFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Location</label>
                    <select id="obLocFilter" class="glass-input">
                        <option value="all">All Locations</option>
                        <option value="Davao">Davao</option>
                        <option value="Gensan">Gensan</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 pt-3 border-t border-slate-100">
                <div>
                    <label for="obCustomerFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Customer</label>
                    <select id="obCustomerFilter" class="glass-input">
                        <option value="all">All Customers</option>
                    </select>
                </div>
                <div>
                    <label for="obBuFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">BU</label>
                    <select id="obBuFilter" class="glass-input">
                        <option value="all">All BUs</option>
                        <option value="Health">Health</option>
                        <option value="Hygiene">Hygiene</option>
                        <option value="Nutri">Nutri</option>
                    </select>
                </div>
                <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Status</label>
                        <select id="obStatusFilter" class="glass-input">
                            <option value="active" selected>Active Orders</option>
                            <option value="all">All Status (Inc. Cancelled & Deleted)</option>
                            <option value="open">Open (No SO)</option>
                            <option value="invoiced">Invoiced (Has SO)</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-red-400 uppercase tracking-widest mb-1.5">Issues</label>
                        <select id="obErrorFilter" class="glass-input border-red-200 focus:border-red-500 focus:ring-red-500/20">
                            <option value="all">No Issue Filter</option>
                            <option value="no_so">Missing SO Number</option>
                            <option value="dup_po">Duplicate PO</option>
                            <option value="dup_so">Duplicate SO</option>
                        </select>
                    </div>
                <div>
                    <label for="obSoFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">SO Number</label>
                    <input type="text" id="obSoFilter" placeholder="Search SO..." class="glass-input">
                </div>
                <div>
                    <label for="obAddressFilter" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Address</label>
                    <input type="text" id="obAddressFilter" placeholder="Search address..." class="glass-input">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-3.5 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Order List</span>
                    <span id="orderBookTotalCountBadge" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold text-white bg-indigo-500 shadow-sm">0 found</span>
                </div>
                
                <!-- Bulk Actions Toolbar -->
                <div id="obBulkActions" class="hidden flex items-center gap-2 animate-fadeIn bg-white px-3 py-1.5 rounded-lg border border-indigo-100 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 mr-2"><span id="obSelectedCount">0</span> selected</span>
                    <button id="obBulkViewBtn" class="px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 rounded-lg transition-colors">View Selected</button>
                    <button id="obBulkPrintBtn" class="px-3 py-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-lg transition-colors shadow-sm">Bulk Print / PDF</button>                    <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                    <div class="w-px h-4 bg-slate-200 mx-1"></div>
                    <button id="obBulkCancelBtn" class="px-3 py-1.5 text-xs font-bold text-orange-600 bg-orange-50 border border-orange-200 hover:bg-orange-100 rounded-lg transition-colors">Cancel Selected</button>
                    <button id="obBulkDeleteBtn" class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg transition-colors">Delete Selected</button>
                    <div class="w-px h-4 bg-slate-200 mx-1"></div>
                    <button id="obBulkDeductBtn" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded-lg transition-colors" title="Force deduct the stock for these POs again">Re-Deduct Stock</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-4 py-4 w-10 text-center"><input type="checkbox" id="obSelectAll" class="rounded w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer shadow-sm"></th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Customer & PO</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Address</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Date</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Location / BU</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Encoder</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Total Value</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orderBookList" class="divide-y divide-slate-50">
                        <tr>
                            <td colspan="8" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-300 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </div>
                                    <span class="text-sm font-bold text-slate-400">Loading orders...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span id="obPageInfo" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Page 1 of 1</span>
                    <button id="obViewAllBtn" class="hidden text-[10px] font-bold text-indigo-500 hover:text-indigo-700 underline uppercase tracking-wider cursor-pointer transition-colors">View All on One Page</button>
                </div>
                <div class="flex items-center gap-2">
                    <button id="obPrevBtn" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:text-indigo-600 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        Previous
                    </button>
                    <button id="obNextBtn" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        Next
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

<div id="obDeleteModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all scale-100 border border-red-100">
        <div class="flex items-start gap-4 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-[#0D111A] uppercase tracking-tight">Delete Order?</h3>
                <p class="text-sm text-[#6B7280] mt-2">
                    Are you sure you want to delete Order <span id="obDeleteModalId" class="font-bold text-[#0D111A]">#---</span>?
                </p>
                <p class="text-xs text-red-500 mt-2 font-medium bg-red-50 p-2 rounded-lg border border-red-100">
                    Warning: This action is permanent and cannot be undone. The order will be completely erased from the database.
                </p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button id="obCancelDeleteBtn" class="px-4 py-2 text-sm font-bold text-[#6B7280] hover:text-[#0D111A] hover:bg-gray-50 rounded-lg transition-colors">Cancel</button>
            <button id="obConfirmDeleteBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg text-sm shadow-sm transition-colors">Delete Order</button>
        </div>
    </div>
</div>

    <div id="obUncancelModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all scale-100 border border-emerald-100">
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div class="w-full">
                    <h3 class="text-lg font-black text-[#0D111A] uppercase tracking-tight">Restore Cancelled Order?</h3>
                    <p class="text-sm text-[#6B7280] mt-2">
                        You are about to un-cancel Order <span id="obUncancelModalId" class="font-bold text-[#0D111A]">#---</span>.
                    </p>
                    <p class="text-xs text-emerald-700 mt-2 font-medium bg-emerald-50 p-2 rounded-lg border border-emerald-100">
                        Each item will be returned to the status it had before cancellation. Stock for served/fulfilled items will be re-deducted.
                    </p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <button id="obCloseUncancelBtn" class="px-4 py-2 text-sm font-bold text-[#6B7280] hover:text-[#0D111A] hover:bg-gray-50 rounded-lg transition-colors">Back</button>
                <button id="obConfirmUncancelBtn" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 px-6 rounded-lg text-sm shadow-sm transition-colors">Yes, Restore Order</button>
            </div>
        </div>
    </div>

    <div id="obCancelModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all scale-100 border border-orange-100">
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="w-full">
                    <h3 class="text-lg font-black text-[#0D111A] uppercase tracking-tight">Cancel Order?</h3>
                    <p class="text-sm text-[#6B7280] mt-2">
                        You are about to cancel Order <span id="obCancelModalId" class="font-bold text-[#0D111A]">#---</span>.
                    </p>
                    
                    <div class="mt-4">
                        <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-wider mb-2">Reason for Cancellation</label>
                        <select id="obCancelReason" class="block w-full text-sm py-2 px-3 border border-[rgba(13,17,26,0.08)] rounded-xl bg-[#F9FAFB] focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 mb-2">
                            <option value="Cancel date due">Cancel date due</option>
                            <option value="Errors encoding">Errors encoding</option>
                            <option value="Aging/ Overdue payments">Aging/ Overdue payments</option>
                            <option value="Over their LV">Over their LV</option>
                            <option value="Other">Other (Please specify)</option>
                        </select>
                        <input type="text" id="obCancelReasonCustom" placeholder="Type custom reason here..." class="hidden block w-full text-sm py-2 px-3 border border-[rgba(13,17,26,0.08)] rounded-xl bg-[#F9FAFB] focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                <button id="obCloseCancelBtn" class="px-4 py-2 text-sm font-bold text-[#6B7280] hover:text-[#0D111A] hover:bg-gray-50 rounded-lg transition-colors">
                    Back
                </button>
                <button id="obConfirmCancelBtn" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg text-sm shadow-sm transition-colors">
                    Confirm Cancellation
                </button>
            </div>
        </div>
    </div>
</div>