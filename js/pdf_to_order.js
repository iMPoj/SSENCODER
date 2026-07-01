import { showLoader, hideLoader, showMessage, showConfirmation } from './ui.js';
import { postData, fetchData } from './api.js';

let parsedOrderData = {};
let localCustomers = [];

function updateRowPrice(row) {
    const qtyInput = row.querySelector('.item-quantity');
    const skuSelect = row.querySelector('.item-sku-select');
    const priceDisplay = row.querySelector('.item-price-display');
    const unitPriceDisplay = row.querySelector('.item-unit-price-display'); // Get the unit price element
    const selectedOption = skuSelect.options[skuSelect.selectedIndex];
    const discountPercent = parseFloat(document.getElementById('discountPercentage').value) || 0;

    const quantity = parseInt(qtyInput.value) || 0;
    const pdiff = parseFloat(row.dataset.pdiff || 0); // Inject the price difference
    const unitPrice = parseFloat(selectedOption?.dataset.price || 0) + pdiff;

    const grossLineTotal = quantity * unitPrice;
    const discountedLineTotal = grossLineTotal * (1 - discountPercent / 100);

    // Update Unit Price Display Visually
    if (unitPriceDisplay) {
        unitPriceDisplay.textContent = '@ ' + unitPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (pdiff !== 0) unitPriceDisplay.classList.add('text-blue-500', 'font-bold');
        else unitPriceDisplay.classList.remove('text-blue-500', 'font-bold');
    }

    priceDisplay.textContent = discountedLineTotal > 0 ? discountedLineTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
}

function updateSummary() {
    const list = document.getElementById('orderItemsList');
    const rows = list.querySelectorAll('tr.item-row');
    let totalDiscountedValue = 0;
    let itemCount = 0;

    rows.forEach((row, index) => {
        const rowNumberCell = row.querySelector('.row-number');
        if (rowNumberCell) rowNumberCell.textContent = index + 1;

        const qtyInput = row.querySelector('.item-quantity');
        const quantity = parseInt(qtyInput.value) || 0;
        const priceDisplay = row.querySelector('.item-price-display');

        // Include ALL items in the subtotal visual check (even unserved) so PO totals match
        if (quantity > 0) {
            const priceText = priceDisplay.textContent.replace(/[₱,]/g, '');
            totalDiscountedValue += parseFloat(priceText) || 0;
            itemCount++;
        }
    });

    document.getElementById('orderTotalDisplay').textContent = totalDiscountedValue.toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
    document.getElementById('summaryItemCount').textContent = `(${itemCount} items)`;
    document.getElementById('poItemCount').textContent = itemCount;
}

async function populateOrderItems(items, location) {
    const list = document.getElementById('orderItemsList');
    list.innerHTML = `<tr><td colspan="8" class="text-center py-4">Finding best SKUs for ${items.length} items...</td></tr>`;

    const itemPromises = items.map(item =>
        postData('find_product_with_best_sku', {
            term: item.vendorCode,
            location: location,
            quantity: item.quantity || 1
        })
    );
    const results = await Promise.all(itemPromises);

    const rowsHtml = results.map((result, index) => {
        return createItemRow(items[index], result.success ? result.data : null, index);
    }).join('');

    list.innerHTML = rowsHtml || `<tr><td colspan="8" class="text-center py-4">No items were parsed.</td></tr>`;

    list.querySelectorAll('tr.item-row').forEach(updateRowPrice);
    updateSummary();
}

