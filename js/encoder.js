import { appState } from './state.js';
import { postData, fetchData } from './api.js';
import { showLoader, hideLoader, showMessage, showConfirmation } from './ui.js';

let editingItemIndex = null;
let selectedLocation = '';
let debounceTimer;
let currentItemData = null;

// --- INITIALIZATION AND STATE MANAGEMENT ---

async function init() {
    showLoader();

    // Give the browser 50ms to actually paint the loading animation on the screen 
    // before we lock up the CPU with heavy data parsing.
    await new Promise(resolve => setTimeout(resolve, 50));

    await Promise.all([
        // CACHE PRODUCTS
        (async () => {
            const cachedProducts = sessionStorage.getItem('app_products');
            if (cachedProducts) {
                appState.products = JSON.parse(cachedProducts);
                return;
            }
            const res = await fetchData('get_products');
            if (res.success && Array.isArray(res.data)) {
                appState.products = {};
                res.data.forEach(p => {
                    if (Array.isArray(p.codes)) {
                        p.codes.forEach(s => {
                            appState.products[s.code] = {
                                productId: p.id,
                                description: p.description,
                                bu: p.bu,
                                inventory: s.inventory || [],
                                sales_price: s.sales_price,
                                pieces_per_case: s.pieces_per_case,
                                type: s.type
                            };
                        });
                    }
                });
                try { sessionStorage.setItem('app_products', JSON.stringify(appState.products)); } catch (e) { }
            }
        })(),

        // CACHE CUSTOMERS
        (async () => {
            const cachedCustomers = sessionStorage.getItem('app_customers');
            if (cachedCustomers) {
                appState.customers = JSON.parse(cachedCustomers);
                return;
            }
            const res = await fetchData('get_customers');
            if (res.success && Array.isArray(res.data)) {
                appState.customers = res.data;
                try { sessionStorage.setItem('app_customers', JSON.stringify(res.data)); } catch (e) { }
            }
        })()
    ]);

    hideLoader();
    setupEventListeners();
    loadOrderFromStorage();
}

