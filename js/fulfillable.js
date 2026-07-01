import { appState } from './state.js';
import { postData } from './api.js';
import { showLoader, hideLoader, showMessage } from './ui.js';

export async function fetchFulfillableData() {
    showLoader();
    try {
        const filters = {
            location: document.getElementById('fulfillableLocFilter')?.value || 'all',
            customer: document.getElementById('fulfillableCustomerFilter')?.value || 'all',
            bu: document.getElementById('fulfillableBuFilter')?.value || 'all',
            month: document.getElementById('fulfillableMonthFilter')?.value || 'all',
            year: document.getElementById('fulfillableYearFilter')?.value || 'all'
        };

        const result = await postData('get_fulfillable_items', filters);

        if (result.success) {
            renderFulfillablePage(result.data || []);
        } else {
            showMessage(result.message || 'Failed to fetch fulfillable items.', true);
        }
    } catch (e) {
        console.error(e);
        showMessage('Error loading fulfillable items.', true);
    } finally {
        hideLoader();
    }
}

function renderFulfillablePage(items) {
    const list = document.getElementById('fulfillableList');
    const emptyState = document.getElementById('fulfillableEmptyState');
    if (!list) return;

    list.innerHTML = '';

    // Calculate Grand Total
    const grandTotal = items.reduce((sum, item) => {
        const product = appState.products[item.sku];
        const itemPrice = (product?.sales_price || 0) * item.quantity;
        return sum + itemPrice;
    }, 0);
    document.getElementById('fulfillableGrandTotal').textContent = grandTotal.toLocaleString('en-US', { style: 'currency', currency: 'PHP' });

    if (items.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
        return;
    }

    if (emptyState) emptyState.classList.add('hidden');

    // 1. Group Items by Order ID
    const orders = {};
    items.forEach(item => {
        if (!orders[item.order_id]) {
            orders[item.order_id] = {
                ...item, // Keep order metadata from the first item
                items: []
            };
        }
        orders[item.order_id].items.push(item);
    });

    // 2. Render Groups using Rowspan
    const html = Object.values(orders).map(order => {
        const rowSpan = order.items.length;

        return order.items.map((item, index) => {
            const isFirst = index === 0;

            // Cells that span multiple rows (Customer & Action)
            let parsedSO = 'None';
            if (order.so_number) {
                try {
                    let soArray = JSON.parse(order.so_number);
                    if (!Array.isArray(soArray)) soArray = [order.so_number];
                    let cleanSOs = soArray.filter(s => s && String(s).trim() !== '');
                    if (cleanSOs.length > 0) parsedSO = cleanSOs.join(', ');
                } catch (e) {
                    if (String(order.so_number).trim() !== '') parsedSO = order.so_number;
                }
            }

            const customerCell = isFirst ? `
                <td class="px-6 py-4 align-top border-r border-slate-100 bg-white" rowspan="${rowSpan}">
                    <div class="font-bold text-slate-800 text-sm">${order.customer_name}</div>
                    <div class="text-xs text-slate-500 font-mono mt-1">PO: ${order.po_number}</div>
                    <div class="text-xs text-slate-500 font-mono mt-0.5">SO: ${parsedSO}</div>
                    <div class="text-[10px] text-slate-400 mt-1 max-w-[200px] truncate">${order.customer_address || ''}</div>
                </td>` : '';

            const actionCell = isFirst ? `
                <td class="px-6 py-4 align-middle text-right bg-white border-l border-slate-100" rowspan="${rowSpan}">
                    <a href="view_order.php?id=${order.order_id}&context=fulfillable" target="_blank" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors whitespace-nowrap">
                        Process Order
                    </a>
                </td>` : '';

            // Item specific cells
            return `
                <tr class="hover:bg-slate-50 transition-colors ${isFirst ? 'border-t-2 border-slate-200' : 'border-t border-slate-50'}">
                    ${customerCell}
                    <td class="px-6 py-3 align-top">
                        <div class="font-medium text-slate-700 text-sm">${item.description}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Ordered: <span class="font-mono">${item.sku}</span></div>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 mt-1 border border-slate-200">
                            ${item.bu || 'N/A'}
                        </span>
                    </td>
                    <td class="px-6 py-3 align-top text-center">
                        <div class="text-sm font-bold text-slate-800">${parseInt(item.quantity).toLocaleString()}</div>
                        <div class="text-[10px] text-slate-400">pcs</div>
                    </td>
                    <td class="px-6 py-3 align-top">
                        <div class="font-bold text-green-700 text-sm">${parseInt(item.total_available_stock).toLocaleString()}</div>
                        <div class="text-[10px] text-slate-600 mt-0.5">
                            Use: <span class="font-mono font-bold bg-green-100 text-green-800 px-1 rounded">${item.available_sku || item.sku}</span>
                        </div>
                    </td>
                    ${actionCell}
                </tr>
            `;
        }).join('');
    }).join('');

    list.innerHTML = html;
}

export function populateFulfillableFilters() {
    const customerFilter = document.getElementById('fulfillableCustomerFilter');
    if (customerFilter) {
        customerFilter.innerHTML = '<option value="all">All Customers</option>' +
            appState.customers.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
    }
}

export function initFulfillable() {
    // Set the current month and year as the default selection
    const currentMonth = String(new Date().getMonth() + 1).padStart(2, '0');
    const currentYear = String(new Date().getFullYear());

    const monthDropdown = document.getElementById('fulfillableMonthFilter');
    const yearDropdown = document.getElementById('fulfillableYearFilter');

    if (monthDropdown) monthDropdown.value = currentMonth;
    if (yearDropdown) yearDropdown.value = currentYear;

    // Attach event listeners so it fetches data when any dropdown changes
    ['fulfillableLocFilter', 'fulfillableCustomerFilter', 'fulfillableBuFilter', 'fulfillableMonthFilter', 'fulfillableYearFilter'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', fetchFulfillableData);
    });

    document.getElementById('refreshFulfillableBtn')?.addEventListener('click', fetchFulfillableData);
}