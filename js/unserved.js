// FILE: js/unserved.js
import { appState } from './state.js';
import { postData } from './api.js';
import { showLoader, hideLoader, showMessage, showConfirmation } from './ui.js';

let currentPage = 1;
const ROWS_PER_PAGE = 50;

// ✅ ADD: Helper function for the delete action
function handleDeleteOrder(orderId) {
    showConfirmation(`Are you sure you want to permanently delete Order #${orderId}? Stock for served items will be returned.`, async () => {
        showLoader();
        try {
            const result = await postData('delete_order', { order_id: orderId });
            if (result.success) {
                showMessage(result.message);
                fetchUnservedPage(currentPage); // Refresh the unserved list
            } else {
                showMessage(result.message || 'Failed to delete order.', true);
            }
        } catch (e) {
            showMessage('An error occurred while deleting the order.', true);
        } finally {
            hideLoader();
        }
    });
}


export async function fetchUnservedPage(page) {
    currentPage = page;
    showLoader();

    const filterData = {
        page: currentPage,
        limit: ROWS_PER_PAGE,
        month: document.getElementById('unMonthFilter').value, // ADDED
        year: document.getElementById('unYearFilter').value,   // ADDED
        location: document.getElementById('unLocFilter').value,
        bu: document.getElementById('unBuFilter').value,
        customer: document.getElementById('unCustomerFilter').value,
        sku: document.getElementById('unSkuFilter').value,
    };

    try {
        const result = await postData('get_unserved_orders', filterData);
        if (result.success) {
            // Note: Using a different state variable to avoid conflicts with order book
            appState.unservedOrders = result.data || [];
            appState.unservedTotal = result.total_orders || 0;
            renderUnservedPage();
        } else {
            showMessage(result.message || 'Failed to fetch unserved orders.', true);
        }
    } catch(e) {
        showMessage('An error occurred while fetching unserved orders.', true);
    } finally {
        hideLoader();
    }
}
export function renderUnservedPage() {
    const list = document.getElementById('unservedList');
    if (!list) return;

    const pageItems = appState.unservedOrders || [];

    const grandTotalUnserved = pageItems.reduce((sum, order) => {
        const orderUnservedValue = order.items.reduce((itemSum, item) => {
            return item.status === 'unserved' ? itemSum + parseFloat(item.price) : itemSum;
        }, 0);
        return sum + orderUnservedValue;
    }, 0);
    document.getElementById('unservedGrandTotal').textContent = grandTotalUnserved.toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
    
    const totalPages = Math.ceil(appState.unservedTotal / ROWS_PER_PAGE);

    if (pageItems.length === 0) {
        list.innerHTML = `
            <tr>
                <td colspan="5" class="py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                            <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="font-bold text-slate-500">No unserved orders match your filters</p>
                        <p class="text-xs text-slate-400">Try adjusting the filters above</p>
                    </div>
                </td>
            </tr>`;
    } else {
        list.innerHTML = pageItems.map(order => {
            const unservedItems = order.items.filter(i => i.status === 'unserved');
            const unservedValue = unservedItems.reduce((sum, item) => sum + parseFloat(item.price || 0), 0);
            if (unservedValue === 0) return '';

            const address = order.customer.address || order.customer.ship_to || order.customer.shippingAddress || '—';
            const unservedCount = unservedItems.length;

            // ★ Action button-group (View + Delete) — uniform, aligned, no wrap
            const viewSegment = `
                <a href="view_order.php?id=${order.id}&context=unserved" title="View order"
                   class="px-3 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-50 inline-flex items-center gap-1.5 whitespace-nowrap transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>View</span>
                </a>`;
            const deleteSegment = window.userRole === 'admin' ? `
                <button data-id="${order.id}" title="Delete order permanently"
                        class="delete-order-btn px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 inline-flex items-center gap-1.5 whitespace-nowrap transition-colors">
                    <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Delete</span>
                </button>` : '';
            const actionButtonGroup = `
                <div class="inline-flex items-stretch rounded-xl overflow-hidden border border-slate-200 bg-white shadow-sm divide-x divide-slate-200">
                    ${viewSegment}${deleteSegment}
                </div>`;

            // ★ Items panel (dropdown row)
            const itemsRows = unservedItems.map(it => `
                <tr class="bg-white">
                    <td class="px-3 py-2 text-[11px] font-mono font-bold text-slate-700">${it.sku || '—'}</td>
                    <td class="px-3 py-2 text-[11px] text-slate-600">${it.description || it.name || '—'}</td>
                    <td class="px-3 py-2 text-[11px] text-right font-bold text-slate-700 tabular-nums">${parseInt(it.quantity || 0)}</td>
                    <td class="px-3 py-2 text-[11px] text-right font-bold text-red-600 tabular-nums">${parseFloat(it.price || 0).toLocaleString('en-US', { style: 'currency', currency: 'PHP' })}</td>
                </tr>`).join('');

            const itemsPanel = `
                <tr class="items-panel" data-order-id="${order.id}">
                    <td colspan="5" class="px-6 py-3 bg-red-50/30 border-l-4 border-l-red-300">
                        <div class="rounded-lg border border-red-100 bg-white overflow-hidden shadow-inner">
                            <div class="px-3 py-2 bg-red-50/60 border-b border-red-100 flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest text-red-700 flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                                    ${unservedCount} Unserved item${unservedCount === 1 ? '' : 's'}
                                </span>
                                <span class="text-[10px] font-bold text-slate-400">Order #${order.id} · PO ${order.customer.poNumber}</span>
                            </div>
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50/60 border-b border-slate-100">
                                        <th class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-slate-400">SKU</th>
                                        <th class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-slate-400">Description</th>
                                        <th class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Qty</th>
                                        <th class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Value</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">${itemsRows}</tbody>
                            </table>
                        </div>
                    </td>
                </tr>`;

            return `
                <tr class="group hover:bg-red-50/30 transition-colors duration-150" data-order-id="${order.id}">
                    <td class="px-6 py-4" data-label="Customer / PO">
                        <div class="flex items-start gap-3">
                            <button class="row-toggle-btn w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 border border-red-100 flex items-center justify-center flex-shrink-0 transition-colors mt-1" data-order-id="${order.id}" aria-expanded="false" title="Show / hide unserved items">
                                <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-500 to-rose-400 flex items-center justify-center text-white font-black text-sm flex-shrink-0 shadow-sm shadow-red-200">
                                ${order.customer.name.charAt(0).toUpperCase()}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-800 leading-tight">${order.customer.name}</div>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <span class="po-line inline-flex items-center gap-1">
                                        <span class="text-[10px] font-mono font-bold text-slate-400">PO:</span>
                                        <span class="text-xs font-bold text-slate-600">${order.customer.poNumber}</span>
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-red-100 text-red-700 text-[9px] font-black uppercase tracking-wide">${unservedCount} unserved</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4" data-label="Address">
                        <div class="flex items-start gap-1.5 max-w-[200px]">
                            <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-xs text-slate-500 leading-snug">${address}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4" data-label="Location / BU">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-700">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                ${order.location}
                            </span>
                            <span class="inline-flex w-fit items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">${order.bu}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right" data-label="Unserved Value">
                        <div class="inline-flex flex-col items-end">
                            <span class="text-base font-black text-red-600 tabular-nums">${unservedValue.toLocaleString('en-US', { style: 'currency', currency: 'PHP' })}</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wide">unserved</span>
                        </div>
                    </td>
                    <td class="px-6 py-4" data-label="Actions">
                        <div class="flex items-center justify-center">
                            ${actionButtonGroup}
                        </div>
                    </td>
                </tr>
                ${itemsPanel}
            `;
        }).join('');
    }

    document.getElementById('unPageInfo').textContent = `Page ${currentPage} of ${totalPages || 1}`;
    document.getElementById('unPrevBtn').disabled = currentPage <= 1;
    document.getElementById('unNextBtn').disabled = currentPage >= totalPages;
}

