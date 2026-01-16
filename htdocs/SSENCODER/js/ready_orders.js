import { postData } from './api.js';
import { showLoader, hideLoader, showMessage } from './ui.js';

export async function fetchReadyOrders() {
    showLoader();
    try {
        const result = await postData('get_draft_orders', { location: 'all', bu: 'all' });
        if (result.success) {
            renderReadyOrders(result.data || []);
        }
    } catch (e) {
        console.error(e);
        showMessage('Error fetching ready orders.', true);
    } finally {
        hideLoader();
    }
}

function renderReadyOrders(items) {
    const list = document.getElementById('readyOrdersList');
    const emptyState = document.getElementById('readyOrdersEmptyState');
    if (!list) return;

    list.innerHTML = '';
    if (items.length === 0) {
        if (emptyState) emptyState.classList.remove('hidden');
        return;
    }
    if (emptyState) emptyState.classList.add('hidden');

    // Group items by Order ID
    const orders = {};
    items.forEach(item => {
        if (!orders[item.order_id]) {
            orders[item.order_id] = { ...item, items: [], allReady: true };
        }
        
        // Check if THIS item has enough stock
        const stock = parseInt(item.current_stock || 0);
        const qty = parseInt(item.quantity || 0);
        const isReady = stock >= qty;
        
        // If any item is not ready, the whole order isn't "Fully Ready"
        if (!isReady) orders[item.order_id].allReady = false;

        orders[item.order_id].items.push({ ...item, isReady, stock });
    });

    const html = Object.values(orders).map(order => {
        // Status Badge Logic
        let statusHtml = '';
        if (order.allReady) {
            statusHtml = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 animate-pulse">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span> Ready to Submit
                          </span>`;
        } else {
            statusHtml = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            Waiting for Stock
                          </span>`;
        }

        // Render Items
        const itemsHtml = order.items.map(i => `
            <div class="flex justify-between items-center py-1 text-xs border-b border-slate-50 last:border-0">
                <span class="text-slate-700 font-medium w-1/2 truncate" title="${i.description}">${i.description}</span>
                <span class="text-slate-500 w-16 text-right">${i.quantity} pcs</span>
                <span class="w-24 text-right ${i.isReady ? 'text-green-600 font-bold' : 'text-red-500'}">
                    ${i.isReady ? 'Stock: ' + i.stock : 'Stock: ' + i.stock}
                </span>
            </div>
        `).join('');

        return `
            <tr class="hover:bg-slate-50 transition-colors bg-white border-b border-slate-100">
                <td class="px-6 py-4 align-top w-1/4">
                    <div class="font-bold text-slate-800">${order.customer_name}</div>
                    <div class="text-xs text-slate-500 font-mono mt-1">PO: ${order.po_number}</div>
                    <div class="text-[10px] text-slate-400 mt-2">${new Date(order.order_date).toLocaleDateString()}</div>
                </td>
                <td class="px-6 py-4 align-top w-1/3">
                    <div class="bg-slate-50 rounded p-2 border border-slate-100">
                        ${itemsHtml}
                    </div>
                </td>
                <td class="px-6 py-4 align-middle text-center">
                    ${statusHtml}
                </td>
                <td class="px-6 py-4 align-middle text-right">
                    <a href="view_order.php?id=${order.order_id}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">
                        Process
                    </a>
                </td>
            </tr>
        `;
    }).join('');

    list.innerHTML = html;
}

export function initReadyOrders() {
    // 1. Add Listener to the Refresh Button
    document.getElementById('refreshReadyOrdersBtn')?.addEventListener('click', fetchReadyOrders);

    // 2. AUTO-LOAD Logic
    // Check if we are currently on the #readyOrders tab
    if (window.location.hash === '#readyOrders') {
        fetchReadyOrders();
    }

    // Listen for tab changes to auto-load when user clicks the tab later
    window.addEventListener('hashchange', () => {
        if (window.location.hash === '#readyOrders') {
            fetchReadyOrders();
        }
    });
}