function updateFormState() {
    const locationSelected = document.getElementById('orderLocation').value;
    const buSelected = document.getElementById('orderBu').value;
    const isDisabled = !locationSelected || !buSelected;

    // Disable/enable item entry inputs based on location/BU selection
    const itemInputs = [
        document.getElementById('itemBarcode'),
        document.getElementById('itemDescription'),
        document.getElementById('itemQuantity'),
        document.getElementById('itemUnit'),
        document.getElementById('itemSkuSelect'),
        document.getElementById('addItemBtn')
    ];

    itemInputs.forEach(input => {
        if (input) {
            input.disabled = isDisabled;
            if (isDisabled) {
                input.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                input.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    });

    if (!isDisabled) {
        localStorage.setItem('defaultLocation', locationSelected);
    }
    selectedLocation = locationSelected;
}

function saveOrderToStorage() {
    const orderDraft = {
        orderLocation: selectedLocation,
        orderBu: document.getElementById('orderBu').value,
        customerName: document.getElementById('customerName').value,
        poNumber: document.getElementById('poNumber').value,
        address: document.getElementById('customerAddress').value,
        discount: document.getElementById('discountPercentage').value,
        items: appState.orderItems,
        selectedCustomer: appState.selectedCustomer
    };
    sessionStorage.setItem('currentOrderDraft', JSON.stringify(orderDraft));
}

function loadOrderFromStorage() {
    const draftJSON = sessionStorage.getItem('currentOrderDraft');
    if (!draftJSON) {
        document.getElementById('orderLocation').value = localStorage.getItem('defaultLocation') || '';
        updateFormState();
        return;
    }
    try {
        const draft = JSON.parse(draftJSON);
        if (!draft) return;
        document.getElementById('customerName').value = draft.customerName || '';
        document.getElementById('poNumber').value = draft.poNumber || '';
        document.getElementById('customerAddress').value = draft.address || '';
        document.getElementById('discountPercentage').value = draft.discount || '';
        document.getElementById('orderBu').value = draft.orderBu || '';
        document.getElementById('orderLocation').value = draft.orderLocation || '';
        appState.orderItems = Array.isArray(draft.items) ? draft.items : [];
        appState.selectedCustomer = draft.selectedCustomer || null;
        updateOrderSummary();
        updateFormState();
    } catch (e) {
        console.error("Failed to load draft", e);
        sessionStorage.removeItem('currentOrderDraft');
    }
}

function resetEncoderState() {
    appState.selectedCustomer = null;
    appState.orderItems = [];

    document.getElementById('orderBu').value = '';
    document.getElementById('customerName').value = '';
    document.getElementById('discountPercentage').value = '';
    document.getElementById('customerAddress').value = '';
    document.getElementById('poNumber').value = '';
    document.getElementById('customerCode').value = '';

    stopEditingItem();
    updateOrderSummary();
    sessionStorage.removeItem('currentOrderDraft');
    document.getElementById('orderLocation').value = localStorage.getItem('defaultLocation') || '';
    updateFormState();
}

function setupAutocomplete(inputId, suggestionsId, onSelect, sourceFunction) {
    const input = document.getElementById(inputId);
    const suggestionsBox = document.getElementById(suggestionsId);

    if (!input || !suggestionsBox) return;

    input.addEventListener('input', () => {
        const term = input.value.trim().toLowerCase();

        if (term.length === 0) {
            suggestionsBox.classList.add('hidden');
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const suggestions = await sourceFunction(term);
            if (suggestions.length > 0) {
                suggestionsBox.innerHTML = suggestions.map(s => {
                    const displayText = s.name || s.description || s.address;
                    const subText = s.sku || s.barcode || s.customer_code || '';
                    const safeSuggestionJSON = JSON.stringify(s).replace(/"/g, '&quot;');
                    return `<div class="p-2 hover:bg-pink-50 cursor-pointer suggestion-item border-b border-gray-100 last:border-0 transition-colors" data-suggestion="${safeSuggestionJSON}">
                                <div class="font-medium text-gray-800">${displayText}</div>
                                <div class="text-xs text-gray-500">${subText}</div>
                            </div>`;
                }).join('');
                suggestionsBox.classList.remove('hidden');
            } else {
                suggestionsBox.classList.add('hidden');
            }
        }, 300);
    });

    const selectItem = (item) => {
        if (item) {
            onSelect(JSON.parse(item.dataset.suggestion.replace(/&quot;/g, '"')));
            suggestionsBox.classList.add('hidden');
        }
    };

    suggestionsBox.addEventListener('click', (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item) selectItem(item);
    });

    input.addEventListener('keydown', async (e) => {
        if (e.key === 'Tab') {
            e.preventDefault();
            clearTimeout(debounceTimer);
            const term = input.value.trim();
            if (term.length === 0) return;
            const suggestions = await sourceFunction(term);
            if (suggestions && suggestions.length > 0) {
                onSelect(suggestions[0]);
                suggestionsBox.classList.add('hidden');
            }
        }
    });

    input.addEventListener('blur', () => setTimeout(() => suggestionsBox.classList.add('hidden'), 200));
}

async function getProductSuggestions(term) {
    const bu = document.getElementById('orderBu').value;
    const result = await postData('get_product_suggestions', { term, bu });
    return result.success ? result.data : [];
}

async function handleProductSelect(productObject) {
    if (!productObject) return;
    document.getElementById('itemDescription').value = productObject.description;
    const location = document.getElementById('orderLocation').value;
    const quantity = parseInt(document.getElementById('itemQuantity').value) || 1;
    const result = await postData('find_product_with_best_sku', { term: productObject.id, location, quantity });
    if (result.success && result.data) {
        populateItemDetails(result.data);
    } else {
        showMessage('Could not retrieve full product details.', true);
    }
}

async function searchCustomers(term) {
    if (!appState.customers) return [];
    return appState.customers.filter(c =>
        c.name.toLowerCase().includes(term.toLowerCase()) ||
        (c.customer_code && c.customer_code.toLowerCase().includes(term.toLowerCase()))
    );
}

function onCustomerSelect(customer) {
    if (customer) {
        appState.selectedCustomer = customer;
        document.getElementById('customerName').value = customer.name;
        document.getElementById('discountPercentage').value = customer.default_discount || '';
        updateOrderSummary();
        document.getElementById('customerAddress').focus();
    }
}

function populateItemDetails(productData) {
    currentItemData = productData;
    const skuSelect = document.getElementById('itemSkuSelect');
    const descriptionInput = document.getElementById('itemDescription');
    const barcodeInput = document.getElementById('itemBarcode');

    if (!productData) {
        skuSelect.innerHTML = '';
        document.getElementById('skuSelectionContainer').classList.add('hidden');
        return;
    }

    descriptionInput.value = productData.description;
    const barcode = productData.allSkus.find(s => s.type === 'barcode');
    if (barcode) barcodeInput.value = barcode.code;

    const skusWithOptions = productData.allSkus.filter(s => s.type === 'sku' && parseFloat(s.sales_price) > 0);

    if (skusWithOptions.length === 0) {
        showMessage(`No valid SKUs with prices found for ${productData.description}. Cannot add item.`, true);
        clearItemInputs();
        return;
    }

    skuSelect.innerHTML = skusWithOptions.map(s =>
        `<option value="${s.code}">${s.code} - ₱${parseFloat(s.sales_price).toFixed(2)}</option>`
    ).join('');

    document.getElementById('skuSelectionContainer').classList.remove('hidden');

    if (productData.bestSku) {
        skuSelect.value = productData.bestSku;
    } else if (skuSelect.options.length > 0) {
        skuSelect.value = skuSelect.options[0].value;
    }

    updateStockDisplay();
    calculateTotalPrice();

    document.getElementById('itemQuantity').focus();
    document.getElementById('itemQuantity').select();
}

function autoSelectBestSku() {
    if (!currentItemData || !currentItemData.allSkus) return;

    let quantity = parseInt(document.getElementById('itemQuantity').value) || 1;
    const unit = document.getElementById('itemUnit').value;

    const sellableSkus = currentItemData.allSkus
        .filter(s => s.type === 'sku' && parseFloat(s.sales_price) > 0)
        .map(s => ({
            code: s.code,
            stock: parseInt(s.stock) || 0,
            price: parseFloat(s.sales_price),
            pieces_per_case: parseInt(s.pieces_per_case) || 1
        }));

    if (sellableSkus.length === 0) return;

    // Determine actual pieces needed if 'case' is selected
    const currentSelectVal = document.getElementById('itemSkuSelect').value;
    const currentSkuInfo = sellableSkus.find(s => s.code === currentSelectVal) || sellableSkus[0];
    let qtyNeeded = quantity;
    if (unit === 'case') {
        qtyNeeded = quantity * currentSkuInfo.pieces_per_case;
    }

    const totalStock = sellableSkus.reduce((sum, s) => sum + s.stock, 0);
    let bestSku = '';

    if (totalStock <= 0) {
        sellableSkus.sort((a, b) => b.price - a.price);
        bestSku = sellableSkus[0].code;
    } else {
        const sufficient = sellableSkus.filter(s => s.stock >= qtyNeeded);
        if (sufficient.length > 0) {
            // Lowest stock that fulfills the qty
            sufficient.sort((a, b) => a.stock - b.stock);
            bestSku = sufficient[0].code;
        } else {
            // Highest stock available as fallback
            const partial = sellableSkus.filter(s => s.stock > 0);
            partial.sort((a, b) => b.stock - a.stock);
            bestSku = partial.length > 0 ? partial[0].code : sellableSkus[0].code;
        }
    }

    if (bestSku) {
        const skuSelect = document.getElementById('itemSkuSelect');
        if (skuSelect.value !== bestSku) {
            skuSelect.value = bestSku;
            updateStockDisplay();
        }
    }
}

function calculateTotalPrice() {
    const selectedSku = document.getElementById('itemSkuSelect').value;
    let quantity = parseInt(document.getElementById('itemQuantity').value);
    const unit = document.getElementById('itemUnit').value;
    const discount = parseFloat(document.getElementById('discountPercentage').value) || 0;

    if (!selectedSku || !quantity || quantity <= 0 || !currentItemData) {
        document.getElementById('itemPrice').value = '';
        return;
    }

    const skuInfo = currentItemData.allSkus.find(s => s.code === selectedSku);

    if (skuInfo && skuInfo.sales_price > 0) {
        if (unit === 'case') quantity *= (parseInt(skuInfo.pieces_per_case) || 1);
        let totalPrice = parseFloat(skuInfo.sales_price) * quantity;
        let discountedPrice = totalPrice * (1 - discount / 100);
        document.getElementById('itemPrice').value = discountedPrice.toFixed(2);
    } else {
        document.getElementById('itemPrice').value = '0.00';
    }
}

function updateStockDisplay() {
    const productCode = document.getElementById('itemSkuSelect').value;
    if (!productCode) return;
    const productInfo = appState.products[productCode];
    if (productInfo) {
        const invEntry = productInfo.inventory.find(i => i.location === selectedLocation);
        const stock = invEntry ? parseInt(invEntry.stock) : 0;
        document.getElementById('skuStockDisplay').textContent = `Stock: ${stock.toLocaleString('en-US')}`;
        document.getElementById('caseInfoDisplay').textContent = `(1 case = ${productInfo.pieces_per_case || 1} pcs)`;
    }
}

function handleItemSubmit() {
    const selectedSku = document.getElementById('itemSkuSelect').value;
    if (!currentItemData || !selectedSku) return showMessage('Please search and select a product first.', true);

    let quantity = parseInt(document.getElementById('itemQuantity').value);
    if (!quantity || quantity <= 0) return showMessage('Please enter a valid quantity.', true);

    const orderBu = document.getElementById('orderBu').value;
    if (currentItemData.bu !== orderBu) return showMessage(`Item BU (${currentItemData.bu}) does not match order BU (${orderBu}).`, true);

    const unit = document.getElementById('itemUnit').value;
    const skuInfo = currentItemData.allSkus.find(s => s.code === selectedSku);
    const finalQuantity = unit === 'case' ? quantity * (parseInt(skuInfo.pieces_per_case) || 1) : quantity;
    const price = parseFloat(document.getElementById('itemPrice').value);
    const stock = skuInfo.stock ? parseInt(skuInfo.stock) : 0;

    const newItem = {
        sku: selectedSku,
        description: currentItemData.description,
        quantity: finalQuantity,
        price: price,
        unitPrice: parseFloat(skuInfo.sales_price),
        status: stock >= finalQuantity ? 'served' : 'unserved'
    };

    if (stock < finalQuantity) {
        showMessage(`Warning: Stock for ${newItem.description} is insufficient. Marked as unserved.`, true);
    }

    if (editingItemIndex !== null) {
        appState.orderItems[editingItemIndex] = newItem;
        showMessage('Item updated successfully');
    } else {
        const existingItemIndex = appState.orderItems.findIndex(item => item.sku === newItem.sku);
        if (existingItemIndex > -1) {
            showMessage(`Item already in list. Switched to edit mode.`);
            startEditingItem(existingItemIndex);
            return;
        } else {
            appState.orderItems.push(newItem);
        }
    }

    updateOrderSummary();
    stopEditingItem();
}

function startEditingItem(index) {
    editingItemIndex = index;
    const item = appState.orderItems[index];

    // Find product data
    const productEntry = Object.entries(appState.products).find(([code, data]) =>
        code === item.sku || data.productId === item.productId
    );

    if (!productEntry) {
        showMessage('Could not find product details to edit.', true);
        return;
    }

    const productData = {
        id: productEntry[1].productId,
        description: item.description,
        sku: item.sku
    };

    handleProductSelect(productData);

    setTimeout(() => {
        const productInfo = appState.products[item.sku];
        if (productInfo && productInfo.pieces_per_case > 1 && item.quantity % productInfo.pieces_per_case === 0) {
            document.getElementById('itemUnit').value = 'case';
            document.getElementById('itemQuantity').value = item.quantity / productInfo.pieces_per_case;
        } else {
            document.getElementById('itemUnit').value = 'pcs';
            document.getElementById('itemQuantity').value = item.quantity;
        }
        calculateTotalPrice();
        document.getElementById('addItemBtn').textContent = 'Update Item';
        document.getElementById('addItemBtn').classList.add('bg-blue-600');
        document.getElementById('addItemBtn').classList.remove('bg-[#E42278]');

        // Scroll to item form on mobile
        document.querySelector('.lg\\\\:col-span-4').scrollIntoView({ behavior: 'smooth' });
    }, 500);
}

function stopEditingItem() {
    editingItemIndex = null;
    const btn = document.getElementById('addItemBtn');
    btn.textContent = 'Add to Order';
    btn.classList.remove('bg-blue-600');
    btn.classList.add('bg-[#E42278]');
    clearItemInputs();
}

function clearItemInputs() {
    currentItemData = null;
    document.getElementById('itemBarcode').value = '';
    document.getElementById('itemDescription').value = '';
    document.getElementById('itemQuantity').value = '1';
    document.getElementById('itemUnit').value = 'pcs';
    document.getElementById('itemPrice').value = '';

    document.getElementById('skuSelectionContainer').classList.add('hidden');
    document.getElementById('itemSkuSelect').innerHTML = '';
    document.getElementById('skuStockDisplay').textContent = '';
    document.getElementById('caseInfoDisplay').textContent = '';

    document.getElementById('itemBarcode').focus();
}

function applyDiscountToExistingItems() {
    const discount = parseFloat(document.getElementById('discountPercentage').value) || 0;
    let needsUpdate = false;

    appState.orderItems.forEach(item => {
        const grossPrice = item.unitPrice * item.quantity;
        const newPrice = grossPrice * (1 - (discount / 100));

        if (item.price !== newPrice) {
            item.price = newPrice;
            needsUpdate = true;
        }
    });

    if (needsUpdate) {
        updateOrderSummary();
    }
}

function updateOrderSummary() {
    const list = document.getElementById('orderItemsList');
    const emptyState = document.getElementById('emptyState');
    const itemsTable = document.getElementById('itemsTable');
    const totalCountElement = document.getElementById('totalItemsCount');
    const sideItemCount = document.getElementById('sideItemCount');

    // Toggle Empty State and Table
    if (appState.orderItems.length === 0) {
        list.innerHTML = '';
        if (emptyState) emptyState.style.display = 'block';
        if (itemsTable) itemsTable.classList.add('hidden');
    } else {
        if (emptyState) emptyState.style.display = 'none';
        if (itemsTable) itemsTable.classList.remove('hidden');

        // Generate rows matching the 4-column table structure
        list.innerHTML = appState.orderItems.map((item, index) => {
            const productInfo = appState.products[item.sku];
            const buMismatch = productInfo && productInfo.bu !== document.getElementById('orderBu').value;
            const buWarning = buMismatch ? `<span class="text-red-500 font-bold ml-1" title="BU Mismatch!">⚠</span>` : '';

            return `
            <tr class="premium-row group" data-index="${index}">
                <td data-label="Item Description">
                    <div class="font-bold text-[#0D111A] text-sm">${item.description} ${buWarning}</div>
                    <div class="text-[11px] text-[#6B7280] font-mono mt-0.5">SKU: ${item.sku}</div>
                </td>
                <td class="text-center" data-label="Qty">
                    <span class="inline-flex items-center justify-center px-3 py-1 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-md text-sm font-black text-[#0D111A] shadow-sm min-w-[2.5rem]">
                        ${item.quantity.toLocaleString('en-US')}
                    </span>
                    ${item.status === 'unserved' ? '<span class="badge-pill badge-unserved mt-2 mx-auto block w-fit">Unserved</span>' : ''}
                </td>
                <td class="text-right font-mono text-sm font-medium text-[#6B7280]" data-label="Unit Price">
                    ₱${(item.unitPrice || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                </td>
                <td class="text-right font-mono text-sm font-medium text-[#6B7280]" data-label="Gross Total">
                    ₱${((item.unitPrice || 0) * item.quantity).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                </td>
                <td class="text-right font-mono text-sm font-black text-[#E42278]" data-label="Net Total">
                    ₱${parseFloat(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                </td>
                <td class="text-right" data-label="Actions">
                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="edit-btn p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-700 rounded-md transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button class="delete-btn p-1.5 text-[#E42278] hover:bg-pink-50 hover:text-pink-700 rounded-md transition-colors" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    // Update counters
    const totalItems = appState.orderItems.length;
    if (totalCountElement) totalCountElement.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
    if (sideItemCount) sideItemCount.textContent = totalItems;

    // Calculate grand total
    const total = appState.orderItems.reduce((sum, item) => sum + (parseFloat(item.price) || 0), 0);
    const totalDisplay = document.getElementById('orderTotalDisplay');
    if (totalDisplay) {
        totalDisplay.textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    saveOrderToStorage();
}

// --- FINAL SUBMISSION ---

async function submitFinalOrder() {
    if (appState.orderItems.length === 0) {
        return showMessage("Cannot submit an empty order.", true);
    }

    showLoader();
    const orderData = {
        location: selectedLocation,
        bu: document.getElementById('orderBu').value,
        customer_id: appState.selectedCustomer ? appState.selectedCustomer.id : null,
        customer_name: document.getElementById('customerName').value,
        customer_address: document.getElementById('customerAddress').value,
        po_number: document.getElementById('poNumber').value,
        discount: document.getElementById('discountPercentage').value,
        items: JSON.stringify(appState.orderItems)
    };

    try {
        const result = await postData('add_order', orderData);
        if (result.success) {
            showMessage('Order created successfully.');

            if (result.order_id) {
                window.open(`view_order.php?id=${result.order_id}`, '_blank');
            }

            resetForNextOrder();
        } else {
            showMessage(result.message || 'Failed to submit order.', true);
        }
    } catch (e) {
        console.error(e);
        showMessage('An error occurred while submitting.', true);
    } finally {
        hideLoader();
    }
}

function resetForNextOrder() {
    appState.orderItems = [];
    appState.selectedCustomer = null;
    document.getElementById('poNumber').value = '';
    document.getElementById('customerAddress').value = '';
    document.getElementById('customerCode').value = '';
    document.getElementById('customerName').value = '';
    document.getElementById('discountPercentage').value = '0';
    stopEditingItem();
    updateOrderSummary();
    document.getElementById('poNumber').focus();
}

function setupEventListeners() {
    // Form state changes
    document.getElementById('orderLocation')?.addEventListener('change', updateFormState);
    document.getElementById('orderBu')?.addEventListener('change', updateFormState);

    // Auto-save on input
    ['customerName', 'customerAddress', 'poNumber', 'discountPercentage'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', saveOrderToStorage);
    });

    // Customer autocomplete
    setupAutocomplete('customerName', 'customerSuggestions', onCustomerSelect, searchCustomers);

    // Address autocomplete
    // Address autocomplete
    setupAutocomplete('customerAddress', 'addressSuggestions',
        (suggestion) => {
            document.getElementById('customerAddress').value = suggestion.address;
            document.getElementById('customerCode').value = suggestion.customer_code || '';
            saveOrderToStorage();
            document.getElementById('poNumber').focus();
        },
        async (term) => {
            // Pass the selected Customer ID to the API to filter addresses
            const customerId = appState.selectedCustomer ? appState.selectedCustomer.id : '';
            const result = await postData('get_address_suggestions', { term, customer_id: customerId });
            return result.success ? result.data : [];
        }
    );

    // Product search
    setupAutocomplete('itemBarcode', 'barcodeSuggestions', handleProductSelect, getProductSuggestions);
    setupAutocomplete('itemDescription', 'descriptionSuggestions', handleProductSelect, getProductSuggestions);

    // Item form
    document.getElementById('addItemBtn')?.addEventListener('click', handleItemSubmit);

    ['itemQuantity', 'itemUnit', 'itemSkuSelect', 'discountPercentage'].forEach(id => {
        const el = document.getElementById(id);
        el?.addEventListener('input', () => {
            if (id === 'itemQuantity' || id === 'itemUnit') autoSelectBestSku();
            calculateTotalPrice();
            if (id === 'discountPercentage') applyDiscountToExistingItems();
        });
        el?.addEventListener('change', () => {
            if (id === 'itemQuantity' || id === 'itemUnit') autoSelectBestSku();
            calculateTotalPrice();
            if (id === 'itemSkuSelect') updateStockDisplay();
            if (id === 'discountPercentage') applyDiscountToExistingItems();
        });
    });

    // Order item actions (Edit/Delete)
    document.getElementById('orderItemsList')?.addEventListener('click', (e) => {
        const button = e.target.closest('button');
        if (!button) return;

        const row = button.closest('tr');
        if (!row) return;

        const index = parseInt(row.dataset.index);

        if (button.classList.contains('edit-btn')) {
            startEditingItem(index);
        }
        if (button.classList.contains('delete-btn')) {
            const item = appState.orderItems[index];
            showConfirmation(`Delete "${item.description}" from order?`, () => {
                appState.orderItems.splice(index, 1);
                updateOrderSummary();
                if (editingItemIndex === index) stopEditingItem();
            });
        }
    });

    // Main action buttons
    document.getElementById('submitOrderBtn')?.addEventListener('click', () => {
        showConfirmation("Submit this order? Stock will be deducted.", () => submitFinalOrder());
    });

    document.getElementById('cancelOrderBtn')?.addEventListener('click', () => {
        showConfirmation("Clear this entire order? All data will be lost.", resetEncoderState);
    });

    // Barcode scanner auto-submit (if user presses Enter in barcode field)
    document.getElementById('itemBarcode')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            // If a product is already populated from barcode scan, add it immediately
            if (currentItemData && document.getElementById('itemSkuSelect').value) {
                handleItemSubmit();
            }
        }
    });

    // Custom Tab Navigation: Jump from Discount directly to Add Item Barcode
    document.getElementById('discountPercentage')?.addEventListener('keydown', (e) => {
        if (e.key === 'Tab' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('itemBarcode')?.focus();
        }
    });

    // Allows you to Shift+Tab backwards from Barcode back to Discount
    document.getElementById('itemBarcode')?.addEventListener('keydown', (e) => {
        if (e.key === 'Tab' && e.shiftKey) {
            e.preventDefault();
            document.getElementById('discountPercentage')?.focus();
        }
    });
}

document.addEventListener('DOMContentLoaded', init);