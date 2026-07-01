// FILE: js/order_book.js
import { appState } from './state.js';
import { postData } from './api.js';
import { showLoader, hideLoader, showMessage } from './ui.js';

let currentPage = 1;
let rowsPerPage = 20;
let orderIdToDelete = null; // Store ID for modal

// Replace the fetchOrderBookPage function with this:

export async function fetchOrderBookPage(page) {
    currentPage = page;

    // OPTIMIZATION: Only show full loader on first load or filter change
    const list = document.getElementById('orderBookList');
    if (list && page === 1) {
        // Generate 7 rows of pulsing skeleton blocks
        let skeletonRows = '';
        for (let i = 0; i < 7; i++) {
            skeletonRows += `
                <tr class="animate-pulse border-b border-slate-100">
                    <td class="p-4"><div class="h-4 bg-slate-200 rounded w-4"></div></td>
                    <td class="p-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-slate-200"></div><div class="space-y-2 flex-1"><div class="h-4 bg-slate-200 rounded w-3/4"></div><div class="h-3 bg-slate-200 rounded w-1/2"></div></div></div></td>
                    <td class="p-4"><div class="h-4 bg-slate-200 rounded w-full max-w-[160px]"></div></td>
                    <td class="p-4"><div class="space-y-2"><div class="h-4 bg-slate-200 rounded w-20"></div><div class="h-3 bg-slate-200 rounded w-16"></div></div></td>
                    <td class="p-4"><div class="space-y-2"><div class="h-4 bg-slate-200 rounded w-16"></div><div class="h-3 bg-slate-200 rounded w-12"></div></div></td>
                    <td class="p-4"><div class="w-9 h-9 rounded-full bg-slate-200 mx-auto"></div></td>
                    <td class="p-4 flex flex-col items-end space-y-2"><div class="h-5 bg-slate-200 rounded w-24"></div><div class="h-3 bg-slate-200 rounded w-10"></div></td>
                    <td class="p-4"><div class="h-8 bg-slate-200 rounded w-16 ml-auto"></div></td>
                </tr>`;
        }
        list.innerHTML = skeletonRows;
    } else {
        // Optional: Add a small spinner near pagination if moving pages
    }

    const filterData = {
        page: currentPage,
        limit: rowsPerPage,
        month: document.getElementById('obMonthFilter').value,
        year: document.getElementById('obYearFilter').value,
        po_number: document.getElementById('obPoFilter').value,
        address: document.getElementById('obAddressFilter').value,
        location: document.getElementById('obLocFilter').value,
        bu: document.getElementById('obBuFilter').value,
        customer: document.getElementById('obCustomerFilter').value,
        so_number: document.getElementById('obSoFilter').value,
        days: document.getElementById('obDaysFilter')?.value || 'all',
        status_filter: document.getElementById('obStatusFilter')?.value || 'active',
        error_filter: document.getElementById('obErrorFilter')?.value || 'all' // ★ NEW
    };

    try {
        const result = await postData('get_orders', filterData);
        if (result.success) {
            appState.processedOrders = result.data || [];
            appState.orderBookTotal = result.pagination.total || 0;
            renderOrderBookPage();
        } else {
            showMessage(result.message || 'Failed to fetch orders.', true);
        }
    } catch (e) {
        showMessage('An error occurred while fetching the order book.', true);
    }
    // Note: hideLoader() removed because we didn't call showLoader()
}
export function renderOrderBookPage() {
    const list = document.getElementById('orderBookList');
    if (!list) return;

    const pageItems = appState.processedOrders;

    const grandTotal = pageItems.reduce((sum, order) => sum + parseFloat(order.total_value || 0), 0);
    document.getElementById('orderBookGrandTotal').textContent = grandTotal.toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
    document.getElementById('orderBookTotalCount').textContent = appState.orderBookTotal.toLocaleString('en-US');

    const badge = document.getElementById('orderBookTotalCountBadge');
    if (badge) badge.textContent = `${appState.orderBookTotal} found`;

    const totalPages = Math.ceil(appState.orderBookTotal / rowsPerPage);

    // Handle View All Button Visibility
    const viewAllBtn = document.getElementById('obViewAllBtn');
    if (viewAllBtn) {
        if (appState.orderBookTotal > 20 && rowsPerPage === 20) {
            viewAllBtn.classList.remove('hidden');
        } else {
            viewAllBtn.classList.add('hidden');
        }
    }

    if (pageItems.length === 0) {
        list.innerHTML = `
            <tr>
                <td colspan="8" class="py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center">
                            <svg class="w-7 h-7 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="font-bold text-slate-500">No orders match your filters</p>
                        <p class="text-xs text-slate-400">Try adjusting the filters above</p>
                    </div>
                </td>
            </tr>`;
    } else {
        list.innerHTML = pageItems.map(order => {
            const dateObj = new Date(order.order_date);
            const dateStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const timeStr = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            const address = order.customer_address || '—';
            const initial = (order.customer_name || '?').charAt(0).toUpperCase();

            // --- Enhanced Encoder Display ---
            const encoderDisp = order.encoder_display_name || order.encoded_by || 'Unknown';
            const encoderInitials = encoderDisp.substring(0, 2).toUpperCase();
            const encoderAvatar = order.encoder_avatar
                ? `<img src="${order.encoder_avatar}?v=${Date.now()}" class="w-full h-full object-cover">`
                : encoderInitials;

            // ---- Status / Invoiced & SO detection ----
            // Treat legacy 'deleted' rows as 'cancelled'
            const isCancelled = (order.status === 'cancelled' || order.status === 'deleted');
            let isInvoiced = false;
            let validSos = [];

            try {
                const soArr = order.so_number ? JSON.parse(order.so_number) : [];
                if (Array.isArray(soArr)) {
                    validSos = soArr.filter(s => s && String(s).trim().length > 0);
                }
            } catch (e) {
                if (order.so_number && order.so_number.trim().length > 0) {
                    validSos = [order.so_number.trim()];
                }
            }

            isInvoiced = validSos.length > 0;

            // Generate beautifully formatted SO numbers
            let soDisplay = '';
            if (validSos.length === 1) {
                soDisplay = `
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <span class="text-[10px] font-mono font-bold text-emerald-500">SO:</span>
                        <span class="text-[11px] font-mono font-bold text-slate-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100 shadow-sm">${validSos[0]}</span>
                    </div>`;
            } else if (validSos.length > 1) {
                const firstSo = validSos[0];
                const restCount = validSos.length - 1;
                const allSoList = validSos.map(so => `<div class="py-0.5 text-[11px] font-mono text-slate-600 font-medium whitespace-nowrap flex items-center gap-2"><div class="w-1 h-1 rounded-full bg-emerald-300"></div>${so}</div>`).join('');

                soDisplay = `
                    <details class="mt-1.5 group/so select-none">
                        <summary class="flex items-center gap-1.5 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                            <span class="text-[10px] font-mono font-bold text-emerald-500">SO:</span>
                            <div class="flex items-center gap-1">
                                <span class="text-[11px] font-mono font-bold text-slate-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100 shadow-sm">${firstSo}</span>
                                <span class="text-[9px] font-bold text-emerald-700 bg-emerald-100/60 hover:bg-emerald-200 px-1.5 py-0.5 rounded border border-emerald-200 transition-colors shadow-sm flex items-center gap-0.5" title="Click to view all SOs">
                                    +${restCount}
                                    <svg class="w-3 h-3 transition-transform duration-200 group-open/so:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </summary>
                        <div class="mt-2 pl-6 pb-1 relative animate-fadeIn">
                            <div class="absolute left-2.5 top-1 bottom-1 w-[1.5px] bg-gradient-to-b from-emerald-200 to-transparent"></div>
                            <div class="relative space-y-0.5">
                                ${allSoList}
                            </div>
                        </div>
                    </details>`;
            }

            // ★ Reorganized Vertical Buttons ★
            const viewSegment = `
                <a href="view_order.php?id=${order.id}&context=orderBook" target="_blank" title="View order"
                   data-id="${order.id}" 
                   class="view-order-link w-full px-2 py-1.5 text-[10px] font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded text-center uppercase tracking-wider transition-colors shadow-sm">
                    View
                </a>`;

            let middleSegment = '';
            let deleteSegment = '';
            if (window.userRole === 'admin') {
                if (isCancelled) {
                    middleSegment = `
                        <button data-id="${order.id}" data-po="${order.po_number}" title="Restore cancelled order"
                                class="uncancel-order-btn w-full px-2 py-1.5 text-[10px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 rounded text-center uppercase tracking-wider transition-colors shadow-sm">
                            Restore
                        </button>`;
                    deleteSegment = `
                        <button data-id="${order.id}" data-po="${order.po_number}" title="Delete order permanently"
                                class="delete-order-btn w-full px-2 py-1.5 text-[10px] font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded text-center uppercase tracking-wider transition-colors shadow-sm">
                            Delete
                        </button>`;
                } else {
                    middleSegment = `
                        <button data-id="${order.id}" data-po="${order.po_number}" title="Cancel order"
                                class="cancel-order-btn w-full px-2 py-1.5 text-[10px] font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 border border-orange-100 rounded text-center uppercase tracking-wider transition-colors shadow-sm">
                            Cancel
                        </button>`;
                    // No delete segment for active orders.
                }
            }

            const actionButtonGroup = `
                <div class="flex flex-col items-center gap-1.5 w-20 ml-auto">
                    ${viewSegment}${middleSegment}${deleteSegment}
                </div>`;

            const lastClickedId = sessionStorage.getItem('lastClickedOrderId');
            const isLastClicked = (lastClickedId == order.id);

            let rowClass = isCancelled
                ? 'group bg-red-50/40 hover:bg-red-50 transition-colors duration-150 border-l-4 border-l-red-400'
                : 'group hover:bg-indigo-50/30 transition-colors duration-150';

            if (isLastClicked) {
                rowClass += isCancelled
                    ? ' ring-2 ring-amber-400 ring-inset'
                    : ' bg-amber-50/80 border-l-4 border-l-amber-400 ring-1 ring-amber-400/50';
            }

            const statusBadge = isCancelled
                ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700 border border-red-200 shadow-sm">Cancelled</span>`
                : (isInvoiced
                    ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm">Encoded</span>`
                    : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-200 shadow-sm">Open</span>`);

            const cancelReasonChip = (isCancelled && order.cancel_reason)
                ? `<div class="mt-1.5 text-[10px] text-red-700 italic font-medium" title="Reason">Reason: ${order.cancel_reason}</div>`
                : '';

            return `
                <tr class="${rowClass}">
                    <td class="px-4 py-4 text-center">
                        <input type="checkbox" class="ob-row-checkbox rounded w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer transition-all shadow-sm" value="${order.id}">
                    </td>
                    <td class="px-6 py-4" data-label="Customer / PO">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white font-black text-sm flex-shrink-0 shadow-sm shadow-indigo-200">
                                ${initial}
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="font-bold text-slate-800 leading-tight ${isCancelled ? 'line-through text-slate-500' : ''}">${order.customer_name}</div>
                                    ${statusBadge}
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[10px] font-mono font-bold text-slate-400">PO:</span>
                                    <span class="text-xs font-bold ${isCancelled ? 'text-red-500' : 'text-indigo-600 group-hover:text-indigo-700'}">${order.po_number}</span>
                                </div>
                                ${soDisplay}
                                ${cancelReasonChip}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4" data-label="Address">
                        <div class="flex items-start gap-1.5 max-w-[160px]">
                            <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-[11px] text-slate-500 leading-snug" title="${order.customer_address || ''}">${address}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4" data-label="Date">
                        <div class="font-semibold text-slate-700 text-xs">${dateStr}</div>
                        <div class="flex items-center gap-1 mt-0.5 text-slate-400 text-[10px]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            ${timeStr}
                        </div>
                    </td>
                    <td class="px-6 py-4" data-label="Location / BU">
                        <div class="flex flex-col items-start gap-1.5">
                            <span class="flex items-center gap-1 text-[11px] font-bold text-slate-700">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                ${order.location}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-600 border border-indigo-100 shadow-sm">${order.bu}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4" data-label="Encoder">
                        <div class="flex items-center justify-center cursor-help" title="Encoded by: ${encoderDisp}">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-bold text-sm shadow-[0_3px_8px_rgba(228,34,120,0.3)] ring-2 ring-white overflow-hidden hover:scale-110 transition-transform duration-200">
                                ${encoderAvatar}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right" data-label="Total Value">
                        <div class="inline-flex flex-col items-end">
                            <span class="text-sm font-black text-slate-800 tabular-nums">${parseFloat(order.total_value || 0).toLocaleString('en-US', { style: 'currency', currency: 'PHP' })}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide mt-0.5">total</span>
                        </div>
                    </td>
                    <td class="px-6 py-4" data-label="Actions">
                        ${actionButtonGroup}
                    </td>
                </tr>
            `;
        }).join('');
    }

    document.getElementById('obPageInfo').textContent = `Page ${currentPage} of ${totalPages || 1}`;
    document.getElementById('obPrevBtn').disabled = currentPage <= 1;
    document.getElementById('obNextBtn').disabled = currentPage >= totalPages;

    // Reset Checkboxes & Bulk UI on page turn
    const selectAllCb = document.getElementById('obSelectAll');
    if (selectAllCb) selectAllCb.checked = false;
    const bulkDiv = document.getElementById('obBulkActions');
    if (bulkDiv) bulkDiv.classList.add('hidden');
}

export function populateOrderBookFilters() {
    const customerFilter = document.getElementById('obCustomerFilter');
    if (customerFilter) {
        customerFilter.innerHTML = '<option value="all">All Customers</option>' +
            appState.customers.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
    }
}

// --- NEW: Local Delete Logic using specific Modal ---

function openDeleteModal(orderId, poNumber) {
    orderIdToDelete = orderId;
    // Update modal text
    const displayId = poNumber ? `${poNumber} (ID: ${orderId})` : `#${orderId}`;
    document.getElementById('obDeleteModalId').textContent = displayId;
    // Show modal
    document.getElementById('obDeleteModal').classList.remove('hidden');
}

// --- NEW: Local Uncancel Logic ---
let orderIdToUncancel = null;

function openUncancelModal(orderId, poNumber) {
    orderIdToUncancel = orderId;
    const displayId = poNumber ? `${poNumber} (ID: ${orderId})` : `#${orderId}`;
    document.getElementById('obUncancelModalId').textContent = displayId;
    document.getElementById('obUncancelModal').classList.remove('hidden');
}

function closeUncancelModal() {
    document.getElementById('obUncancelModal').classList.add('hidden');
    orderIdToUncancel = null;
}

async function confirmUncancel() {
    if (!orderIdToUncancel) return;
    const btn = document.getElementById('obConfirmUncancelBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = "Restoring...";
    try {
        const result = await postData('uncancel_order', { order_id: orderIdToUncancel });
        if (result.success) {
            showMessage(result.message || 'Order restored.');
            closeUncancelModal();
            fetchOrderBookPage(currentPage);
        } else {
            showMessage(result.message || 'Failed to restore.', true);
        }
    } catch (e) {
        showMessage('Error communicating with server.', true);
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

// --- NEW: Local Cancel Logic ---
let orderIdToCancel = null;

function openCancelModal(orderId, poNumber) {
    orderIdToCancel = orderId;
    const displayId = poNumber ? `${poNumber} (ID: ${orderId})` : `#${orderId}`;
    document.getElementById('obCancelModalId').textContent = displayId;
    document.getElementById('obCancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('obCancelModal').classList.add('hidden');
    orderIdToCancel = null;
    document.getElementById('obCancelReason').value = 'Cancel date due'; // reset
    document.getElementById('obCancelReasonCustom').classList.add('hidden');
    document.getElementById('obCancelReasonCustom').value = '';
}

async function confirmCancel() {
    if (!orderIdToCancel) return;

    let reason = document.getElementById('obCancelReason').value;
    if (reason === 'Other') {
        reason = document.getElementById('obCancelReasonCustom').value.trim() || 'No reason provided';
    }

    const btn = document.getElementById('obConfirmCancelBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = "Canceling...";

    try {
        const result = await postData('cancel_order', { order_id: orderIdToCancel, reason: reason });
        if (result.success) {
            showMessage("Order cancelled successfully.");
            closeCancelModal();
            fetchOrderBookPage(currentPage);
        } else {
            showMessage(result.message || 'Failed to cancel.', true);
        }
    } catch (e) {
        showMessage('Error communicating with server.', true);
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

function closeDeleteModal() {
    document.getElementById('obDeleteModal').classList.add('hidden');
    orderIdToDelete = null;
}

async function confirmDelete() {
    if (!orderIdToDelete) return;

    const btn = document.getElementById('obConfirmDeleteBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = "Deleting...";

    try {
        // No reason needed for Hard Delete
        const result = await postData('delete_order', { order_id: orderIdToDelete });
        if (result.success) {
            showMessage(result.message);
            closeDeleteModal();
            fetchOrderBookPage(currentPage); // Refresh list
        } else {
            showMessage(result.message || 'Failed to delete.', true);
        }
    } catch (e) {
        console.error(e);
        showMessage('Error communicating with server.', true);
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

let searchTimeout = null;

export function initOrderBook() {
    // --- 1. FILTER LOGIC (WITH DEBOUNCE FIX) ---
    const selectFilterIds = ['obMonthFilter', 'obYearFilter', 'obLocFilter', 'obBuFilter', 'obCustomerFilter', 'obDaysFilter', 'obStatusFilter', 'obErrorFilter'];
    selectFilterIds.forEach(id => {
        document.getElementById(id)?.addEventListener('change', () => {
            rowsPerPage = 20; // Reset to 20 on filter change
            fetchOrderBookPage(1);
        });
    });

    const textFilterIds = ['obPoFilter', 'obSoFilter', 'obAddressFilter'];
    textFilterIds.forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                rowsPerPage = 20; // Reset to 20 on search
                fetchOrderBookPage(1);
            }, 400);
        });
    });

    // --- 2. PAGINATION LOGIC ---
    document.getElementById('obPrevBtn')?.addEventListener('click', () => {
        if (currentPage > 1) fetchOrderBookPage(currentPage - 1);
    });
    document.getElementById('obNextBtn')?.addEventListener('click', () => {
        const totalPages = Math.ceil(appState.orderBookTotal / rowsPerPage);
        if (currentPage < totalPages) fetchOrderBookPage(currentPage + 1);
    });

    document.getElementById('obViewAllBtn')?.addEventListener('click', () => {
        if (appState.orderBookTotal > 500) {
            alert(`Loading ${appState.orderBookTotal} records at once requires too much memory and may crash this device. Please use the search filters or export to CSV instead.`);
            return;
        }
        if (confirm(`Load all ${appState.orderBookTotal} records onto one page?`)) {
            rowsPerPage = 9999;
            fetchOrderBookPage(1);
        }
    });

    // --- 3. BUTTON CLICK LISTENERS (Cancel & Delete) ---
    document.getElementById('orderBookList')?.addEventListener('click', (e) => {
        const viewLink = e.target.closest('.view-order-link');
        if (viewLink) {
            sessionStorage.setItem('lastClickedOrderId', viewLink.dataset.id);

            // Instantly clear old highlights from other rows
            document.querySelectorAll('#orderBookList tr').forEach(tr => {
                tr.classList.remove('bg-amber-50/80', 'border-l-4', 'border-l-amber-400', 'ring-1', 'ring-amber-400/50', 'ring-2', 'ring-amber-400', 'ring-inset');
            });

            // Apply highlight to the currently clicked row
            const row = viewLink.closest('tr');
            if (row) {
                if (row.classList.contains('border-l-red-400')) {
                    row.classList.add('ring-2', 'ring-amber-400', 'ring-inset');
                } else {
                    row.classList.add('bg-amber-50/80', 'border-l-4', 'border-l-amber-400', 'ring-1', 'ring-amber-400/50');
                }
            }
            // Allow natural link navigation to continue
            return;
        }

        const deleteBtn = e.target.closest('.delete-order-btn');
        if (deleteBtn) {
            openDeleteModal(deleteBtn.dataset.id, deleteBtn.dataset.po);
            return;
        }

        const cancelBtn = e.target.closest('.cancel-order-btn');
        if (cancelBtn) {
            openCancelModal(cancelBtn.dataset.id, cancelBtn.dataset.po);
            return;
        }

        const uncancelBtn = e.target.closest('.uncancel-order-btn');
        if (uncancelBtn) {
            openUncancelModal(uncancelBtn.dataset.id, uncancelBtn.dataset.po);
            return;
        }
    });

    // --- 4. MODAL BUTTON LISTENERS ---
    document.getElementById('obCancelDeleteBtn')?.addEventListener('click', closeDeleteModal);
    document.getElementById('obConfirmDeleteBtn')?.addEventListener('click', confirmDelete);

    document.getElementById('obCloseCancelBtn')?.addEventListener('click', closeCancelModal);
    document.getElementById('obConfirmCancelBtn')?.addEventListener('click', confirmCancel);

    document.getElementById('obCancelReason')?.addEventListener('change', (e) => {
        const customInput = document.getElementById('obCancelReasonCustom');
        if (e.target.value === 'Other') {
            customInput.classList.remove('hidden');
            customInput.focus();
        } else {
            customInput.classList.add('hidden');
        }
    });

    document.getElementById('obCloseUncancelBtn')?.addEventListener('click', closeUncancelModal);
    document.getElementById('obConfirmUncancelBtn')?.addEventListener('click', confirmUncancel);

    // ============================================================
    // ★ ESCAPE ANIMATION TRAP: Move Modals to Body ★
    // ============================================================
    const deleteModal = document.getElementById('obDeleteModal');
    if (deleteModal && deleteModal.parentElement !== document.body) {
        document.body.appendChild(deleteModal);
    }
    const cancelModal = document.getElementById('obCancelModal');
    if (cancelModal && cancelModal.parentElement !== document.body) {
        document.body.appendChild(cancelModal);
    }
    const uncancelModal = document.getElementById('obUncancelModal');
    if (uncancelModal && uncancelModal.parentElement !== document.body) {
        document.body.appendChild(uncancelModal);
    }

    // --- 5. BULK SELECTION & ACTION LOGIC ---
    function updateBulkActionUI() {
        const checkboxes = document.querySelectorAll('.ob-row-checkbox');
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        const bulkDiv = document.getElementById('obBulkActions');
        const countSpan = document.getElementById('obSelectedCount');

        if (bulkDiv && countSpan) {
            if (checked.length > 0) {
                bulkDiv.classList.remove('hidden');
                countSpan.textContent = checked.length;
            } else {
                bulkDiv.classList.add('hidden');
            }
        }

        const selectAll = document.getElementById('obSelectAll');
        if (selectAll && checkboxes.length > 0) {
            selectAll.checked = checked.length === checkboxes.length;
        }
    }

    document.addEventListener('change', (e) => {
        if (e.target.id === 'obSelectAll') {
            const isChecked = e.target.checked;
            document.querySelectorAll('.ob-row-checkbox').forEach(cb => cb.checked = isChecked);
            updateBulkActionUI();
        } else if (e.target.classList.contains('ob-row-checkbox')) {
            updateBulkActionUI();
        }
    });

    document.getElementById('obBulkViewBtn')?.addEventListener('click', () => {
        const checked = Array.from(document.querySelectorAll('.ob-row-checkbox:checked')).map(cb => cb.value);
        checked.forEach(id => window.open(`view_order.php?id=${id}&context=orderBook`, '_blank'));
    });

    document.getElementById('obBulkPrintBtn')?.addEventListener('click', () => {
        const checked = Array.from(document.querySelectorAll('.ob-row-checkbox:checked')).map(cb => cb.value);
        if (checked.length === 0) return;

        // Bundle all IDs and send them to a single tab to bypass popup blockers
        const bulkIds = checked.join(',');
        window.open(`view_order.php?bulk_ids=${bulkIds}&context=orderBook`, '_blank');
    });

    document.getElementById('obBulkDeleteBtn')?.addEventListener('click', async () => {
        const checked = Array.from(document.querySelectorAll('.ob-row-checkbox:checked')).map(cb => cb.value);
        if (!checked.length) return;
        if (!confirm(`Are you sure you want to permanently delete ${checked.length} selected orders?`)) return;

        const reason = prompt("Enter a reason for deleting these orders:", "Bulk Deleted");
        if (reason === null) return;

        const btn = document.getElementById('obBulkDeleteBtn');
        const ogText = btn.innerText;
        btn.disabled = true; btn.innerText = 'Deleting...';

        for (const id of checked) await postData('delete_order', { order_id: id, reason: reason });

        btn.disabled = false; btn.innerText = ogText;
        fetchOrderBookPage(currentPage);
    });

    document.getElementById('obBulkCancelBtn')?.addEventListener('click', async () => {
        const checked = Array.from(document.querySelectorAll('.ob-row-checkbox:checked')).map(cb => cb.value);
        if (!checked.length) return;

        const reason = prompt("Enter a cancellation reason for these orders:", "Bulk Cancelled");
        if (reason === null) return;

        const btn = document.getElementById('obBulkCancelBtn');
        const ogText = btn.innerText;
        btn.disabled = true; btn.innerText = 'Canceling...';

        for (const id of checked) await postData('cancel_order', { order_id: id, reason: reason });

        btn.disabled = false; btn.innerText = ogText;
        fetchOrderBookPage(currentPage);
    });

    document.getElementById('obBulkDeductBtn')?.addEventListener('click', async () => {
        const checked = Array.from(document.querySelectorAll('.ob-row-checkbox:checked')).map(cb => cb.value);
        if (!checked.length) return;

        if (!confirm(`Are you sure you want to FORCE RE-DEDUCT stock for ${checked.length} selected orders?\n\nThis will subtract all served items from your inventory again. Use this only if the external system restored stocks you already shipped.`)) return;

        const btn = document.getElementById('obBulkDeductBtn');
        const ogText = btn.innerText;
        btn.disabled = true; btn.innerText = 'Deducting...';

        try {
            const result = await postData('bulk_deduct_stock', { order_ids: JSON.stringify(checked) });
            if (result.success) {
                showMessage(result.message);
                // Clear selection after deduction
                document.querySelectorAll('.ob-row-checkbox:checked').forEach(cb => cb.checked = false);
                document.getElementById('obBulkActions').classList.add('hidden');
                document.getElementById('obSelectAll').checked = false;
            } else {
                showMessage(result.message || 'Failed to deduct stock.', true);
            }
        } catch (e) {
            console.error(e);
            showMessage('Error communicating with server.', true);
        } finally {
            btn.disabled = false; btn.innerText = ogText;
        }
    });

}

