import { appState } from './state.js';
import { postData } from './api.js';
import { showLoader, hideLoader, showMessage, showConfirmation } from './ui.js';

// --- MAIN EXPORTED FUNCTIONS ---

export function initReadyOrders() {
    // Listeners for filters
    const filterIds = ['readyLocFilter', 'readyBuFilter'];
    filterIds.forEach(id => {
        document.getElementById(id)?.addEventListener('change', fetchReadyOrdersData);
    });

    // Refresh Button
    document.getElementById('refreshReadyOrdersBtn')?.addEventListener('click', fetchReadyOrdersData);

    // Event Delegation for Table Actions
    const list = document.getElementById('readyOrdersList');
    if (list) {
        list.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const orderId = btn.dataset.id;
            
            if (btn.classList.contains('delete-draft-btn')) {
                handleDeleteDraft(orderId);
            } else if (btn.classList.contains('post-draft-btn')) {
                const orderData = JSON.parse(decodeURIComponent(btn.dataset.order));
                handlePostDraft(orderData);
            } else if (btn.classList.contains('edit-draft-btn')) {
                // Optional: Redirect to encoder page if you support editing drafts there
                window.location.href = `create_order.php?edit_order=${orderId}`; 
            }
        });
    }
}

export async function fetchReadyOrdersData() {
    const location = document.getElementById('readyLocFilter').value;
    const bu = document.getElementById('readyBuFilter').value;

    showLoader();

    const payload = {
        location: location,
        bu: bu
    };

    try {
        // We reuse 'get_draft_orders' from your API
        // It returns a list of ITEMS, not orders. We must group them.
        const result = await postData('get_draft_orders', payload);
        
        if (result.success) {
            renderReadyOrdersTable(result.data);
        } else {
            showMessage(result.message || "Failed to load drafts", true);
        }
    } catch (e) {
        console.error(e);
        showMessage("Error fetching drafts", true);
    } finally {
        hideLoader();
    }
}

// --- INTERNAL HELPERS ---

