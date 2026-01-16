import { showLoader, hideLoader, showMessage, showConfirmation } from './ui.js';
import { postData, fetchData } from './api.js';

let isEditMode = false;
let debounceTimer;

/**
 * Toggles the entire page between view and edit mode.
 */
function setEditMode(edit) {
    isEditMode = edit;
    
    // 1. Toggle Global Header Inputs (PO, Date, Location, Discount)
    const discountInput = document.getElementById('orderDiscountInput');
    if (discountInput) discountInput.disabled = !edit;
    
    // Toggle visibility for ALL view/edit elements on the page (Header fields)
    document.querySelectorAll('.view-mode-element').forEach(el => el.classList.toggle('hidden', edit));
    document.querySelectorAll('.edit-mode-element').forEach(el => el.classList.toggle('hidden', !edit));

    // Toggle SO Number specific classes (from your PHP structure)
    document.querySelectorAll('.soNumberText').forEach(el => el.classList.toggle('hidden', edit));
    document.querySelectorAll('.soNumberInput').forEach(el => el.classList.toggle('hidden', !edit));

    // 2. Toggle Table Rows (Inputs inside the table)
    document.querySelectorAll('.item-row').forEach(row => {
        row.querySelectorAll('.sku-select, .quantity-input, .status-toggle-btn').forEach(el => {
            el.disabled = !edit;
        });
    });

    // 3. Toggle Action Buttons
    const toggleHidden = (id, shouldHide) => {
        const el = document.getElementById(id);
        if(el) el.classList.toggle('hidden', shouldHide);
    };

    toggleHidden('editOrderBtn', edit);           // Hide "Edit" when in edit mode
    toggleHidden('repairDescriptionsBtn', !edit); // Show tools when in edit mode
    toggleHidden('recalculatePricesBtn', !edit);
    toggleHidden('saveChangesBtn', !edit);        // Show "Save"
    toggleHidden('cancelChangesBtn', !edit);      // Show "Cancel"
    toggleHidden('deleteOrderBtn', edit);         // Hide "Delete" to prevent accidents

    // Trigger stock fetch if entering edit mode to populate dropdowns
    if (edit) {
        fetchAllProductStocks();
    }
}

/**
 * Recalculates the total price for a single item row.
 */
function recalculateRowPrice(row) {
    const quantityInput = row.querySelector('.quantity-input');
    const skuSelect = row.querySelector('.sku-select');
    const priceInput = row.querySelector('.price-input');
    const discountInput = document.getElementById('orderDiscountInput');
    const pristineDisplay = row.querySelector('.pristine-price-display');

    const quantity = parseInt(quantityInput.value, 10) || 0;
    const selectedSku = skuSelect.value;
    const discount = parseFloat(discountInput?.value) || 0;
    
    // Fetch product info from global cache
    const productInfo = window.productsBySku ? window.productsBySku[selectedSku] : null;
    const unitPrice = productInfo ? parseFloat(productInfo.sales_price) : 0;

    // Update the displayed Unit Price (Red text)
    if (pristineDisplay) {
        pristineDisplay.textContent = '₱' + unitPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const preDiscountTotal = quantity * unitPrice;
    const finalTotal = preDiscountTotal * (1 - (discount / 100));

    priceInput.value = finalTotal.toFixed(2);
}

function recalculateAllPrices() {
    document.querySelectorAll('.item-row').forEach(row => {
        recalculateRowPrice(row);
    });
}

async function fetchAllProductStocks() {
    if (!window.productsBySku) return;

    const productIds = new Set();
    document.querySelectorAll('.item-row').forEach(row => {
        const skuSelect = row.querySelector('.sku-select');
        const sku = skuSelect.value || row.dataset.originalSku;
        const productInfo = window.productsBySku[sku];
        if (productInfo) {
            productIds.add(productInfo.productId);
        }
    });

    for (const productId of productIds) {
        await fetchAndPopulateStock(productId);
    }
}

async function fetchAndPopulateStock(productId) {
    try {
        const result = await fetchData(`get_stock_for_product&product_id=${productId}`);
        if (result.success) {
            updateAllSkuDropdownsForProduct(productId, result.data);
        }
    } catch (e) {
        console.error(`Failed to fetch stock for product ${productId}`, e);
    }
}

function updateAllSkuDropdownsForProduct(productId, stockData) {
    document.querySelectorAll('.item-row').forEach(row => {
        const skuSelect = row.querySelector('.sku-select');
        const currentSku = skuSelect.value || row.dataset.originalSku;
        
        const productInfo = window.productsBySku ? window.productsBySku[currentSku] : null;
        if (productInfo && productInfo.productId == productId) {
            // Rebuild options with stock info
            skuSelect.innerHTML = productInfo.allSkus.map(s => {
                const stockInfo = stockData[s.code] || { Davao: 0, Gensan: 0 };
                return `<option value="${s.code}">
                            ${s.code} (${s.type}) (DVO: ${stockInfo.Davao} | GEN: ${stockInfo.Gensan})
                        </option>`;
            }).join('');
            
            // Restore selection
            if (skuSelect.querySelector(`option[value="${currentSku}"]`)) {
                skuSelect.value = currentSku;
            }
        }
    });
}

function initializeStatusButton(btn) {
    const row = btn.closest('.item-row');
    let currentStatus = row.dataset.originalStatus;
    
    const updateButtonState = () => {
        btn.dataset.status = currentStatus;
        if (currentStatus === 'served') {
            btn.textContent = 'Served';
            btn.classList.add('bg-green-100', 'text-green-800');
            btn.classList.remove('bg-white', 'text-red-800', 'border', 'border-red-300');
        } else {
            btn.textContent = 'Unserved';
            btn.classList.add('bg-white', 'text-red-800', 'border', 'border-red-300');
            btn.classList.remove('bg-green-100', 'text-green-800');
        }
    };

    btn.addEventListener('click', () => {
        if (!isEditMode) return;
        currentStatus = (currentStatus === 'served') ? 'unserved' : 'served';
        updateButtonState();
    });
    updateButtonState();
}

function setupItemSearch(row) {
    const input = row.querySelector('.item-search-input');
    const suggestionsBox = row.querySelector('.item-suggestions');

    input.addEventListener('input', () => {
        const term = input.value.trim();
        if (term.length < 2) {
            suggestionsBox.classList.add('hidden');
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const result = await postData('get_product_suggestions', { term });
            if (result.success && result.data.length > 0) {
                suggestionsBox.innerHTML = result.data.map(p => 
                    `<div class="p-2 hover:bg-slate-100 cursor-pointer suggestion-item text-xs" data-product='${JSON.stringify(p)}'>
                        <div class="font-bold text-slate-700">${p.description}</div>
                        <div class="text-slate-400">${p.sku}</div>
                    </div>`
                ).join('');
                suggestionsBox.classList.remove('hidden');
            } else {
                suggestionsBox.classList.add('hidden');
            }
        }, 300);
    });

    suggestionsBox.addEventListener('click', async (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item) {
            const product = JSON.parse(item.dataset.product);
            await swapProductInRow(row, product);
            suggestionsBox.classList.add('hidden');
        }
    });

    input.addEventListener('blur', () => setTimeout(() => suggestionsBox.classList.add('hidden'), 200));
}