function createItemRow(originalItem, apiData, index) {
    let optionsHtml = '<option value="">Not Found</option>';
    let bestSku = '';
    let description = `(Not Found) ${originalItem.description || ''}`;
    let barcode = 'No Barcode';
    let stockWarningDot = ''; // <-- 1. Setup our empty dot variable

    if (apiData && Array.isArray(apiData.allSkus)) {
        description = apiData.description;
        bestSku = apiData.bestSku;
        const barcodeObj = apiData.allSkus.find(c => c.type === 'barcode');
        if (barcodeObj) barcode = barcodeObj.code;

        // --- 2. CHECK STOCK FOR RED DOT START ---
        const bestSkuData = apiData.allSkus.find(s => s.code === bestSku);
        const currentStock = bestSkuData && bestSkuData.stock ? parseInt(bestSkuData.stock) : 0;

        if (currentStock <= 0) {
            stockWarningDot = `<span class="inline-block w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse shadow-[0_0_5px_rgba(239,68,68,0.8)] ml-2" title="Out of Stock (0)"></span>`;
        }
        // --- CHECK STOCK FOR RED DOT END ---

        optionsHtml = apiData.allSkus
            .filter(s => s.type === 'sku' && parseFloat(s.sales_price) > 0)
            .map(s => {
                const stock = s.stock ? parseInt(s.stock) : 0;
                return `<option value="${s.code}" data-price="${s.sales_price || 0}" ${s.code === bestSku ? 'selected' : ''}>
                            ${s.code} (Stock: ${stock.toLocaleString()})
                        </option>`;
            })
            .join('');
    } else {
        // If the item API data completely fails, show the red dot as a warning too
        stockWarningDot = `<span class="inline-block w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse shadow-[0_0_5px_rgba(239,68,68,0.8)] ml-2" title="Item Not Found in Database"></span>`;
    }

    // 3. Inject the dot into the description <p> tag and align it using flexbox
    return `
        <tr class="item-row" data-product-id="${apiData?.productId || ''}" data-pdiff="0" data-status="served">
            <td class="px-2 py-2 w-10 text-center cursor-move drag-handle text-slate-400">☰</td>
            <td class="px-2 py-2 text-center row-number">${index + 1}</td>
            <td class="px-4 py-2">
                <p class="font-medium text-slate-800 item-description flex items-center">${description} ${stockWarningDot}</p>
                <p class="font-mono text-xs text-slate-500 mt-1">${barcode}</p>
            </td>
            <td class="px-4 py-2">
                <select class="item-sku-select mt-1 block w-full rounded-md border-slate-300 shadow-sm text-xs">${optionsHtml}</select>
            </td>
            <td class="px-4 py-2"><input type="number" class="item-quantity w-20 rounded-md border-slate-300 shadow-sm text-sm" value="${originalItem.quantity || 1}"></td>
            <td class="px-4 py-2 w-16 item-unit">pc</td>
            <td class="px-4 py-2 text-right">
                <div class="item-unit-price-display text-[10px] text-slate-500 mb-0.5">@ 0.00</div>
                <div class="item-price-display font-medium text-slate-800">0.00</div>
            </td>
            <td class="px-4 py-2 text-right whitespace-nowrap">
                <button type="button" class="status-toggle-btn text-emerald-500 hover:text-emerald-700 font-bold mr-2 text-xs" title="Toggle Serve/Unserve">S</button>
                <button type="button" class="pdiff-btn text-blue-500 hover:text-blue-700 font-bold mr-2 text-xs" title="Price Difference per PC">PDiff</button>
                <button class="delete-item-btn text-red-500 hover:text-red-700 text-xs">✖</button>
            </td>
        </tr>`;
}

let orderQueue = [];
let currentQueueIndex = 0;