function renderReadyOrdersTable(items) {
    const tbody = document.getElementById('readyOrdersList');
    const countBadge = document.getElementById('readyOrdersCountBadge');
    
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        countBadge.innerText = "0 Drafts";
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-12">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-bold text-gray-400 uppercase tracking-wide">No drafts found.</span>
                    </div>
                </td>
            </tr>`;
        return;
    }

    // --- GROUPING LOGIC ---
    // The API returns rows of ITEMS. We need to group them by Order ID.
    const orders = {};
    items.forEach(item => {
        if (!orders[item.order_id]) {
            orders[item.order_id] = {
                order_id: item.order_id,
                po_number: item.po_number,
                order_date: item.order_date,
                customer_name: item.customer_name,
                location: item.location,
                bu: item.bu,
                customer_address: item.customer_address,
                items: []
            };
        }
        orders[item.order_id].items.push(item);
    });

    const orderArray = Object.values(orders);
    countBadge.innerText = `${orderArray.length} Drafts`;

    orderArray.forEach(order => {
        const row = document.createElement('tr');
        row.className = "hover:bg-amber-50/50 transition-colors group border-b border-gray-50";
        
        // Analyze Stock for this Order
        let canFulfillAll = true;
        let itemsHtml = `<div class="space-y-1">`;
        
        order.items.forEach(i => {
            const stock = parseFloat(i.current_stock || 0);
            const qty = parseFloat(i.quantity);
            const hasStock = stock >= qty;
            if(!hasStock) canFulfillAll = false;

            const color = hasStock ? 'text-emerald-600' : 'text-red-500 font-bold';
            
            itemsHtml += `
                <div class="text-[10px] grid grid-cols-12 gap-2 items-center">
                    <span class="col-span-3 font-mono text-gray-500">${i.sku}</span>
                    <span class="col-span-6 truncate" title="${i.description}">${i.description}</span>
                    <span class="col-span-3 text-right ${color}">${qty} / <span class="text-gray-400">${stock}</span></span>
                </div>
            `;
        });
        itemsHtml += `</div>`;

        // Status Badge
        const statusBadge = canFulfillAll 
            ? `<span class="badge-pill bg-emerald-100 text-emerald-700 border-emerald-200">Ready to Post</span>`
            : `<span class="badge-pill bg-red-100 text-red-700 border-red-200">Insufficient Stock</span>`;

        // Encode order for the button
        const orderString = encodeURIComponent(JSON.stringify(order));

        row.innerHTML = `
            <td class="px-6 py-4 align-top">
                <div class="font-bold text-[#0D111A] text-xs">${order.po_number || 'No PO'}</div>
                <div class="font-mono text-[10px] text-[#6B7280] mt-1">${new Date(order.order_date).toLocaleDateString()}</div>
                <div class="mt-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">${order.location} | ${order.bu}</div>
            </td>
            <td class="px-6 py-4 align-top">
                <div class="font-bold text-[#0D111A] text-xs">${order.customer_name}</div>
                <div class="text-[10px] text-[#6B7280] mt-1 truncate max-w-[150px]" title="${order.customer_address}">${order.customer_address}</div>
            </td>
            <td class="px-6 py-4 align-top min-w-[250px]">
                ${itemsHtml}
            </td>
            <td class="px-6 py-4 align-top text-center">
                ${statusBadge}
            </td>
            <td class="px-6 py-4 align-top text-right space-y-2">
                <button class="post-draft-btn btn-primary !py-1.5 !px-3 !text-[10px] !w-full !bg-gradient-to-r !from-emerald-600 !to-emerald-500 shadow-emerald-200" data-order="${orderString}">
                    POST ORDER
                </button>
                <div class="flex gap-2 justify-end">
                    <a href="create_order.php?edit_id=${order.order_id}" class="text-xs font-bold text-blue-600 hover:text-blue-800 uppercase tracking-wider border border-blue-200 rounded px-2 py-1 bg-blue-50">Edit</a>
                    <button class="delete-draft-btn text-xs font-bold text-red-500 hover:text-red-700 uppercase tracking-wider border border-red-200 rounded px-2 py-1 bg-red-50" data-id="${order.order_id}">Del</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function handleDeleteDraft(orderId) {
    showConfirmation(
        `Are you sure you want to delete this draft?`,
        async () => {
            showLoader();
            try {
                // Delete draft uses the same logic as deleting an order
                const result = await postData('delete_order', { order_id: orderId });
                if (result.success) {
                    showMessage("Draft deleted.");
                    fetchReadyOrdersData();
                } else {
                    showMessage(result.message || "Failed to delete.", true);
                }
            } catch (e) {
                console.error(e);
                showMessage("Error deleting draft.", true);
            } finally {
                hideLoader();
            }
        }
    );
}

function handlePostDraft(order) {
    showConfirmation(
        `<b>Post this Order?</b><br><br>
         PO: ${order.po_number}<br>
         Customer: ${order.customer_name}<br><br>
         <span class="text-xs text-gray-500">This will check stock and move items to 'Served' or 'Unserved'.</span>`,
        async () => {
            showLoader();
            try {
                // We construct the payload to update order items
                // This converts the 'draft' status to actual 'served'/'unserved' based on stock logic
                // The PHP backend 'update_order_items' handles stock deduction if status becomes 'served'
                
                const newItems = order.items.map(item => {
                    const stock = parseFloat(item.current_stock || 0);
                    const qty = parseFloat(item.quantity);
                    // Determine new status
                    const status = (stock >= qty) ? 'served' : 'unserved';
                    
                    return {
                        id: item.id || item.item_id, // Important: pass the order_item ID
                        sku: item.sku,
                        description: item.description,
                        quantity: qty,
                        price: item.price,
                        status: status
                    };
                });

                const payload = {
                    order_id: order.order_id,
                    items: JSON.stringify(newItems),
                    so_numbers: '[]',
                    restore_stock: 'false' // false because we are not returning stock, we are consuming it
                };

                const result = await postData('update_order_items', payload);
                if (result.success) {
                    showMessage("Order Posted Successfully!");
                    fetchReadyOrdersData();
                } else {
                    showMessage(result.message || "Failed to post order.", true);
                }
            } catch (e) {
                console.error(e);
                showMessage("Error posting order.", true);
            } finally {
                hideLoader();
            }
        }
    );
}