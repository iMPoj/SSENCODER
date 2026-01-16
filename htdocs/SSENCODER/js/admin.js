// FILE: js/admin.js
import { appState } from './state.js';
import { postData, fetchData } from './api.js';
import { showLoader, hideLoader, showMessage, showConfirmation } from './ui.js';

let refreshDataCallback = () => {};

function handleAdminTabSwitch(tabName) {
    const sections = {
        inventory: document.getElementById('adminInventorySection'),
        bulkOrder: document.getElementById('adminBulkOrderSection'),
        customer: document.getElementById('adminCustomerSection'),
        export: document.getElementById('adminExportSection'),
        targets: document.getElementById('adminTargetsSection')
    };

    document.querySelectorAll('.admin-tab-btn').forEach(btn => {
        const btnTabName = btn.id.replace('AdminBtn', '');
        const isActive = btnTabName === tabName;
        
        btn.classList.toggle('active', isActive);
        btn.classList.toggle('border-indigo-500', isActive);
        btn.classList.toggle('text-indigo-600', isActive);
        btn.classList.toggle('text-slate-500', !isActive);
        
        if (sections[btnTabName]) {
            sections[btnTabName].classList.toggle('hidden', !isActive);
        }
    });
}

function renderCustomerManagementList() {
    const list = document.getElementById('customerManagementList');
    if (!list) return;
    list.innerHTML = appState.customers.map(c => `
        <div class="flex justify-between items-center bg-white p-2 rounded shadow-sm">
            <span class="${c.is_priority ? 'font-semibold text-indigo-600' : ''}">${c.name}</span>
            <div class="flex items-center gap-4">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer priority-toggle" data-id="${c.id}" ${c.is_priority ? 'checked' : ''}>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-900">Priority</span>
                </label>
                <button class="delete-customer-btn text-red-500 hover:text-red-700 text-sm" data-id="${c.id}" data-name="${c.name}">Delete</button>
            </div>
        </div>
    `).join('');
}

async function exportData(format) {
    const month = document.getElementById('exportMonth').value;
    const year = document.getElementById('exportYear').value;
    const location = document.getElementById('exportLoc').value;

    if (!year || !month) { return showMessage("Please select a valid month and year.", true); }
    showLoader();
    const result = await postData('get_orders_for_export', { month, year, location });
    hideLoader();
    if (!result.success || !result.data || result.data.length === 0) {
        return showMessage("No data found for the selected period.", true);
    }

    const headers = ['OrderDate', 'Location', 'BU', 'Customer', 'Address', 'PONumber', 'CustomerCode', 'SONumber', 'SKU', 'Description', 'Quantity', 'Price', 'Status'];
    const delimiter = format === 'csv' ? ',' : '\t';
    const sanitize = (value) => {
        const strValue = String(value ?? ''); // Use nullish coalescing for safety
        if ((strValue.includes(delimiter) || strValue.includes('"') || strValue.includes('\n')) && format === 'csv') {
            return `"${strValue.replace(/"/g, '""')}"`;
        }
        return strValue;
    };

    let fileContent = headers.join(delimiter) + '\n';
    
    result.data.forEach(row => {
        let so_number = '';
        try {
            const soArray = JSON.parse(row.so_number || '[]');
            so_number = Array.isArray(soArray) ? soArray.join(';') : (row.so_number || '');
        } catch (e) {
            so_number = row.so_number || ''; 
        }

        const csvRow = [
            row.order_date.split(' ')[0],
            row.location,
            row.bu,
            row.customer_name,
            row.customer_address,
            row.po_number,
            row.customer_code || '',
            so_number,
            row.sku,
            row.description,
            row.quantity,
            row.price,
            row.status
        ];
        fileContent += csvRow.map(sanitize).join(delimiter) + '\n';
    });

    const blob = new Blob([fileContent], { type: `text/${format};charset=utf-8;` });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `orders_${location}_${year}-${String(month).padStart(2, '0')}.${format}`;
    link.click();
    link.remove();
}