function setupEventListeners() {
    const list = document.getElementById('orderItemsList');

    list.addEventListener('input', (e) => {
        if (e.target.matches('.item-quantity, .item-sku-select')) {
            updateRowPrice(e.target.closest('tr'));
            updateSummary();
        }
    });

    list.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-item-btn')) {
            e.target.closest('tr').remove();
            updateSummary();
        }
        if (e.target.classList.contains('pdiff-btn')) {
            const row = e.target.closest('tr');
            let diff = prompt("Enter price difference per pc (e.g., -5.50 or 2.00):", row.dataset.pdiff || "0");
            if (diff !== null) {
                row.dataset.pdiff = parseFloat(diff) || 0;
                updateRowPrice(row);
                updateSummary();
            }
        }
        if (e.target.classList.contains('status-toggle-btn')) {
            const row = e.target.closest('tr');
            let current = row.dataset.status;
            let next = current === 'served' ? 'unserved' : 'served';

            row.dataset.status = next;
            e.target.textContent = next === 'served' ? 'S' : 'U';
            e.target.className = next === 'served'
                ? 'status-toggle-btn text-emerald-500 hover:text-emerald-700 font-bold mr-2 text-xs'
                : 'status-toggle-btn text-red-500 hover:text-red-700 font-bold mr-2 text-xs';

            row.style.opacity = next === 'unserved' ? '0.5' : '1';
            updateSummary();
        }
    });

    document.getElementById('discountPercentage').addEventListener('input', () => {
        document.querySelectorAll('#orderItemsList tr.item-row').forEach(updateRowPrice);
        updateSummary();
    });

    // --- NEW: Skip Button Logic ---
    document.getElementById('skipOrderBtn')?.addEventListener('click', () => {
        showConfirmation("Skip this order? It will not be saved.", () => {
            moveToNextOrderInQueue();
        });
    });

    document.getElementById('processOrderBtn').addEventListener('click', () => {
        const items = [];
        document.querySelectorAll('#orderItemsList tr.item-row').forEach(row => {
            const sku = row.querySelector('.item-sku-select').value;
            const quantity = row.querySelector('.item-quantity').value;
            const status = row.dataset.status || 'served'; // Capture the toggled status
            if (sku && parseInt(quantity) > 0) {
                items.push({
                    sku: sku,
                    description: row.querySelector('.item-description').textContent.trim(),
                    quantity: quantity,
                    status: status // Send it to the API
                });
            }
        });

        if (items.length === 0) return showMessage("Cannot process an empty order.", true);

        const finalOrderData = {
            customer_name: document.getElementById('customerName').value,
            customer_address: document.getElementById('customerAddress').value,
            po_number: document.getElementById('poNumber').value,
            location: document.getElementById('orderLocation').value,
            bu: document.getElementById('orderBu').value,
            discount: document.getElementById('discountPercentage').value,
            items: JSON.stringify(items)
        };

        showConfirmation("Process and save this order?", async () => {
            showLoader();
            const result = await postData('add_order', finalOrderData);
            hideLoader();
            if (result.success) {
                showMessage(`Order #${result.order_id} saved successfully!`);
                moveToNextOrderInQueue(); // Automatically progress the queue
            } else {
                showMessage(result.message || "Failed to process order.", true);
            }
        });
    });

    // --- SEARCH & SELECT LOGIC (Unchanged) ---
    const searchInput = document.getElementById('itemDescription');
    const suggestionsBox = document.getElementById('descriptionSuggestions');
    let selectedProduct = null;
    let currentSuggestions = [];

    searchInput.addEventListener('input', async () => {
        const term = searchInput.value.trim();
        if (term.length < 2) { suggestionsBox.classList.add('hidden'); return; }
        const result = await postData('get_product_suggestions', { term, bu: document.getElementById('orderBu').value });
        if (result.success && result.data.length > 0) {
            currentSuggestions = result.data;
            suggestionsBox.innerHTML = result.data.map((p, index) =>
                `<div class="p-2 hover:bg-slate-100 cursor-pointer suggestion-item" data-index="${index}">${p.description} <span class="text-xs text-slate-400">${p.sku}</span></div>`
            ).join('');
            suggestionsBox.classList.remove('hidden');
        } else { suggestionsBox.classList.add('hidden'); }
    });

    suggestionsBox.addEventListener('mousedown', (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item) {
            e.preventDefault();
            const index = item.dataset.index;
            if (currentSuggestions[index]) {
                selectedProduct = currentSuggestions[index];
                searchInput.value = selectedProduct.description;
                suggestionsBox.classList.add('hidden');
                document.getElementById('itemQuantity').focus();
            }
        }
    });

    searchInput.addEventListener('keydown', (e) => {
        if ((e.key === 'Tab' || e.key === 'Enter') && !suggestionsBox.classList.contains('hidden')) {
            if (currentSuggestions.length > 0) {
                e.preventDefault();
                selectedProduct = currentSuggestions[0];
                searchInput.value = selectedProduct.description;
                suggestionsBox.classList.add('hidden');
                document.getElementById('itemQuantity').focus();
            }
        }
    });

    document.getElementById('addItemBtn').addEventListener('click', async () => {
        if (!selectedProduct) return showMessage('Please search for and select a product first.', true);
        showLoader();
        const result = await postData('find_product_with_best_sku', {
            term: selectedProduct.id,
            location: document.getElementById('orderLocation').value,
            quantity: document.getElementById('itemQuantity').value
        });
        hideLoader();
        if (result.success) {
            const list = document.getElementById('orderItemsList');
            const rowCount = list.querySelectorAll('tr').length;
            const newRowHtml = createItemRow({ quantity: document.getElementById('itemQuantity').value }, result.data, rowCount);
            list.insertAdjacentHTML('beforeend', newRowHtml);
            const newRowElement = list.lastElementChild;
            if (newRowElement) { updateRowPrice(newRowElement); updateSummary(); }
            searchInput.value = ''; document.getElementById('itemQuantity').value = 1;
            selectedProduct = null; searchInput.focus();
        } else { showMessage(result.message || 'Could not add item.', true); }
    });
}