async function swapProductInRow(row, productOverview) {
    showLoader();
    try {
        const result = await postData('find_product_with_best_sku', { 
            term: productOverview.id, 
            location: window.orderLocation 
        });

        if (result.success && result.data) {
            const fullData = result.data;
            // Update global cache
            if (!window.productsBySku) window.productsBySku = {};
            fullData.allSkus.forEach(code => {
                window.productsBySku[code.code] = {
                    productId: fullData.productId,
                    description: fullData.description,
                    sales_price: parseFloat(code.sales_price),
                    allSkus: fullData.allSkus
                };
            });

            row.querySelector('.item-search-input').value = fullData.description;
            row.querySelector('.item-description-text').textContent = fullData.description;
            row.querySelector('.sku-text-display').textContent = fullData.bestSku;
            
            const skuSelect = row.querySelector('.sku-select');
            await fetchAndPopulateStock(fullData.productId);
            skuSelect.value = fullData.bestSku;
            recalculateRowPrice(row);

            showMessage(`Item swapped to: ${fullData.description}`);
        } else {
            showMessage('Could not load details for selected product.', true);
        }
    } catch (e) {
        console.error(e);
        showMessage('Error swapping product.', true);
    } finally {
        hideLoader();
    }
}


function init() {
    // 1. Attach Button Listeners (Do this first so button works even if rows fail)
    const discountInput = document.getElementById('orderDiscountInput');
    if(discountInput) discountInput.addEventListener('input', recalculateAllPrices);

    document.getElementById('editOrderBtn')?.addEventListener('click', () => setEditMode(true));
    document.getElementById('cancelChangesBtn')?.addEventListener('click', () => window.location.reload());
    
    // Repair Description Button
    document.getElementById('repairDescriptionsBtn')?.addEventListener('click', () => {
        document.querySelectorAll('.item-row').forEach(row => {
            const sku = row.querySelector('.sku-select').value;
            const productInfo = window.productsBySku ? window.productsBySku[sku] : null;
            if (productInfo) {
                row.querySelector('.item-description-text').textContent = productInfo.description;
                row.querySelector('.item-search-input').value = productInfo.description;
            }
        });
        showMessage("Descriptions repaired! Please click 'Save Changes' to apply.");
    });

    // Recalculate Prices Button
    document.getElementById('recalculatePricesBtn')?.addEventListener('click', () => {
        showConfirmation("Update all prices from database?", () => {
            recalculateAllPrices();
            showMessage("Prices updated! Save to apply.");
        });
    });

    // Save Changes Button
    document.getElementById('saveChangesBtn')?.addEventListener('click', async () => {
        // Check for unserved items logic
        let hasUnservedChange = false;
        document.querySelectorAll('.item-row').forEach(row => {
            const orig = row.dataset.originalStatus;
            const curr = row.querySelector('.status-toggle-btn').dataset.status;
            if (orig === 'served' && curr === 'unserved') {
                hasUnservedChange = true;
            }
        });

        if (hasUnservedChange) {
            const modal = document.getElementById('stockDecisionModal');
            if (modal) {
                modal.classList.remove('hidden');
                
                const yesBtn = document.getElementById('stockReturnYesBtn');
                const noBtn = document.getElementById('stockReturnNoBtn');
                const cancelBtn = document.getElementById('stockReturnCancelBtn');

                // Clone to remove old listeners
                const newYes = yesBtn.cloneNode(true);
                const newNo = noBtn.cloneNode(true);
                const newCancel = cancelBtn.cloneNode(true);

                yesBtn.parentNode.replaceChild(newYes, yesBtn);
                noBtn.parentNode.replaceChild(newNo, noBtn);
                cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);

                newYes.addEventListener('click', () => { modal.classList.add('hidden'); performSave(true); });
                newNo.addEventListener('click', () => { modal.classList.add('hidden'); performSave(false); });
                newCancel.addEventListener('click', () => modal.classList.add('hidden'));
            } else {
                performSave(true);
            }
        } else {
            performSave(true);
        }
    });

    // 2. Initialize Rows
    if (!window.productsBySku) {
        console.warn("productsBySku data missing from PHP. Some features may be disabled.");
        window.productsBySku = {};
    }

    document.querySelectorAll('.item-row').forEach((row) => {
        const originalSku = row.dataset.originalSku;
        const skuSelect = row.querySelector('.sku-select');
        const quantityInput = row.querySelector('.quantity-input');
        const productInfo = window.productsBySku[originalSku];

        if (productInfo && productInfo.allSkus) {
            skuSelect.innerHTML = productInfo.allSkus.map(s => 
                `<option value="${s.code}">${s.code} (${s.type})</option>`
            ).join('');
            skuSelect.value = originalSku;

            skuSelect.addEventListener('change', () => {
                recalculateRowPrice(row);
            });
        }
        
        if (quantityInput) {
            quantityInput.addEventListener('input', () => recalculateRowPrice(row));
        }
        
        const statusBtn = row.querySelector('.status-toggle-btn');
        if(statusBtn) initializeStatusButton(statusBtn);

        setupItemSearch(row);
    });

    // 3. Pristine Button Logic
    const pristineBtn = document.getElementById('pristineCheckBtn');
    if (pristineBtn) {
        pristineBtn.addEventListener('click', async () => {
            const currentStatus = parseInt(pristineBtn.dataset.status);
            const newStatus = currentStatus ? 0 : 1;
            
            showLoader();
            try {
                const result = await postData('toggle_pristine_status', {
                    order_id: window.orderId,
                    status: newStatus
                });
                
                if (result.success) {
                    pristineBtn.dataset.status = newStatus;
                    if (newStatus) {
                        pristineBtn.textContent = '✓ Pristine';
                        pristineBtn.classList.replace('bg-slate-400', 'bg-emerald-600');
                        pristineBtn.classList.replace('hover:bg-slate-500', 'hover:bg-emerald-700');
                    } else {
                        pristineBtn.textContent = 'Mark Pristine';
                        pristineBtn.classList.replace('bg-emerald-600', 'bg-slate-400');
                        pristineBtn.classList.replace('hover:bg-emerald-700', 'hover:bg-slate-500');
                    }
                    showMessage(result.message);
                } else {
                    showMessage('Failed to update status', true);
                }
            } catch (error) {
                console.error(error);
                showMessage('An error occurred', true);
            } finally {
                hideLoader();
            }
        });
    }
}