export function initAdmin(dataCallback) {
    refreshDataCallback = dataCallback;
    
    // Initial Setup
    const now = new Date();
    document.getElementById('exportMonth').value = now.getMonth() + 1;
    document.getElementById('exportYear').value = now.getFullYear();
    
    // Default Tab & Load
    handleAdminTabSwitch('inventory');
    renderCustomerManagementList();
    
    const pdfParseLocationSelect = document.getElementById('pdfParseLocation');
    const pdfParseBuSelect = document.getElementById('pdfParseBu');
    if (pdfParseLocationSelect) {
        pdfParseLocationSelect.value = localStorage.getItem('lastPdfLocation') || '';
    }
    if (pdfParseBuSelect) {
        pdfParseBuSelect.value = localStorage.getItem('lastPdfBu') || '';
    }

    // --- Tab Switching ---
    document.querySelectorAll('.admin-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => handleAdminTabSwitch(btn.id.replace('AdminBtn', '')));
    });

    document.getElementById('adminLocFilter')?.addEventListener('input', () => {
         // This listener is now only used to save the preferred location if you want to remember it
         const selectedLoc = document.getElementById('adminLocFilter').value;
         localStorage.setItem('lastSelectedLocation', selectedLoc);
    });

    document.getElementById('addCustomerForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newCustomerNameInput = document.getElementById('newCustomerName');
        const name = newCustomerNameInput.value.trim();
        if (!name) return;
        const result = await postData('add_customer', { name });
        if (result.success) {
            await refreshDataCallback(); 
            renderCustomerManagementList();
            newCustomerNameInput.value = '';
            showMessage('Customer added successfully.');
        }
    });

    document.getElementById('customerManagementList')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete-customer-btn')) {
            const customerId = e.target.dataset.id;
            const customerName = e.target.dataset.name;
            showConfirmation(`Delete customer "${customerName}"?`, async () => {
                const result = await postData('delete_customer', { id: customerId });
                if (result.success) {
                    await refreshDataCallback();
                    renderCustomerManagementList();
                    showMessage('Customer deleted.');
                }
            });
        }
    });

    document.getElementById('customerManagementList')?.addEventListener('change', async (e) => {
        if (e.target.classList.contains('priority-toggle')) {
            const customerId = e.target.dataset.id;
            const isChecked = e.target.checked;
            const result = await postData('toggle_customer_priority', { id: customerId, is_priority: isChecked ? 1 : 0 });
            if (result.success) {
                const customer = appState.customers.find(c => c.id == customerId);
                if (customer) {
                    customer.is_priority = isChecked;
                    showMessage(`${customer.name} priority status updated.`);
                    renderCustomerManagementList();
                }
            } else {
                e.target.checked = !isChecked;
                showMessage(`Failed to update status.`, true);
            }
        }
    });
    
    document.getElementById('targetsAdminBtn')?.addEventListener('click', () => handleAdminTabSwitch('targets'));

    async function loadTargets() {
        const result = await postData('get_monthly_targets', {});
        if (result.success && result.data) {
            result.data.forEach(target => {
                const input = document.getElementById(`target-${target.location.toLowerCase()}-${target.bu.toLowerCase()}`);
                if (input) {
                    input.value = parseFloat(target.target_amount);
                }
            });
        }
    }

    document.getElementById('targetsForm')?.addEventListener('submit', async e => {
        e.preventDefault();
        const targets = [];
        let hasInvalidNumber = false;

        document.querySelectorAll('.target-input').forEach(input => {
            const cleanValue = input.value.replace(/,/g, '');
            if (cleanValue) {
                if (isNaN(parseFloat(cleanValue))) {
                    hasInvalidNumber = true;
                }
                targets.push({
                    location: input.dataset.location,
                    bu: input.dataset.bu,
                    amount: cleanValue
                });
            }
        });

        if (hasInvalidNumber) {
            return showMessage('Please enter valid numbers for the targets.', true);
        }

        if (targets.length === 0) {
            return showMessage('Please enter at least one target amount.', true);
        }

        showLoader();
        const result = await postData('set_monthly_targets', { targets: JSON.stringify(targets) });
        hideLoader();
        if (result.success) {
            showMessage(result.message);
        } else {
            showMessage(result.message || 'Failed to save targets.', true);
        }
    });

    loadTargets();
    
    const addressCodeModal = document.getElementById('addressCodeModal');
    const addressCodeForm = document.getElementById('addressCodeForm');
    const addressCodeModalTitle = document.getElementById('addressCodeModalTitle');
    const addressCodeIdInput = document.getElementById('addressCodeId');
    const addressInput = document.getElementById('addressInput');
    const customerCodeInput = document.getElementById('customerCodeInput');

    async function renderAddressCodes() {
        const list = document.getElementById('addressCodeList');
        if (!list) return;

        const result = await postData('get_address_codes', {});
        if (result.success) {
            list.innerHTML = result.data.map(item => `
                <tr class="text-sm">
                    <td>${item.address}</td>
                    <td>${item.customer_code}</td>
                    <td class="text-right">
                        <button class="edit-code-btn text-blue-600 hover:text-blue-900 mr-2 font-medium" data-id="${item.id}" data-address="${item.address}" data-code="${item.customer_code}">Edit</button>
                        <button class="delete-code-btn text-red-600 hover:text-red-900 font-medium" data-id="${item.id}">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
    }

    function openAddressCodeModal(item = null) {
        addressCodeForm.reset();
        if (item) {
            addressCodeModalTitle.textContent = 'Edit Address Code';
            addressCodeIdInput.value = item.id;
            addressInput.value = item.address;
            customerCodeInput.value = item.code;
        } else {
            addressCodeModalTitle.textContent = 'Add New Address Code';
            addressCodeIdInput.value = '';
        }
        addressCodeModal.classList.remove('hidden');
    }

    document.getElementById('addAddressCodeBtn')?.addEventListener('click', () => openAddressCodeModal());
    document.getElementById('cancelAddressCodeBtn')?.addEventListener('click', () => addressCodeModal.classList.add('hidden'));

    document.getElementById('addressCodeList')?.addEventListener('click', e => {
        if (e.target.classList.contains('edit-code-btn')) {
            openAddressCodeModal(e.target.dataset);
        }
        if (e.target.classList.contains('delete-code-btn')) {
            const id = e.target.dataset.id;
            showConfirmation('Are you sure you want to delete this mapping?', async () => {
                const result = await postData('delete_address_code', { id });
                if (result.success) {
                    showMessage('Mapping deleted.');
                    renderAddressCodes();
                }
            });
        }
    });

    addressCodeForm?.addEventListener('submit', async e => {
        e.preventDefault();
        const id = addressCodeIdInput.value;
        const formData = new FormData(addressCodeForm);
        const action = id ? 'update_address_code' : 'add_address_code';

        const result = await postData(action, formData);
        if (result.success) {
            showMessage(`Mapping ${id ? 'updated' : 'added'}.`);
            addressCodeModal.classList.add('hidden');
            renderAddressCodes();
        } else {
            showMessage(result.message || 'An error occurred.', true);
        }
    });

    renderAddressCodes();
    
    document.getElementById('processBulkAddProductsBtn')?.addEventListener('click', async () => {
        const bulkInput = document.getElementById('bulkAddProductsInput');
        if (!bulkInput.value.trim()) return showMessage('Bulk data is empty.', true);
        showLoader();
        const result = await postData('bulk_add_products', { data: bulkInput.value.trim() });
        hideLoader();
        if (result.success) { await refreshDataCallback(); bulkInput.value = ''; showMessage(result.message); }
    });

    document.getElementById('processBulkUpdateStockBtn')?.addEventListener('click', async () => {
        const bulkInput = document.getElementById('bulkUpdateStockInput');
        const location = document.getElementById('adminLocFilter').value;
        if (!bulkInput.value.trim()) return showMessage('Bulk data is empty.', true);
        showLoader();
        const result = await postData('bulk_update_stock', { data: bulkInput.value.trim(), location: location });
        hideLoader();
        if (result.success) { await refreshDataCallback(); bulkInput.value = ''; showMessage(result.message); }
    });
    
    document.getElementById('processBulkAddStockBtn')?.addEventListener('click', async () => {
        const bulkInput = document.getElementById('bulkAddStockInput');
        const location = document.getElementById('adminLocFilter').value;
        
        if (!bulkInput.value.trim()) return showMessage('Bulk data is empty.', true);
        
        showConfirmation(`Add these quantities to ${location} inventory? This will NOT reset existing stock.`, async () => {
            showLoader();
            const result = await postData('bulk_add_stock', { data: bulkInput.value.trim(), location: location });
            hideLoader();
            if (result.success) { 
                await refreshDataCallback(); 
                bulkInput.value = ''; 
                showMessage(result.message); 
            } else {
                showMessage(result.message || 'Failed to add stock.', true);
            }
        });
    });

    // --- NEW: No Price Bulk Add Handler ---
    document.getElementById('processBulkAddStockNoPriceBtn')?.addEventListener('click', async () => {
        const bulkInput = document.getElementById('bulkAddStockNoPriceInput');
        const location = document.getElementById('adminLocFilter').value;
        
        if (!bulkInput.value.trim()) return showMessage('Bulk data is empty.', true);
        
        showConfirmation(`Add these quantities to ${location} inventory? (Prices will NOT be updated).`, async () => {
            showLoader();
            // Use the NEW action here
            const result = await postData('bulk_add_stock_no_price', { data: bulkInput.value.trim(), location: location });
            hideLoader();
            if (result.success) { 
                await refreshDataCallback(); 
                bulkInput.value = ''; 
                showMessage(result.message); 
            } else {
                showMessage(result.message || 'Failed to add stock.', true);
            }
        });
    });

    document.getElementById('processBulkAddAliasBtn')?.addEventListener('click', async () => {
        const bulkInput = document.getElementById('bulkAddAliasInput');
        if (!bulkInput.value.trim()) return showMessage('Bulk alias data is empty.', true);
        showLoader();
        const result = await postData('bulk_add_aliases', { data: bulkInput.value.trim() });
        hideLoader();
        if (result.success) { await refreshDataCallback(); bulkInput.value = ''; showMessage(result.message); }
    });
    
    document.getElementById('parsePdfTextBtn')?.addEventListener('click', async () => {
        const rawText = document.getElementById('rawPdfInput').value;
        const location = document.getElementById('pdfParseLocation').value;
        const bu = document.getElementById('pdfParseBu').value;

        if (!location) {
            return showMessage("Please select an Order Location first.", true);
        }
        if (!rawText.trim()) {
            return showMessage("Please paste text from the PDF first.", true);
        }

        localStorage.setItem('lastPdfLocation', location);
        localStorage.setItem('lastPdfBu', bu);
        
        showLoader();
        const formData = new FormData();
        formData.append('text', rawText);
        try {
            const response = await fetch('parse_order_text.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                const orderData = result.data;
                orderData.location = location;
                orderData.bu = bu;
                
                sessionStorage.setItem('pdfOrderData', JSON.stringify(orderData));
                window.location.href = 'pdf_to_order.php';
            } else { 
                showMessage(result.message || "Failed to parse text.", true); 
            }
        } catch (error) {
            showMessage("An error occurred while parsing.", true);
        } finally {
            hideLoader();
        }
    });

    document.getElementById('runUnlinkedSkuReportBtn')?.addEventListener('click', async () => {
        showLoader();
        const resultsContainer = document.getElementById('unlinkedSkuResults');
        const resultsList = document.getElementById('unlinkedSkuList');
        const selectedLoc = document.getElementById('adminLocFilter').value;
        const result = await postData('get_unlinked_skus', { location: selectedLoc });
        hideLoader();
        if (result.success && Array.isArray(result.data)) {
            resultsList.innerHTML = result.data.length > 0 ? result.data.map(item => `
                <tr class="text-sm">
                    <td class="px-4 py-2 font-mono">${item.sku}</td>
                    <td class="px-4 py-2">${item.description}</td>
                    <td class="px-4 py-2 font-medium text-slate-700">${item.current_stock ?? 0}</td>
                </tr>
            `).join('') : '<tr><td colspan="3" class="text-center py-4 text-slate-500">Good job! No unlinked SKUs found.</td></tr>';
            resultsContainer.classList.remove('hidden');
        }
    });

    document.getElementById('exportCsvBtn')?.addEventListener('click', () => exportData('csv'));
    document.getElementById('exportTsvBtn')?.addEventListener('click', () => exportData('tsv'));

    const displayMonthSelect = document.getElementById('displayMonth');
    const displayYearSelect = document.getElementById('displayYear');
    const setDisplayMonthBtn = document.getElementById('setDisplayMonthBtn');

    function loadDisplayMonthSetting() {
        displayMonthSelect.value = new Date().getMonth() + 1;
        displayYearSelect.value = new Date().getFullYear();
    }

    setDisplayMonthBtn?.addEventListener('click', async () => {
        const month = displayMonthSelect.value;
        const year = displayYearSelect.value;
        showConfirmation(`Set the global dashboard display to ${displayMonthSelect.options[displayMonthSelect.selectedIndex].text} ${year}?`, async () => {
            showLoader();
            const result = await postData('set_display_month', { month, year });
            hideLoader();
            if (result.success) {
                showMessage(result.message);
            } else {
                showMessage(result.message || 'Failed to update setting.', true);
            }
        });
    });

    loadDisplayMonthSetting();
}