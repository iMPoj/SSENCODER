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

    // Toggle SO Number specific classes
    document.querySelectorAll('.soNumberText').forEach(el => el.classList.toggle('hidden', edit));
    document.querySelectorAll('.soNumberInput').forEach(el => el.classList.toggle('hidden', !edit));

    // Toggle Remarks and Auto-resize
    document.querySelectorAll('.remarksText').forEach(el => el.classList.toggle('hidden', edit));
    document.querySelectorAll('.remarksInput').forEach(el => {
        el.classList.toggle('hidden', !edit);
        if (edit) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        }
    });

    // 2. Toggle Table Rows (Inputs inside the table)
    document.querySelectorAll('.item-row').forEach(row => {
        row.querySelectorAll('.sku-select, .quantity-input, .status-toggle-btn').forEach(el => {
            el.disabled = !edit;
        });
    });

    // 3. Toggle Action Buttons
    const toggleHidden = (id, shouldHide) => {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('hidden', shouldHide);
    };

    toggleHidden('editSoBtn', edit);              // Hide "Edit SO" when in edit mode
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

let isSoEditMode = false;

/**
 * Toggles ONLY the SO inputs and Remarks for instant, lag-free editing.
 */
function setSoEditMode(edit) {
    isSoEditMode = edit;

    // Toggle only the SO Number fields
    document.querySelectorAll('.soNumberText').forEach(el => el.classList.toggle('hidden', edit));
    document.querySelectorAll('.soNumberInput').forEach(el => el.classList.toggle('hidden', !edit));

    // Toggle Remarks
    document.querySelectorAll('.remarksText').forEach(el => el.classList.toggle('hidden', edit));
    document.querySelectorAll('.remarksInput').forEach(el => el.classList.toggle('hidden', !edit));

    // Toggle Action Buttons
    const toggleHidden = (id, shouldHide) => {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('hidden', shouldHide);
    };

    toggleHidden('editSoBtn', edit);
    toggleHidden('editOrderBtn', edit);         // Hide full edit while editing SO
    toggleHidden('saveChangesBtn', !edit);      // Show "Save"
    toggleHidden('cancelChangesBtn', !edit);    // Show "Cancel"

    // Auto-focus the first SO input for convenience
    if (edit) {
        const firstInput = document.querySelector('.soNumberInput');
        if (firstInput) firstInput.focus();
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
    const pdiff = parseFloat(row.dataset.pdiff || 0);

    // Fetch product info from global cache
    const productInfo = window.productsBySku ? window.productsBySku[selectedSku] : null;
    const unitPrice = (productInfo ? parseFloat(productInfo.sales_price) : 0) + pdiff;

    // Update the displayed Unit Price 
    if (pristineDisplay) {
        pristineDisplay.textContent = '₱' + unitPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (pdiff !== 0) pristineDisplay.classList.add('text-blue-600', 'font-bold');
        else pristineDisplay.classList.remove('text-blue-600', 'font-bold');
    }

    const preDiscountTotal = quantity * unitPrice;
    const finalTotal = preDiscountTotal * (1 - (discount / 100));

    priceInput.value = finalTotal.toFixed(2);
}

function recalculateAllPrices() {
    document.querySelectorAll('.item-row').forEach(row => {
        recalculateRowPrice(row);
    });
    updateFooterTotals();
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
        const result = await postData('get_stock_for_product', { product_id: productId });

        if (result.success) {
            updateAllSkuDropdownsForProduct(productId, result.data);
        } else {
            console.error("Stock fetch failed:", result.message);
        }
    } catch (e) {
        console.error(`Failed to fetch stock for product ${productId}`, e);
    }
}

function updateAllSkuDropdownsForProduct(productId, stockData) {
    document.querySelectorAll('.item-row').forEach(row => {
        const skuSelect = row.querySelector('.sku-select');
        if (!skuSelect) return;

        const currentSku = skuSelect.value || row.dataset.originalSku;
        const productInfo = window.productsBySku ? window.productsBySku[currentSku] : null;

        if (productInfo && productInfo.productId == productId) {
            // Rebuild options with stock info
            skuSelect.innerHTML = productInfo.allSkus.map(s => {
                const stockInfo = stockData[s.code] || {};
                const dvoStock = stockInfo['Davao'] || 0;
                const genStock = stockInfo['Gensan'] || 0;

                return `<option value="${s.code}">${s.code} (${s.type}) - DVO: ${dvoStock} | GEN: ${genStock}</option>`;
            }).join('');

            // Keep their current selection highlighted
            if (skuSelect.querySelector(`option[value="${currentSku}"]`)) {
                skuSelect.value = currentSku;
            }
        }
    });
}

