// FILE: js/order_book.js
import { appState } from './state.js';
import { postData } from './api.js';
import { showLoader, hideLoader, showMessage, showConfirmation } from './ui.js';

let currentPage = 1;
const ROWS_PER_PAGE = 20;

function handleDeleteOrder(orderId) {
    showConfirmation(`Are you sure you want to permanently delete Order #${orderId}? Stock for served items will be returned.`, async () => {
        showLoader();
        try {
            const result = await postData('delete_order', { order_id: orderId });
            if (result.success) {
                showMessage(result.message);
                fetchOrderBookPage(currentPage); // Refresh the list
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

export async function fetchOrderBookPage(page) {
    currentPage = page;
    showLoader();

    const filterData = {
        page: currentPage,
        limit: ROWS_PER_PAGE,
        month: document.getElementById('obMonthFilter').value,
        year: document.getElementById('obYearFilter').value,
        po_number: document.getElementById('obPoFilter').value,
        address: document.getElementById('obAddressFilter').value,
        location: document.getElementById('obLocFilter').value,
        bu: document.getElementById('obBuFilter').value,
        customer: document.getElementById('obCustomerFilter').value,
        so_number: document.getElementById('obSoFilter').value,
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
    } catch(e) {
        showMessage('An error occurred while fetching the order book.', true);
    } finally {
        hideLoader();
    }
}

export function renderOrderBookPage() {
    const list = document.getElementById('orderBookList');
    if (!list) return;

    const pageItems = appState.processedOrders;
    
    const grandTotal = pageItems.reduce((sum, order) => sum + parseFloat(order.total_value || 0), 0);
    document.getElementById('orderBookGrandTotal').textContent = grandTotal.toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
    
    // --- NEW: Update Total Orders Count ---
    document.getElementById('orderBookTotalCount').textContent = appState.orderBookTotal.toLocaleString('en-US');

    const totalPages = Math.ceil(appState.orderBookTotal / ROWS_PER_PAGE);

    if (pageItems.length === 0) {
        list.innerHTML = `<tr><td colspan="5" class="text-center py-12 text-slate-400 italic">No orders found matching your filters.</td></tr>`;
    } else {
        list.innerHTML = pageItems.map(order => {
            const deleteButton = window.userRole === 'admin' 
                ? `<button data-id="${order.id}" class="delete-order-btn text-red-500 hover:text-red-700 font-medium text-xs ml-3 px-2 py-1 rounded border border-red-200 hover:bg-red-50">Delete</button>` 
                : '';

            return `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800">${order.customer_name}</div>
                        <div class="text-xs text-slate-500 mt-0.5 font-mono">${order.po_number}</div>
                        <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]" title="${order.customer_address || ''}">${order.customer_address || 'No address'}</div>
                    </td>
                    <td class="px-6 py-4 text-slate-600">
                        ${new Date(order.order_date).toLocaleDateString()}
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-700">${order.location}</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 mt-1">
                            ${order.bu}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold text-slate-800">
                        ${parseFloat(order.total_value || 0).toLocaleString('en-US', { style: 'currency', currency: 'PHP' })}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="view_order.php?id=${order.id}&context=orderBook" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm hover:underline">View</a>
                        ${deleteButton}
                    </td>
                </tr>
            `;
        }).join('');
    }

    document.getElementById('obPageInfo').textContent = `Page ${currentPage} of ${totalPages || 1}`;
    document.getElementById('obPrevBtn').disabled = currentPage <= 1;
    document.getElementById('obNextBtn').disabled = currentPage >= totalPages;
}

export function populateOrderBookFilters() {
    const customerFilter = document.getElementById('obCustomerFilter');
    if (customerFilter) {
        customerFilter.innerHTML = '<option value="all">All Customers</option>' + 
            appState.customers.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
    }
}

export function initOrderBook() {
    const filterIds = [
        'obMonthFilter', 'obYearFilter', 'obPoFilter', 'obSoFilter', 
        'obAddressFilter', 'obLocFilter', 'obBuFilter', 'obCustomerFilter'
    ];
    filterIds.forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => fetchOrderBookPage(1));
    });
    
    document.getElementById('obPrevBtn')?.addEventListener('click', () => {
        if (currentPage > 1) fetchOrderBookPage(currentPage - 1);
    });
    document.getElementById('obNextBtn')?.addEventListener('click', () => {
        const totalPages = Math.ceil(appState.orderBookTotal / ROWS_PER_PAGE);
        if (currentPage < totalPages) fetchOrderBookPage(currentPage + 1);
    });

    document.getElementById('orderBookList')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-order-btn')) {
            const orderId = e.target.dataset.id;
            handleDeleteOrder(orderId);
        }
    });
}