<!-- Low Stock Modal -->
<div id="lowStockModal" class="modal-backdrop hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="modal-content w-full max-w-md bg-white rounded-2xl shadow-2xl shadow-slate-200/80 p-8 text-center border border-slate-100">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 border border-amber-100 mb-5">
            <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Low Stock Alert</h3>
        <p class="text-sm text-slate-500 leading-relaxed">Stocks for <strong id="modalItemName" class="text-slate-800"></strong> are not enough. Would you like to mark this item as unserved?</p>
        <div class="mt-7 flex gap-3">
            <button id="markUnservedBtn" type="button" class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl border border-transparent bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 active:scale-95 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                Mark Unserved
            </button>
            <button id="addAnywayBtn" type="button" class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                Add Anyway
            </button>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="modal-backdrop hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="modal-content text-center w-full max-w-md bg-white rounded-2xl shadow-2xl shadow-slate-200/80 p-8 border border-slate-100">
        <h3 id="messageModalTitle" class="text-xl font-bold text-slate-900 mb-3">Message</h3>
        <p id="messageModalText" class="text-sm text-slate-500 leading-relaxed"></p>
        <div class="mt-7">
            <button id="messageModalCloseBtn" type="button" class="w-full inline-flex justify-center rounded-xl border border-transparent bg-gradient-to-r from-[#E42278] to-[#ED7BAB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-110 active:scale-95 transition-all">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="modal-backdrop hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="modal-content text-center w-full max-w-md bg-white rounded-2xl shadow-2xl shadow-slate-200/80 p-8 border border-slate-100">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 border border-red-100 mb-5">
            <svg class="h-7 w-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <h3 id="confirmModalTitle" class="text-xl font-bold text-slate-900 mb-2">Are you sure?</h3>
        <p id="confirmModalText" class="text-sm text-slate-500 leading-relaxed"></p>
        <div class="mt-7 flex gap-3">
            <button id="confirmModalNoBtn" type="button" class="flex-1 inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                Cancel
            </button>
            <button id="confirmModalYesBtn" type="button" class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl border border-transparent bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 active:scale-95 transition-all">
                Yes, Confirm
            </button>
        </div>
    </div>
</div>

<!-- PDF Upload Modal -->
<div id="pdfUploadModal" class="modal-backdrop hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="modal-content !max-w-xl w-full bg-white rounded-2xl shadow-2xl shadow-slate-200/80 p-8 text-left border border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-900">PDF to Order Uploader</h3>
                <p class="text-sm text-slate-500 mt-0.5">Process one or more PO PDFs at once.</p>
            </div>
            <button id="closePdfModalBtn" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors text-xl font-bold">&times;</button>
        </div>
        <div id="pdfDropZone" class="border-2 border-dashed border-slate-200 rounded-xl p-10 text-center cursor-pointer hover:border-[#E42278] hover:bg-pink-50/30 bg-slate-50 transition-all group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-3 text-slate-300 group-hover:text-[#E42278] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
            <p class="text-slate-500 font-medium">Drag & drop files here, or <span class="text-[#E42278] font-semibold">click to select</span></p>
            <p class="text-xs text-slate-400 mt-1">PDF files only</p>
            <input type="file" id="pdfFileInput" multiple accept=".pdf" class="hidden">
        </div>
        <div id="pdfFileList" class="space-y-2 mt-4 max-h-48 overflow-y-auto"></div>
        <div class="mt-6 flex justify-end gap-3">
            <button id="closePdfModalBtn2" type="button" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                Cancel
            </button>
            <button id="startPdfProcessingBtn" type="button" class="inline-flex justify-center items-center gap-2 rounded-xl border border-transparent bg-gradient-to-r from-[#E42278] to-[#ED7BAB] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:brightness-110 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Process Files
            </button>
        </div>
    </div>
</div>

<!-- Address Code Modal -->
<div id="addressCodeModal" class="modal-backdrop hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="modal-content !max-w-md w-full bg-white rounded-2xl shadow-2xl shadow-slate-200/80 p-8 text-left border border-slate-100">
        <h3 id="addressCodeModalTitle" class="text-xl font-bold text-slate-900 mb-6">Add / Edit Address Code</h3>
        <form id="addressCodeForm" class="space-y-5">
            <input type="hidden" id="addressCodeId" name="id">
            <div>
                <label for="addressInput" class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
                <input type="text" id="addressInput" name="address" required class="glass-input">
            </div>
            <div>
                <label for="customerCodeInput" class="block text-sm font-semibold text-slate-700 mb-1.5">Customer Code</label>
                <input type="text" id="customerCodeInput" name="customer_code" required class="glass-input">
            </div>
            <div>
                <label for="customerLocationInput" class="block text-sm font-semibold text-slate-700 mb-1.5">Warehouse Location</label>
                <select id="customerLocationInput" name="location" class="glass-input w-full">
                    <option value="Davao">Davao</option>
                    <option value="Gensan">Gensan</option>
                </select>
            </div>
            <div class="mt-6 flex justify-end gap-3 pt-2">
                <button id="cancelAddressCodeBtn" type="button" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Cancel</button>
                <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-gradient-to-r from-[#E42278] to-[#ED7BAB] px-6 py-2.5 text-sm font-semibold text-white hover:brightness-110 active:scale-95 transition-all">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Stock Decision Modal -->
<div id="stockDecisionModal" class="modal-backdrop hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="modal-content text-center max-w-md w-full bg-white rounded-2xl shadow-2xl shadow-slate-200/80 p-8 border border-slate-100">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 border border-emerald-100 mb-5">
            <svg class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Stock Adjustment</h3>
        <p class="text-sm text-slate-500 leading-relaxed">
            You have marked items as <strong class="text-slate-800">Unserved</strong>.<br>
            Should the stock for these items be returned to inventory?
        </p>
        <div class="mt-7 flex flex-col sm:flex-row gap-3">
            <button id="stockReturnYesBtn" class="flex-1 inline-flex justify-center items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-95 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Yes, Return Stock
            </button>
            <button id="stockReturnNoBtn" class="flex-1 inline-flex justify-center items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 active:scale-95 transition-all">
                No, Keep Deducted
            </button>
        </div>
        <button id="stockReturnCancelBtn" class="mt-4 text-xs text-slate-400 hover:text-slate-600 hover:underline transition-colors">Cancel</button>
    </div>
</div>