// --- NEW: Helper to navigate the Queue ---
function moveToNextOrderInQueue() {
    currentQueueIndex++;
    sessionStorage.setItem('pdfOrderIndex', currentQueueIndex);

    setTimeout(() => {
        if (currentQueueIndex < orderQueue.length) {
            window.location.reload(); // Reload page to show next order in queue
        } else {
            // Queue is finished!
            sessionStorage.removeItem('pdfOrderQueue');
            sessionStorage.removeItem('pdfOrderIndex');
            window.location.href = 'index.php#orderBook';
        }
    }, 1000);
}

async function initializePage() {
    setupEventListeners();

    const customersResult = await fetchData('get_customers');
    if (customersResult.success) {
        localCustomers = customersResult.data;
    } else {
        showMessage('Critical Error: Could not load customer data.', true);
        hideLoader();
        return;
    }

    // --- NEW: Read from Queue Array ---
    const queueJSON = sessionStorage.getItem('pdfOrderQueue');
    if (!queueJSON) {
        showMessage("No pending orders found. Redirecting to Admin.", true);
        setTimeout(() => { window.location.href = 'index.php#admin'; }, 2000);
        return;
    }

    orderQueue = JSON.parse(queueJSON);
    currentQueueIndex = parseInt(sessionStorage.getItem('pdfOrderIndex') || '0');

    // Failsafe: if somehow index gets out of bounds
    if (currentQueueIndex >= orderQueue.length) {
        sessionStorage.removeItem('pdfOrderQueue');
        sessionStorage.removeItem('pdfOrderIndex');
        window.location.href = 'index.php#orderBook';
        return;
    }

    // Load the CURRENT order from the queue
    parsedOrderData = orderQueue[currentQueueIndex];

    // --- NEW: Update Queue Banner UI ---
    if (orderQueue.length > 1) {
        document.getElementById('queueStatusBanner').classList.remove('hidden');
        document.getElementById('currentQueueIndex').textContent = currentQueueIndex + 1;
        document.getElementById('totalQueueCount').textContent = orderQueue.length;

        const processBtn = document.getElementById('processOrderBtn');
        if (currentQueueIndex < orderQueue.length - 1) {
            processBtn.textContent = "Process & Load Next Order ➔";
            processBtn.classList.replace('btn-primary', 'bg-indigo-600');
            processBtn.classList.add('text-white', 'hover:bg-indigo-700');
        } else {
            processBtn.textContent = "Process Final Order ✓";
        }
    }

    // Populate Fields
    document.getElementById('orderLocation').value = parsedOrderData.location || 'Davao';
    document.getElementById('orderBu').value = parsedOrderData.bu || '';
    document.getElementById('customerName').value = parsedOrderData.customerName || '';

    // Auto-fill PO Number if it's missing or '-'
    let poDisplay = parsedOrderData.poNumber || '';
    if (poDisplay === '-' || poDisplay === '') {
        poDisplay = `PO-${parsedOrderData.shipTo}`;
    }
    document.getElementById('poNumber').value = poDisplay;

    document.getElementById('customerAddress').value = parsedOrderData.shipTo || '';

    // --- APPLY DETECTED LOCATION FROM PARSER ---
    if (parsedOrderData.location) {
        const locationDropdown = document.getElementById('orderLocation');
        if (locationDropdown) {
            locationDropdown.value = parsedOrderData.location === 'Gensan' ? 'Gensan' : 'Davao';
        }
    }

    let customer = localCustomers.find(c => c.id == parsedOrderData.customerId);

    // Fallback: match by name if the ID from PDF parsing is missing or incorrect
    if (!customer && parsedOrderData.customerName) {
        customer = localCustomers.find(c => c.name.toLowerCase() === parsedOrderData.customerName.toLowerCase());
    }

    if (customer) {
        // 1. Prioritize their most recent discount from previous orders
        // 2. Fallback to their hardcoded default discount if they have no past orders
        const pastDiscount = parseFloat(customer.last_discount);
        const defaultDiscount = parseFloat(customer.default_discount); // Fixed missing variable

        if (!isNaN(pastDiscount) && pastDiscount > 0) {
            document.getElementById('discountPercentage').value = pastDiscount;
        } else if (!isNaN(defaultDiscount) && defaultDiscount > 0) {
            document.getElementById('discountPercentage').value = defaultDiscount;
        }
    }

    await populateOrderItems(parsedOrderData.items, parsedOrderData.location);

    new Sortable(document.getElementById('orderItemsList'), {
        animation: 150,
        handle: '.drag-handle',
        onEnd: () => updateSummary()
    });

    hideLoader();
}

document.addEventListener('DOMContentLoaded', initializePage);