async function performSave(restoreStock) {
    showLoader();
    const updatedItems = [];
    document.querySelectorAll('.item-row').forEach(row => {
        updatedItems.push({
            id: row.dataset.itemId, 
            sku: row.querySelector('.sku-select').value,
            description: row.querySelector('.item-search-input').value || row.querySelector('.item-description-text').textContent.trim(),
            quantity: row.querySelector('.quantity-input').value,
            price: row.querySelector('.price-input').value,
            status: row.querySelector('.status-toggle-btn').dataset.status
        });
    });

    const soNumberInputs = document.querySelectorAll('.soNumberInput');
    const soNumbers = Array.from(soNumberInputs).map(input => input.value);
    
    // Collect Header Details
    const discount = document.getElementById('orderDiscountInput')?.value || 0;
    const poNumber = document.getElementById('orderPoInput')?.value || "";
    const orderDate = document.getElementById('orderDateInput')?.value || "";
    const location = document.getElementById('orderLocationInput')?.value || "";

    const result = await postData('update_order_items', {
        order_id: window.orderId,
        items: JSON.stringify(updatedItems),
        so_numbers: JSON.stringify(soNumbers),
        discount: discount,
        po_number: poNumber,
        order_date: orderDate,
        location: location,
        restore_stock: restoreStock
    });

    hideLoader();
    if (result.success) {
        showMessage(result.message);
        setTimeout(() => window.location.reload(), 1500);
    } else {
        showMessage(result.message || 'Failed to save changes.', true);
    }
}

// Safer initialization: check if DOM is already ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}