export function populateUnservedFilters() {
    const customerFilter = document.getElementById('unCustomerFilter');
    if (customerFilter) {
        customerFilter.innerHTML = '<option value="all">All Customers</option>' + 
            appState.customers.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
    }
}

export function initUnservedPage() {
    // ADDED unMonthFilter and unYearFilter to this list
    ['unMonthFilter', 'unYearFilter', 'unLocFilter', 'unBuFilter', 'unCustomerFilter', 'unSkuFilter'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => fetchUnservedPage(1));
    });
    
    document.getElementById('unPrevBtn')?.addEventListener('click', () => {
        if (currentPage > 1) fetchUnservedPage(currentPage - 1);
    });

    document.getElementById('unNextBtn')?.addEventListener('click', () => {
        const totalPages = Math.ceil(appState.unservedTotal / ROWS_PER_PAGE);
        if (currentPage < totalPages) fetchUnservedPage(currentPage + 1);
    });

    // ✅ Event delegation for delete + row-toggle (expand/collapse items panel)
    document.getElementById('unservedList')?.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.delete-order-btn');
        if (deleteBtn) {
            const orderId = deleteBtn.dataset.id;
            handleDeleteOrder(orderId);
            return;
        }
        const toggleBtn = e.target.closest('.row-toggle-btn');
        if (toggleBtn) {
            const orderId = toggleBtn.dataset.orderId;
            const panel = document.querySelector(`.items-panel[data-order-id="${orderId}"]`);
            if (panel) {
                const isOpen = panel.classList.toggle('open');
                toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
        }
    });

    // ★ NEW: Hide PO numbers toggle
    document.getElementById('unHidePoToggle')?.addEventListener('change', (e) => {
        const list = document.getElementById('unservedList');
        if (!list) return;
        list.classList.toggle('po-hidden', e.target.checked);
    });

    // ★ NEW: Expand all unserved items toggle
    document.getElementById('unExpandAllToggle')?.addEventListener('change', (e) => {
        const list = document.getElementById('unservedList');
        if (!list) return;
        list.classList.toggle('expand-all', e.target.checked);
        // Also flip the chevron arrows on the per-row buttons for visual consistency
        list.querySelectorAll('.row-toggle-btn').forEach(btn => {
            btn.setAttribute('aria-expanded', e.target.checked ? 'true' : 'false');
        });
    });
}