function initializeStatusButton(btn) {
    const row = btn.closest('.item-row');
    let currentStatus = row.dataset.status || row.dataset.originalStatus;

    const updateButtonState = () => {
        btn.dataset.status = currentStatus;
        row.dataset.status = currentStatus; // Sync row data

        if (currentStatus === 'served') {
            btn.textContent = 'Served';
            btn.className = 'status-toggle-btn px-3 py-1 text-[10px] uppercase font-bold rounded-full shadow-sm transition-all bg-green-100 text-green-800';
            row.classList.remove('bg-yellow-50', 'print-highlight');
        } else if (currentStatus === 'fulfilled') {
            btn.textContent = 'Fulfilled';
            btn.className = 'status-toggle-btn px-3 py-1 text-[10px] uppercase font-bold rounded-full shadow-sm transition-all bg-yellow-100 text-yellow-800 border border-yellow-300';
            row.classList.add('bg-yellow-50', 'print-highlight');
        } else {
            btn.textContent = 'Unserved';
            btn.className = 'status-toggle-btn px-3 py-1 text-[10px] uppercase font-bold rounded-full shadow-sm transition-all bg-white text-red-800 border border-red-300';
            row.classList.remove('bg-yellow-50', 'print-highlight');
        }
    };

    btn.addEventListener('click', () => {
        if (!isEditMode) return;

        // Cycle smoothly through all 3 statuses: Unserved -> Fulfilled -> Served
        if (currentStatus === 'unserved') {
            currentStatus = 'fulfilled';
        } else if (currentStatus === 'fulfilled') {
            currentStatus = 'served';
        } else {
            currentStatus = 'unserved';
        }

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
                    `<div class="p-2 hover:bg-slate-100 cursor-pointer suggestion-item text-xs" data-product="${encodeURIComponent(JSON.stringify(p))}">
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

    suggestionsBox.addEventListener('mousedown', async (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item) {
            const product = JSON.parse(decodeURIComponent(item.dataset.product));
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

            skuSelect.innerHTML = fullData.allSkus.map(s =>
                `<option value="${s.code}">${s.code} (${s.type})</option>`
            ).join('');

            row.dataset.originalSku = fullData.bestSku;
            skuSelect.value = fullData.bestSku;

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
    // --- RESTORE SCROLL POSITION ---
    const savedScroll = sessionStorage.getItem('scrollPosition_' + window.orderId);
    if (savedScroll !== null) {
        setTimeout(() => {
            window.scrollTo({ top: parseInt(savedScroll, 10), behavior: 'instant' });
        }, 100);
        sessionStorage.removeItem('scrollPosition_' + window.orderId);
    }

    // 1. Attach Button Listeners
    const discountInput = document.getElementById('orderDiscountInput');
    if (discountInput) discountInput.addEventListener('input', recalculateAllPrices);

    document.getElementById('editSoBtn')?.addEventListener('click', () => setSoEditMode(true));
    document.getElementById('editOrderBtn')?.addEventListener('click', () => setEditMode(true));
    document.getElementById('cancelChangesBtn')?.addEventListener('click', () => window.location.reload());

    // Auto-resize remarks textareas dynamically as the user types
    document.querySelectorAll('.remarksInput').forEach(textarea => {
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });

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
        let hasUnservedChange = false;
        document.querySelectorAll('.item-row').forEach(row => {
            const orig = row.dataset.originalStatus;
            const curr = row.querySelector('.status-toggle-btn').dataset.status;
            if ((orig === 'served' || orig === 'fulfilled') && curr === 'unserved') {
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
        if (statusBtn) initializeStatusButton(statusBtn);

        const pdiffBtn = row.querySelector('.pdiff-btn');
        if (pdiffBtn) {
            pdiffBtn.addEventListener('click', () => {
                if (!isEditMode) return;
                let diff = prompt("Enter price difference per pc (+ or -):", row.dataset.pdiff || "0");
                if (diff !== null) {
                    row.dataset.pdiff = parseFloat(diff) || 0;
                    recalculateRowPrice(row);
                    updateFooterTotals();
                }
            });
        }

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

    const remarksInputs = document.querySelectorAll('.remarksInput');
    const remarksArray = Array.from(remarksInputs).map(input => input.value);

    const discount = document.getElementById('orderDiscountInput')?.value || 0;
    const poNumber = document.getElementById('orderPoInput')?.value || "";
    const orderDate = document.getElementById('orderDateInput')?.value || "";
    const location = document.getElementById('orderLocationInput')?.value || "";
    const address = document.getElementById('orderAddressInput')?.value || "";
    const customerId = document.getElementById('orderCustomerInput')?.value || "";

    const result = await postData('update_order_items', {
        order_id: window.orderId,
        items: JSON.stringify(updatedItems),
        so_numbers: JSON.stringify(soNumbers),
        remarks: JSON.stringify(remarksArray),
        discount: discount,
        po_number: poNumber,
        order_date: orderDate,
        location: location,
        address: address,
        customer_id: customerId,
        restore_stock: restoreStock
    });

    hideLoader();
    if (result.success) {
        showMessage(result.message);
        sessionStorage.setItem('scrollPosition_' + window.orderId, window.scrollY);
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

function updateFooterTotals() {
    document.querySelectorAll('.page-container').forEach(page => {
        let pageServedTotal = 0;
        let pageAllTotal = 0;

        page.querySelectorAll('.item-row').forEach(row => {
            const priceInput = row.querySelector('.price-input');
            if (!priceInput) return;

            const finalPrice = parseFloat(priceInput.value) || 0;
            const statusBtn = row.querySelector('.status-toggle-btn');
            const status = statusBtn ? statusBtn.dataset.status : row.dataset.status;

            pageAllTotal += finalPrice;
            if (status === 'served' || status === 'fulfilled') {
                pageServedTotal += finalPrice;
            }
        });

        const servedTotalEl = page.querySelector('.text-emerald-600');
        if (servedTotalEl) servedTotalEl.textContent = '₱' + pageServedTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const allTotalEl = page.querySelector('.print-only .text-slate-800');
        if (allTotalEl) allTotalEl.textContent = '₱' + pageAllTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    });
}