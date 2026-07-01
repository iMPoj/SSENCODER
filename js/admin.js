// js/admin.js

const API_URL = 'api.php';
let customersCache = [];

// ==========================================
// 1. API HELPERS
// ==========================================

async function fetchData(action) {
    try {
        const response = await fetch(`${API_URL}?action=${action}`);
        // Handle non-JSON responses (like HTML errors)
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Server returned an invalid response.");
        }
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || `HTTP error! status: ${response.status}`);
        return result;
    } catch (error) {
        console.error(`Could not fetch ${action}:`, error);
        return { success: false, message: error.message, data: [] };
    }
}

async function postData(action, data) {
    try {
        const formData = new FormData();
        formData.append('action', action);

        if (data instanceof FormData) {
            for (const [key, value] of data.entries()) {
                if (key !== 'action') formData.append(key, value);
            }
        } else {
            for (const key in data) {
                formData.append(key, data[key]);
            }
        }

        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData,
        });

        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Server returned an invalid response (likely HTML error).");
        }

        const result = await response.json();
        if (!response.ok) throw new Error(result.message || `HTTP error! status: ${response.status}`);
        return result;
    } catch (error) {
        console.error(`Could not post ${action}:`, error);
        return { success: false, message: error.message };
    }
}

// ==========================================
// 2. MAIN INITIALIZATION
// ==========================================

function initAdmin() {
    // --- Tabs ---
    const inventoryAdminBtn = document.getElementById('inventoryAdminBtn');
    const pdfAdminBtn = document.getElementById('pdfAdminBtn');
    const translatorAdminBtn = document.getElementById('translatorAdminBtn');
    const customerAdminBtn = document.getElementById('customerAdminBtn');
    const exportAdminBtn = document.getElementById('exportAdminBtn');
    const targetsAdminBtn = document.getElementById('targetsAdminBtn');
    const productsAdminBtn = document.getElementById('productsAdminBtn');
    const customerLvsAdminBtn = document.getElementById('customerLvsAdminBtn');
    const converterAdminBtn = document.getElementById('converterAdminBtn');
    const mergerAdminBtn = document.getElementById('mergerAdminBtn');

    // --- Sections ---
    const adminInventorySection = document.getElementById('adminInventorySection');
    const adminBulkOrderSection = document.getElementById('adminBulkOrderSection');
    const adminTranslatorSection = document.getElementById('admin-tab-translator');
    const adminCustomerSection = document.getElementById('adminCustomerSection');
    const adminExportSection = document.getElementById('adminExportSection');
    const adminTargetsSection = document.getElementById('adminTargetsSection');
    const adminProductsSection = document.getElementById('productsSection');
    const adminCustomerLvsSection = document.getElementById('customerLvsSection');
    const adminConverterSection = document.getElementById('adminConverterSection');
    const adminMergerSection = document.getElementById('adminMergerSection');

    // --- Tab Switching Logic ---
    function hideAllSections() {
        [adminInventorySection, adminBulkOrderSection, adminTranslatorSection, adminCustomerSection, adminExportSection, adminTargetsSection, adminProductsSection, adminCustomerLvsSection, adminConverterSection, adminMergerSection].forEach(el => {
            if (el) el.classList.add('hidden');
        });

        [inventoryAdminBtn, pdfAdminBtn, translatorAdminBtn, customerAdminBtn, exportAdminBtn, targetsAdminBtn, productsAdminBtn, customerLvsAdminBtn, converterAdminBtn, mergerAdminBtn].forEach(btn => {
            if (btn) {
                btn.classList.remove('active', 'border-b-2', 'border-[#E42278]', 'text-[#E42278]');
                btn.classList.add('text-[#6B7280]', 'border-transparent');
            }
        });
    }

    function activateTab(btn, section) {
        if (!btn || !section) return;
        hideAllSections();
        section.classList.remove('hidden');
        btn.classList.add('active', 'border-b-2', 'border-[#E42278]', 'text-[#E42278]');
        btn.classList.remove('text-[#6B7280]', 'border-transparent');
    }

    // --- Event Listeners for Tabs ---
    if (inventoryAdminBtn) inventoryAdminBtn.addEventListener('click', () => activateTab(inventoryAdminBtn, adminInventorySection));
    if (pdfAdminBtn) pdfAdminBtn.addEventListener('click', () => activateTab(pdfAdminBtn, adminBulkOrderSection));
    if (translatorAdminBtn) translatorAdminBtn.addEventListener('click', () => activateTab(translatorAdminBtn, adminTranslatorSection));
    if (exportAdminBtn) {
        exportAdminBtn.addEventListener('click', () => {
            activateTab(exportAdminBtn, adminExportSection);
            loadCustomers(); // Fetch customers so the dropdowns populate!
        });
    }

    if (customerAdminBtn) {
        customerAdminBtn.addEventListener('click', () => {
            activateTab(customerAdminBtn, adminCustomerSection);
            loadCustomers();
            loadAddressCodes();
        });
    }

    if (targetsAdminBtn) {
        targetsAdminBtn.addEventListener('click', () => {
            activateTab(targetsAdminBtn, adminTargetsSection);
            loadMonthlyTargets();
        });
    }

    if (productsAdminBtn) {
        productsAdminBtn.addEventListener('click', () => {
            activateTab(productsAdminBtn, adminProductsSection);
            loadAdminProducts();
        });
    }

    // ★ ADDED: Store LVs Tab Listener
    if (customerLvsAdminBtn) {
        customerLvsAdminBtn.addEventListener('click', () => {
            activateTab(customerLvsAdminBtn, adminCustomerLvsSection);
            loadCustomerLvs();
        });
    }

    if (converterAdminBtn) {
        converterAdminBtn.addEventListener('click', () => activateTab(converterAdminBtn, adminConverterSection));
    }

    if (mergerAdminBtn) {
        mergerAdminBtn.addEventListener('click', () => activateTab(mergerAdminBtn, adminMergerSection));
    }
    // --- PDF PARSER PERSISTENCE & LOGIC (UPDATED) ---
    const pdfLocSelect = document.getElementById('pdfParseLocation');
    const pdfBuSelect = document.getElementById('pdfParseBu');

    // 1. Load saved preferences on init
    if (pdfLocSelect) {
        const savedLoc = localStorage.getItem('pref_pdf_location');
        if (savedLoc) pdfLocSelect.value = savedLoc;

        // Save on change
        pdfLocSelect.addEventListener('change', (e) => {
            localStorage.setItem('pref_pdf_location', e.target.value);
        });
    }

    if (pdfBuSelect) {
        const savedBu = localStorage.getItem('pref_pdf_bu');
        if (savedBu) pdfBuSelect.value = savedBu;

        // Save on change
        pdfBuSelect.addEventListener('change', (e) => {
            localStorage.setItem('pref_pdf_bu', e.target.value);
        });
    }

    // Parse PDF Text Button Logic
    const parsePdfTextBtn = document.getElementById('parsePdfTextBtn');
    if (parsePdfTextBtn) {
        parsePdfTextBtn.addEventListener('click', async () => {
            const bu = document.getElementById('pdfParseBu').value;
            const text = document.getElementById('rawPdfInput').value.trim();

            if (!bu) return alert('Please select a Business Unit.');
            if (!text) return alert('Please paste the PDF text content.');

            // Disable button to prevent double clicks
            const originalText = parsePdfTextBtn.innerText;
            parsePdfTextBtn.disabled = true;
            parsePdfTextBtn.innerText = "Analyzing...";

            try {
                // 2. Send text to server for parsing BEFORE navigating
                const formData = new FormData();
                formData.append('text', text);

                const response = await fetch('parse_order_text.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();


                // 3. Validation: The API now returns an array of orders
                if (result.success && result.data && result.data.length > 0) {

                    // Merge user selections into EVERY order in the array
                    const finalOrders = result.data.map(order => ({
                        ...order,
                        // Prioritize the Smart Scanner location. If it completely fails, default to Davao.
                        location: order.location || 'Davao',
                        bu: bu
                    }));

                    // Save the QUEUE to session storage
                    sessionStorage.setItem('pdfOrderQueue', JSON.stringify(finalOrders));
                    sessionStorage.setItem('pdfOrderIndex', 0); // Start at the first order

                    // Navigate
                    window.location.href = 'pdf_to_order.php';

                } else {
                    // 4. Handle Failure (No items)
                    let errorMsg = result.message || "Parsing failed.";
                    if (result.data && result.data.items && result.data.items.length === 0) {
                        errorMsg = "No valid items found in the pasted text.\nPlease check if you copied the correct columns (Vendor Item Code, Description, etc).";
                    }
                    alert(errorMsg);
                }

            } catch (e) {
                console.error("Parse Error:", e);
                alert("An error occurred while parsing the text. Please try again.");
            } finally {
                // Re-enable button
                parsePdfTextBtn.disabled = false;
                parsePdfTextBtn.innerText = originalText;
            }
        });
    }

    // --- OTHER EVENT LISTENERS (Standard Admin Features) ---

    // Bulk Stock (Qty Only) Helpers
    const setupBulkAddStockNoPrice = (btnId, location) => {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.onclick = async () => {
            const data = document.getElementById('bulkAddStockNoPriceInput').value;
            if (!data) return alert('Please enter data.');
            if (!confirm(`Are you sure you want to ADD this stock to ${location}?`)) return;

            const ogText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Processing...';
            try {
                const result = await postData('bulk_add_stock_no_price', { data, location });
                alert(result.success ? result.message : 'Error: ' + result.message);
                if (result.success) document.getElementById('bulkAddStockNoPriceInput').value = '';
            } catch (error) { console.error(error); alert('An error occurred.'); }
            finally { btn.disabled = false; btn.innerText = ogText; }
        };
    };
    setupBulkAddStockNoPrice('processBulkAddStockNoPriceDavaoBtn', 'Davao');
    setupBulkAddStockNoPrice('processBulkAddStockNoPriceGensanBtn', 'Gensan');

    // Bulk Update (Reset) Helpers
    const setupBulkUpdateStock = (btnId, location) => {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.onclick = async () => {
            const data = document.getElementById('bulkUpdateStockInput').value;
            if (!data) return alert('Please enter data.');
            if (!confirm(`WARNING: This will RESET stock for ${location} to the exact values provided. Continue?`)) return;

            const ogText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Processing...';
            try {
                const result = await postData('bulk_update_stock', { data, location });
                alert(result.success ? result.message : 'Error: ' + result.message);
                if (result.success) document.getElementById('bulkUpdateStockInput').value = '';
            } catch (error) { console.error(error); alert('An error occurred.'); }
            finally { btn.disabled = false; btn.innerText = ogText; }
        };
    };
    setupBulkUpdateStock('processBulkUpdateStockDavaoBtn', 'Davao');
    setupBulkUpdateStock('processBulkUpdateStockGensanBtn', 'Gensan');

    // Run Unlinked SKU Report Helpers
    const setupUnlinkedSkuReport = (btnId, location) => {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.onclick = async () => {
            const ogText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Running...';
            try {
                const result = await postData('get_unlinked_skus', { location });
                const list = document.getElementById('unlinkedSkuList');
                const container = document.getElementById('unlinkedSkuResults');
                list.innerHTML = '';
                if (result.success && result.data.length > 0) {
                    result.data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td class="p-3 font-mono">${item.sku}</td><td class="p-3">${item.description}</td><td class="p-3 font-bold">${item.current_stock}</td>`;
                        list.appendChild(row);
                    });
                    container.classList.remove('hidden');
                } else {
                    alert(`No unlinked SKUs found for ${location}!`);
                    container.classList.add('hidden');
                }
            } catch (e) { console.error(e); alert('Error running report.'); }
            finally { btn.disabled = false; btn.innerText = ogText; }
        };
    };
    setupUnlinkedSkuReport('runUnlinkedSkuReportDavaoBtn', 'Davao');
    setupUnlinkedSkuReport('runUnlinkedSkuReportGensanBtn', 'Gensan');

    // Add Customer
    const addCustomerForm = document.getElementById('addCustomerForm');
    if (addCustomerForm) {
        addCustomerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('newCustomerName').value;
            if (!name) return;
            try {
                const result = await postData('add_customer', { name });
                if (result.success) {
                    document.getElementById('newCustomerName').value = '';
                    loadCustomers();
                } else { alert(result.message); }
            } catch (e) { console.error(e); alert('Error adding customer'); }
        });
    }

    // Add Address Code
    const addAddressCodeBtn = document.getElementById('addAddressCodeBtn');
    if (addAddressCodeBtn) {
        addAddressCodeBtn.addEventListener('click', () => {
            openAddressMappingModal(); // Opens empty modal for new addition
        });
    }
    // Salesman Name → Code autofill
    const salesmanCodes = {
        'RONALD LOPEZ (MAS 106)': 'SSDB06',
        'GLENN BUCAG (KAS 101)': 'SSDB07',
        'REDEMSON DULAY (MAS 102)': 'SSDB02',
        'NORMAN SAMODIO (MAS 105)': 'SSDB05',
        'JOSE PEPITO ORTEGA (MAS 104) GS': 'SSGB01'
    };
    const salesmanNameSel = document.getElementById('mappingSalesmanName');
    if (salesmanNameSel) {
        salesmanNameSel.addEventListener('change', function () {
            const codeField = document.getElementById('mappingSalesmanCode');
            if (codeField) {
                codeField.value = salesmanCodes[this.value] || '';
            }
        });
    }

    // Export Buttons
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    if (exportCsvBtn) exportCsvBtn.addEventListener('click', () => handleUnifiedExport('sales', 'exportCsvBtn'));

    const exportIssuesBtn = document.getElementById('exportIssuesBtn');
    if (exportIssuesBtn) exportIssuesBtn.addEventListener('click', () => handleUnifiedExport('issues', 'exportIssuesBtn'));

    const exportUnservedBtn = document.getElementById('exportUnservedBtn');
    if (exportUnservedBtn) exportUnservedBtn.addEventListener('click', () => handleUnifiedExport('unserved', 'exportUnservedBtn'));

    const exportFulfillableBtn = document.getElementById('exportFulfillableBtn');
    if (exportFulfillableBtn) exportFulfillableBtn.addEventListener('click', () => handleUnifiedExport('fulfillable', 'exportFulfillableBtn'));

    const exportCustomersCsvBtn = document.getElementById('exportCustomersCsvBtn');
    if (exportCustomersCsvBtn) exportCustomersCsvBtn.addEventListener('click', handleCustomersExport);

    const exportProductsCsvBtn = document.getElementById('exportProductsCsvBtn');
    if (exportProductsCsvBtn) exportProductsCsvBtn.addEventListener('click', handleProductsExport);

    const exportNpImportBtn = document.getElementById('exportNpImportBtn');
    if (exportNpImportBtn) {
        exportNpImportBtn.addEventListener('click', handleNpImportExport);

        // Set default time to start and end of today
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        const todayDateStr = now.toISOString().slice(0, 10); // Gets just YYYY-MM-DD

        const npStart = document.getElementById('npExportStart');
        const npEnd = document.getElementById('npExportEnd');

        if (npStart) npStart.value = todayDateStr + 'T00:00'; // Start of today (12:00 AM)
        if (npEnd) npEnd.value = todayDateStr + 'T23:59';   // End of today (11:59 PM)
    }
    // Set Display Month
    const setDisplayMonthBtn = document.getElementById('setDisplayMonthBtn');
    if (setDisplayMonthBtn) {
        setDisplayMonthBtn.addEventListener('click', async () => {
            const m = document.getElementById('displayMonth').value;
            const y = document.getElementById('displayYear').value;
            try {
                const result = await postData('set_display_month', { month: m, year: y });
                alert(result.message);
            } catch (e) { console.error(e); alert('Error setting date.'); }
        });
    }

    // Database Import / Refresh
    const importDatabaseForm = document.getElementById('importDatabaseForm');
    if (importDatabaseForm) {
        importDatabaseForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fileInput = document.getElementById('sqlFile');
            if (!fileInput.files.length) return alert('Please select a .sql file first.');

            if (!confirm('WARNING: This will overwrite your current database with the uploaded file. Are you absolutely sure?')) return;

            const btn = document.getElementById('importDbBtn');
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Importing...';

            const formData = new FormData();
            formData.append('sql_file', fileInput.files[0]);

            try {
                const result = await postData('import_database', formData);
                alert(result.message);
                if (result.success) {
                    fileInput.value = ''; // clear input
                    window.location.reload(); // refresh to show new data
                }
            } catch (e) {
                console.error(e);
                alert('An error occurred during import.');
            } finally {
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    }

    // Save Targets
    const targetsForm = document.getElementById('targetsForm');
    if (targetsForm) {
        targetsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const inputs = document.querySelectorAll('.target-input');
            const targets = [];
            inputs.forEach(input => {
                targets.push({
                    location: input.dataset.location,
                    bu: input.dataset.bu,
                    amount: input.value
                });
            });
            try {
                const result = await postData('set_monthly_targets', { targets: JSON.stringify(targets) });
                alert(result.message);
            } catch (e) { console.error(e); alert('Error saving targets.'); }
        });
    }

    // Product Search (New Tab)
    const productSearchInput = document.getElementById('productSearchInput');
    if (productSearchInput) {
        productSearchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#adminProductTableBody tr');
            rows.forEach(row => {
                // Check against the hidden data-search attribute that contains SKUs, or fallback to innerText
                const searchableText = row.getAttribute('data-search') || row.innerText.toLowerCase();
                row.style.display = searchableText.includes(term) ? '' : 'none';
            });
        });
    }
}


// ==========================================
// 3. EXISTING HELPER FUNCTIONS
// ==========================================

async function loadCustomers() {
    const list = document.getElementById('customerManagementList');
    if (!list) return;
    list.innerHTML = '<div class="text-center p-4">Loading...</div>';
    try {
        const result = await fetchData('get_customers');
        if (result.success) {
            customersCache = result.data; // Store for the address mapping dropdown
            populateCustomerSelect();     // Update the dropdown

            list.innerHTML = '';
            result.data.forEach(c => {
                const div = document.createElement('div');
                div.className = 'flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 bg-white p-3 rounded-lg border border-[rgba(13,17,26,0.08)]';
                div.innerHTML = `
                    <div class="flex items-center gap-3">
                        <input type="checkbox" ${c.is_priority ? 'checked' : ''} onchange="togglePriority(${c.id}, this.checked)" class="rounded text-[#E42278] focus:ring-[#E42278]">
                        <span class="text-sm font-medium text-[#0D111A] truncate max-w-[180px]" title="${c.name}">${c.name}</span>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50 p-1.5 rounded-md border border-gray-100">
                        <button onclick="deleteCustomer(${c.id})" class="text-red-400 hover:text-red-600 bg-white p-1 rounded shadow-sm border border-gray-100" title="Delete Customer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                `;
                list.appendChild(div);
            });
        }
    } catch (e) { console.error(e); list.innerHTML = '<div class="text-red-500">Error loading customers</div>'; }
}

function populateCustomerSelect() {
    const select = document.getElementById('mappingCustomer');
    const exportList = document.getElementById('exportCustomerList');
    const npExportList = document.getElementById('npExportCustomerList');

    if (select) {
        select.innerHTML = '<option value="">Select a customer...</option>';
        customersCache.forEach(c => {
            const option = document.createElement('option');
            option.value = c.id;
            option.textContent = c.name;
            select.appendChild(option);
        });
    }

    const generateCheckboxes = (prefix) => {
        let html = `
            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors border border-transparent hover:border-gray-100">
                <input type="checkbox" value="all" class="${prefix}-checkbox w-4 h-4 rounded text-[#E42278] focus:ring-[#E42278]" checked>
                <span class="font-bold text-[#0D111A]">All Customers</span>
            </label>
            <div class="my-1 border-t border-gray-100"></div>
        `;
        html += customersCache.map(c => `
            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer ${prefix}-item-label transition-colors">
                <input type="checkbox" value="${c.name.replace(/"/g, '&quot;')}" class="${prefix}-checkbox w-4 h-4 rounded text-[#E42278] focus:ring-[#E42278]">
                <span class="text-gray-700 truncate" title="${c.name.replace(/"/g, '&quot;')}">${c.name}</span>
            </label>
        `).join('');
        return html;
    };

    if (exportList) {
        exportList.innerHTML = generateCheckboxes('exportCustomer');
        setupCustomDropdownLogic('exportCustomer');
    }

    if (npExportList) {
        npExportList.innerHTML = generateCheckboxes('npExportCustomer');
        setupCustomDropdownLogic('npExportCustomer');
    }
}

function setupCustomDropdownLogic(prefix) {
    const container = document.getElementById(`${prefix}Container`);
    const btn = document.getElementById(`${prefix}Btn`);
    const dropdown = document.getElementById(`${prefix}Dropdown`);
    const search = document.getElementById(`${prefix}Search`);
    const checkboxes = document.querySelectorAll(`.${prefix}-checkbox`);
    const label = document.getElementById(`${prefix}Label`);

    if (!btn || !dropdown) return;

    btn.onclick = (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            if (search) {
                search.focus();
                search.value = '';
            }
            document.querySelectorAll(`.${prefix}-item-label`).forEach(lbl => lbl.style.display = 'flex');
        }
    };

    document.addEventListener('click', (e) => {
        if (container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    if (search) {
        search.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll(`.${prefix}-item-label`).forEach(lbl => {
                const text = lbl.textContent.toLowerCase();
                lbl.style.display = text.includes(term) ? 'flex' : 'none';
            });
        });
    }

    const allCheckbox = Array.from(checkboxes).find(cb => cb.value === 'all');
    const otherCheckboxes = Array.from(checkboxes).filter(cb => cb.value !== 'all');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', (e) => {
            if (e.target.value === 'all' && e.target.checked) {
                otherCheckboxes.forEach(c => c.checked = false);
            } else if (e.target.checked) {
                if (allCheckbox) allCheckbox.checked = false;
            }

            const selected = otherCheckboxes.filter(c => c.checked);
            if (selected.length === 0) {
                if (allCheckbox) allCheckbox.checked = true;
                label.textContent = 'All Customers';
                label.classList.remove('font-bold', 'text-[#E42278]');
            } else if (selected.length === 1) {
                label.textContent = selected[0].value;
                label.classList.add('font-bold', 'text-[#E42278]');
            } else {
                label.textContent = `${selected.length} Customers Selected`;
                label.classList.add('font-bold', 'text-[#E42278]');
            }
        });
    });
}

async function loadAddressCodes() {
    const list = document.getElementById('addressCodeList');
    if (!list) return;
    list.innerHTML = '<tr><td colspan="4" class="p-4 text-center">Loading...</td></tr>';
    try {
        const result = await fetchData('get_address_codes');
        if (result.success) {
            list.innerHTML = '';
            result.data.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const safeAddr = escapeHtml(item.address || '');
                const safeCode = escapeHtml(item.customer_code || '');
                const customerName = item.customer_name || '<span class="text-red-400 italic">Unassigned</span>';

                row.innerHTML = `
                    <td class="px-6 py-4 font-bold text-[#0D111A]">${customerName}</td>
                    <td class="px-6 py-4 font-medium text-[#6B7280]">${item.address}</td>
                    <td class="px-6 py-4 font-mono text-xs text-blue-600 bg-blue-50/50 rounded">${item.customer_code}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button onclick="openAddressMappingModal('${item.id}', '${item.customer_id || ''}', '${safeAddr}', '${safeCode}', '${(item.salesman_name || '').replace(/'/g, "\\'")}', '${(item.salesman_code || '').replace(/'/g, "\\'")}')" class="text-blue-500 hover:text-blue-700 text-xs font-bold uppercase">Edit</button>
                        <button onclick="deleteAddressCode(${item.id})" class="text-red-400 hover:text-red-600 text-xs font-bold uppercase">Delete</button>
                    </td>
                `;
                list.appendChild(row);
            });
        }
    } catch (e) { console.error(e); list.innerHTML = '<tr><td colspan="4" class="text-red-500 p-4">Error loading data</td></tr>'; }
}

// Modal Handlers
window.openAddressMappingModal = function (id = '', customerId = '', address = '', code = '', salesman_name = '', salesman_code = '', location = 'Davao') {
    document.getElementById('mappingId').value = id;
    document.getElementById('mappingCustomer').value = customerId;
    document.getElementById('mappingAddress').value = address;
    document.getElementById('mappingCode').value = code;
    document.getElementById('mappingSalesmanName').value = salesman_name || '';
    document.getElementById('mappingSalesmanCode').value = salesman_code || '';

    // Set the location dropdown safely
    if (document.getElementById('customerLocationInput')) {
        document.getElementById('customerLocationInput').value = location || 'Davao';
    }

    document.getElementById('addressMappingModalTitle').innerText = id ? 'Edit Address Mapping' : 'Add Address Mapping';
    document.getElementById('addressMappingModal').classList.remove('hidden');
};

window.closeAddressMappingModal = function () {
    document.getElementById('addressMappingModal').classList.add('hidden');
};

window.handleAddressMappingSubmit = async function (e) {
    e.preventDefault();

    const id = document.getElementById('mappingId').value;
    const customer_id = document.getElementById('mappingCustomer').value;
    const address = document.getElementById('mappingAddress').value;
    const customer_code = document.getElementById('mappingCode').value;
    const salesman_name = document.getElementById('mappingSalesmanName').value;
    const salesman_code = document.getElementById('mappingSalesmanCode').value;

    const action = id ? 'update_address_code' : 'add_address_code';
    const payload = { customer_id, address, customer_code, salesman_name, salesman_code };
    if (id) payload.id = id;

    try {
        const result = await postData(action, payload);
        if (result.success) {
            closeAddressMappingModal();
            loadAddressCodes();
        } else {
            alert(result.message);
        }
    } catch (err) {
        console.error(err);
        alert("Error saving address mapping.");
    }
};

window.deleteAddressCode = async function (id) {
    if (!confirm("Delete this mapping?")) return;
    try {
        const result = await postData('delete_address_code', { id });
        if (result.success) loadAddressCodes();
        else alert(result.message);
    } catch (e) { console.error(e); alert('Error deleting'); }
};

async function loadMonthlyTargets() {
    try {
        const result = await fetchData('get_monthly_targets');
        if (result.success) {
            document.querySelectorAll('.target-input').forEach(i => i.value = '');
            result.data.forEach(t => {
                const id = `target-${t.location.toLowerCase()}-${t.bu.toLowerCase()}`;
                const el = document.getElementById(id);
                if (el) el.value = t.target_amount;
            });
        }
    } catch (e) { console.error(e); }
}

async function handleUnifiedExport(exportType, btnId) {
    const btnElement = document.getElementById(btnId);
    const loc = document.getElementById('exportLoc').value;
    const m = document.getElementById('exportMonth').value;
    const y = document.getElementById('exportYear').value;

    let cust = 'all';
    const allCheckbox = document.querySelector('.exportCustomer-checkbox[value="all"]');
    if (allCheckbox && !allCheckbox.checked) {
        const selected = Array.from(document.querySelectorAll('.exportCustomer-checkbox:checked')).map(cb => cb.value);
        if (selected.length > 0) cust = JSON.stringify(selected);
    }

    const originalText = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = 'Generating...';

    try {
        const result = await postData('get_orders_for_export', { export_type: exportType, location: loc, month: m, year: y, customer: cust });
        if (result.success && result.data.length > 0) {
            downloadFile(result.data, 'csv', `${exportType}_export_${loc}_${m}_${y}.csv`);
        } else {
            alert(result.message || "No data found for the selected criteria.");
        }
    } catch (e) { console.error(e); alert("Error exporting data."); }
    finally { btnElement.disabled = false; btnElement.innerHTML = originalText; }
}

async function handleCustomersExport() {
    const btn = document.getElementById('exportCustomersCsvBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Generating...';
    try {
        const result = await fetchData('get_address_codes');
        if (result.success && result.data.length > 0) {
            let content = "Customer Name,Address,Customer Code,Salesman Name,Salesman Code\n";
            result.data.forEach(row => {
                content += `"${row.customer_name || ''}","${row.address || ''}","${row.customer_code || ''}","${row.salesman_name || ''}","${row.salesman_code || ''}"\n`;
            });
            const blob = new Blob(["\uFEFF" + content], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'customers_export.csv';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } else {
            alert("No customers found.");
        }
    } catch (e) { console.error(e); alert("Error exporting customers."); }
    finally { btn.disabled = false; btn.innerHTML = originalText; }
}

async function handleProductsExport() {
    const btn = document.getElementById('exportProductsCsvBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Generating...';
    try {
        const result = await fetchData('get_admin_products');
        if (result.success && result.data.length > 0) {
            let content = "Product ID,Description,BU,Code Type,Code,Pieces Per Case,Sales Price\n";
            result.data.forEach(p => {
                if (p.codes && p.codes.length > 0) {
                    p.codes.forEach(c => {
                        content += `"${p.id}","${p.description || ''}","${p.bu || ''}","${c.type || ''}","${c.code || ''}","${c.pieces_per_case || 1}","${c.sales_price || 0}"\n`;
                    });
                } else {
                    content += `"${p.id}","${p.description || ''}","${p.bu || ''}","","","",""\n`;
                }
            });
            const blob = new Blob(["\uFEFF" + content], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'products_export.csv';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } else {
            alert("No products found.");
        }
    } catch (e) { console.error(e); alert("Error exporting products."); }
    finally { btn.disabled = false; btn.innerHTML = originalText; }
}

function downloadFile(data, format, filename) {
    let content = "";

    // 1. Updated headers matching exact requested layout: ONLY ONE SO Number & Remarks per item
    const headers = [
        "BU", "Location", "PO Number", "Order Date", "Customer Name", "Address", "Customer Code", "Salesman",
        "SKU", "Description", "Quantity", "Status", "Status Reason", "Sales Price", "Discount %",
        "VAT EX w/o Disc (Base Price)", "VAT IN w/o Disc", "VAT EX w/ Disc", "VAT IN w/ Disc (Net Price)", "SO Number", "Remarks"
    ];

    const delimiter = format === 'csv' ? "," : "\t";
    content += headers.join(delimiter) + "\n";

    const escapeField = (field) => {
        if (field === null || field === undefined) return '""';
        let str = String(field);
        str = str.replace(/"/g, '""');
        return `"${str}"`;
    };

    // 2. Group items by Order ID to recreate the View Order page layout
    const orders = {};
    data.forEach(row => {
        const orderKey = row.order_id || (row.po_number + '_' + row.customer_name);
        if (!orders[orderKey]) orders[orderKey] = [];
        orders[orderKey].push(row);
    });

    // 3. For each order, simulate the EXACT chunking logic of view_order.php
    Object.values(orders).forEach(orderItems => {

        // Chunk all items into original pages of 12
        const original_pages = [];
        for (let i = 0; i < orderItems.length; i += 12) {
            original_pages.push(orderItems.slice(i, i + 12));
        }

        const standard_pages = [];
        const all_fulfilled = [];

        // Extract fulfilled items, but keep standard pages locked in place
        original_pages.forEach(page => {
            const cur_std = [];
            page.forEach(item => {
                const status = item.item_status || item.status || '';
                if (status === 'fulfilled') {
                    all_fulfilled.push(item);
                } else {
                    cur_std.push(item);
                }
            });
            standard_pages.push(cur_std);
        });

        // Group the fulfilled items at the end
        const fulfilled_pages = [];
        if (all_fulfilled.length > 0) {
            for (let i = 0; i < all_fulfilled.length; i += 12) {
                fulfilled_pages.push(all_fulfilled.slice(i, i + 12));
            }
        }

        const final_pages = standard_pages.concat(fulfilled_pages);

        // 4. Output lines. Because we grouped them exactly like the UI, pageIndex flawlessly matches the SO arrays
        final_pages.forEach((pageItems, pageIndex) => {
            pageItems.forEach(row => {

                let soList = [];
                if (row.so_number) {
                    try {
                        const parsed = JSON.parse(row.so_number);
                        soList = Array.isArray(parsed) ? parsed : [parsed];
                    } catch (e) { soList = [row.so_number]; }
                }

                let remarksList = [];
                if (row.remarks) {
                    try {
                        const parsed = JSON.parse(row.remarks);
                        remarksList = Array.isArray(parsed) ? parsed : [parsed];
                    } catch (e) { remarksList = [row.remarks]; }
                }

                // Grab the SO and Remark precisely tied to this specific item's page!
                const specificSO = escapeField(soList[pageIndex] || '');
                const specificRemark = escapeField(remarksList[pageIndex] || '');

                const effectiveStatus = (row.order_status === 'deleted' || row.order_status === 'cancelled') ? row.order_status : (row.item_status || row.status);

                const line = [
                    escapeField(row.bu),
                    escapeField(row.location),
                    escapeField(row.po_number),
                    escapeField(row.order_date),
                    escapeField(row.customer_name),
                    escapeField(row.customer_address),
                    escapeField(row.customer_code),
                    escapeField(row.salesman_name),
                    escapeField(row.sku),
                    escapeField(row.description),
                    row.quantity || 0,
                    escapeField(effectiveStatus),
                    escapeField(row.cancel_reason || ''),
                    parseFloat(row.sales_price || 0).toFixed(2),
                    row.discount_percentage || 0,
                    parseFloat(row.vat_ex_wo_disc || 0).toFixed(2),
                    parseFloat(row.vat_in_wo_disc || 0).toFixed(2),
                    parseFloat(row.vat_ex_w_disc || 0).toFixed(2),
                    parseFloat(row.vat_in_w_disc || 0).toFixed(2),
                    specificSO,
                    specificRemark
                ];

                content += line.join(delimiter) + "\n";
            });
        });
    });

    const bom = "\uFEFF";
    const blob = new Blob([bom + content], { type: format === 'csv' ? 'text/csv;charset=utf-8;' : 'text/tab-separated-values;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

async function handleProductsExport() {
    const btn = document.getElementById('exportProductsCsvBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Generating...';
    try {
        const result = await fetchData('get_admin_products');
        if (result.success && result.data.length > 0) {
            let content = "Product ID,Description,BU,Code Type,Code,Pieces Per Case,Sales Price\n";
            result.data.forEach(p => {
                if (p.codes && p.codes.length > 0) {
                    p.codes.forEach(c => {
                        content += `"${p.id}","${p.description || ''}","${p.bu || ''}","${c.type || ''}","${c.code || ''}","${c.pieces_per_case || 1}","${c.sales_price || 0}"\n`;
                    });
                } else {
                    content += `"${p.id}","${p.description || ''}","${p.bu || ''}","","","",""\n`;
                }
            });
            const blob = new Blob(["\uFEFF" + content], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = 'products_export.csv';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } else {
            alert("No products found.");
        }
    } catch (e) { console.error(e); alert("Error exporting products."); }
    finally { btn.disabled = false; btn.innerHTML = originalText; }
}

async function handleNpImportExport() {
    const start = document.getElementById('npExportStart').value;
    const end = document.getElementById('npExportEnd').value;
    const loc = document.getElementById('npExportLoc') ? document.getElementById('npExportLoc').value : 'all';
    const status = document.getElementById('npExportStatus') ? document.getElementById('npExportStatus').value : 'served';
    const bu = document.getElementById('npExportBu') ? document.getElementById('npExportBu').value : 'all';
    let cust = 'all';
    const allCheckbox = document.querySelector('.npExportCustomer-checkbox[value="all"]');
    if (allCheckbox && !allCheckbox.checked) {
        const selected = Array.from(document.querySelectorAll('.npExportCustomer-checkbox:checked')).map(cb => cb.value);
        if (selected.length > 0) cust = JSON.stringify(selected);
    }

    if (!start || !end) return alert("Please select both a Start and End date and time.");

    const btn = document.getElementById('exportNpImportBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Generating Files...';

    try {
        // Pass the customer, location, BU, and status to the API
        const result = await postData('get_np_import_data', {
            start_date: start,
            end_date: end,
            customer: cust,
            location: loc,
            status: status,
            bu: bu
        });

        if (result.success && result.data.length > 0) {
            const categoryMap = { 'Nutri': 'IFCN', 'Health': 'RW', 'Hygiene': 'HYGIENE' };

            // Step 1: Group items by Order ID
            let orders = {};
            result.data.forEach(item => {
                if (!orders[item.order_id]) orders[item.order_id] = [];
                orders[item.order_id].push(item);
            });

            // Step 2: Separate items into File chunks (Max 12 per PO, per file)
            let fileChunks = {};

            // ★ Helper: turn an item into the NP Import row
            const buildRow = (item) => {
                let dateObj = new Date(item.order_date);
                let formattedDate = `${dateObj.getFullYear()}/${dateObj.getMonth() + 1}/${dateObj.getDate()}`;
                return [
                    formattedDate,
                    item.customer_code || '',
                    item.salesman_code || '',
                    categoryMap[item.bu] || item.bu || '',
                    '00',
                    item.po_number || '',
                    '_',
                    item.sku || '',
                    item.quantity || 0
                ];
            };

            Object.values(orders).forEach(orderItems => {
                if (status === 'fulfilled') {
                    // ★ FULFILLED EXEMPTION: filter FIRST, then chunk compactly per order.
                    // This treats all fulfilled items in an order as one continuous list
                    // (regardless of the page they came from) and only spills into
                    // PART2/3 when the fulfilled count actually exceeds 12.
                    const fulfilledOnly = orderItems.filter(it => it.status === 'fulfilled');
                    fulfilledOnly.forEach((item, index) => {
                        let fileNum = Math.floor(index / 12) + 1;
                        if (!fileChunks[fileNum]) fileChunks[fileNum] = [];
                        fileChunks[fileNum].push(buildRow(item));
                    });
                } else {
                    // Original behavior for 'served' and 'all':
                    // Chunk by ORIGINAL position so served items keep their natural NP slot,
                    // then drop items that don't match the requested status.
                    orderItems.forEach((item, index) => {
                        let fileNum = Math.floor(index / 12) + 1;
                        if (status !== 'all' && item.status !== status) return;
                        if (!fileChunks[fileNum]) fileChunks[fileNum] = [];
                        fileChunks[fileNum].push(buildRow(item));
                    });
                }
            });

            const headers = [
                "Order Date (Date) (YYYY/MM/DD)",
                "Customer Code (nv20)",
                "Route Code (nv20)",
                "Product Category Code (nv20)",
                "Ship To (nv40)",
                "Order Number (nv20)",
                "Remarks (nv50)",
                "Product Code (nv20)",
                '"Quantity (numeric 25,4)"'
            ];

            for (const fileNum in fileChunks) {
                let content = headers.join(",") + "\n";

                fileChunks[fileNum].forEach(r => {
                    let escapedRow = r.map((field) => {
                        let str = String(field);
                        if (str.includes(',')) str = `"${str.replace(/"/g, '""')}"`;
                        return str;
                    });
                    content += escapedRow.join(",") + "\n";
                });

                const blob = new Blob(["\uFEFF" + content], { type: 'text/csv;charset=utf-8;' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;

                let fileName = Object.keys(fileChunks).length === 1
                    ? `TXN_ORDER_${loc.toUpperCase()}.CSV`
                    : `TXN_ORDER_${loc.toUpperCase()}_PART${fileNum}.CSV`;

                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                await new Promise(res => setTimeout(res, 300));
            }
            alert(`Export complete! Generated ${Object.keys(fileChunks).length} file(s).`);

        } else {
            alert("No fulfilled records found for this location and date range.");
        }
    } catch (e) {
        console.error(e);
        alert("Error exporting NP Import files.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ==========================================
// 4. PRODUCT MANAGEMENT FUNCTIONS
// ==========================================

window.loadAdminProducts = async function () {
    const tableBody = document.getElementById('adminProductTableBody');
    if (!tableBody) return;
    tableBody.innerHTML = '<tr><td colspan="4" class="p-6 text-center text-gray-400 italic">Loading products...</td></tr>';

    try {
        const result = await fetchData('get_admin_products');
        if (result.success && result.data) {
            window.renderProductTable(result.data);
        } else {
            tableBody.innerHTML = `<tr><td colspan="4" class="p-6 text-center text-red-400">${result.message || 'No products found'}</td></tr>`;
        }
    } catch (error) {
        console.error("Error loading products:", error);
        tableBody.innerHTML = '<tr><td colspan="4" class="p-6 text-center text-red-400">Error connecting to server.</td></tr>';
    }
}

window.renderProductTable = function (products) {
    const tableBody = document.getElementById('adminProductTableBody');
    if (!tableBody) return;
    tableBody.innerHTML = '';

    products.forEach(p => {
        let buBadge = 'bg-gray-100 text-gray-600';
        if (p.bu === 'Health') buBadge = 'bg-blue-100 text-blue-700';
        if (p.bu === 'Hygiene') buBadge = 'bg-emerald-100 text-emerald-700';
        if (p.bu === 'Nutri') buBadge = 'bg-orange-100 text-orange-700';

        // Map all SKUs/Barcodes to a string for searching
        const codesString = p.codes ? p.codes.map(c => c.code).join(' ') : '';
        const searchString = `${p.description} ${codesString}`.toLowerCase();

        const row = document.createElement('tr');
        row.className = 'hover:bg-purple-50 transition-colors group';
        row.setAttribute('data-search', searchString); // Hide data here for the search listener
        row.innerHTML = `
            <td class="p-4 font-bold text-[#0D111A]">${p.description}</td>
            <td class="p-4"><span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider ${buBadge}">${p.bu}</span></td>
            <td class="p-4"><span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-xs font-bold border border-gray-200">${p.codes ? p.codes.length : 0} Codes</span></td>
            <td class="p-4 text-right">
                <div class="flex justify-end gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="editProductBase(${p.id}, '${escapeHtml(p.description)}', '${p.bu}')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100">
                        Edit Info
                    </button>
                    <button onclick="manageSkus(${p.id})" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 border border-purple-100">
                        Manage Codes
                    </button>
                    <button onclick="deleteProductFull(${p.id})" 
                            class="p-1.5 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50" title="Delete Product">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function escapeHtml(text) {
    if (!text) return "";
    return text.replace(/'/g, "\\'");
}

// ================= PRODUCT MODAL =================

window.openProductModal = function () {
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productModalTitle').innerText = 'Add Product';
    document.getElementById('productModal').classList.remove('hidden');
}

window.editProductBase = function (id, description, bu) {
    document.getElementById('productId').value = id;
    document.getElementById('productDescription').value = description;
    document.getElementById('productBu').value = bu;
    document.getElementById('productModalTitle').innerText = 'Edit Product';
    document.getElementById('productModal').classList.remove('hidden');
}

window.closeProductModal = function () {
    document.getElementById('productModal').classList.add('hidden');
}

window.handleProductSubmit = async function (e) {
    e.preventDefault();
    const data = {
        id: document.getElementById('productId').value,
        description: document.getElementById('productDescription').value,
        bu: document.getElementById('productBu').value
    };

    try {
        const result = await postData('save_product_base', data);
        if (result.success) {
            closeProductModal();
            loadAdminProducts();
        } else {
            alert(result.message);
        }
    } catch (error) { console.error(error); alert('Failed to save product'); }
}

window.deleteProductFull = async function (id) {
    if (!confirm('DANGER: This will delete the product, ALL its SKUs/Barcodes, and ALL Inventory levels. This cannot be undone.\n\nAre you sure?')) return;
    try {
        const result = await postData('delete_product_full', { id: id });
        if (result.success) loadAdminProducts();
        else alert(result.message);
    } catch (error) { console.error(error); alert('Error deleting product'); }
}

// ================= SKU MANAGER =================

let currentManagingProductId = null;

window.manageSkus = async function (productId) {
    currentManagingProductId = productId;
    const modal = document.getElementById('skuManagerModal');
    const tableBody = document.getElementById('skuTableBody');
    const title = document.getElementById('skuManagerProductName');

    modal.classList.remove('hidden');
    title.innerText = 'Loading...';
    tableBody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-gray-400">Loading codes...</td></tr>';

    resetSkuForm();

    try {
        // Fetch product details AND inventory stocks concurrently
        const [result, stockResult] = await Promise.all([
            fetchData('get_product_details&id=' + productId),
            fetchData('get_stock_for_product&product_id=' + productId)
        ]);

        if (result.success) {
            title.innerText = result.data.description;
            document.getElementById('skuProductId').value = productId;

            const stockMap = stockResult.success ? stockResult.data : {};
            renderSkuTable(result.data.codes || [], stockMap);
        }
    } catch (error) {
        console.error(error);
        tableBody.innerHTML = '<tr><td colspan="4" class="p-6 text-center text-red-400">Error loading details.</td></tr>';
    }
}

window.renderSkuTable = function (skus, stockMap = {}) {
    const tableBody = document.getElementById('skuTableBody');
    tableBody.innerHTML = '';
    if (!skus || skus.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-gray-400 border-2 border-dashed border-gray-100 rounded-xl bg-gray-50/50 m-4">No codes found. Add one on the left.</td></tr>';
        return;
    }

    skus.forEach(sku => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 transition-colors group';

        let typeBadge = sku.type === 'sku'
            ? '<span class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-2 py-1 rounded">SKU</span>'
            : '<span class="text-[10px] font-black uppercase tracking-widest text-gray-500 bg-gray-100 px-2 py-1 rounded">BARCODE</span>';

        const dStock = stockMap[sku.code]?.Davao || 0;
        const gStock = stockMap[sku.code]?.Gensan || 0;

        // Pass the current stock values to the edit function safely
        const safeSku = JSON.stringify(sku).replace(/'/g, "&apos;");

        row.innerHTML = `
            <td class="p-4 font-mono font-bold text-gray-700">${sku.code}</td>
            <td class="p-4">${typeBadge}</td>
            <td class="p-4 font-mono text-xs">${parseFloat(sku.sales_price).toFixed(2)}</td>
            <td class="p-4 text-xs font-mono text-gray-500">
                <span class="text-blue-600 font-bold" title="Davao Stock">D:${dStock}</span> / 
                <span class="text-emerald-600 font-bold" title="Gensan Stock">G:${gStock}</span>
            </td>
            <td class="p-4 text-right">
                <div class="flex justify-end gap-3 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick='editSku(${safeSku}, ${dStock}, ${gStock})' class="text-xs font-bold text-blue-600 hover:text-blue-800 uppercase">Edit</button>
                    <button onclick="deleteSkuById(${sku.id})" class="text-xs font-bold text-red-400 hover:text-red-600 uppercase">Delete</button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

window.editSku = function (sku, davaoStock = 0, gensanStock = 0) {
    document.getElementById('skuId').value = sku.id;
    document.getElementById('skuCode').value = sku.code;
    document.getElementById('skuType').value = sku.type;
    document.getElementById('skuPrice').value = sku.sales_price;
    document.getElementById('skuPcs').value = sku.pieces_per_case;

    // Populate stocks
    document.getElementById('skuStockDavao').value = davaoStock;
    document.getElementById('skuStockGensan').value = gensanStock;

    document.getElementById('cancelSkuEditBtn').classList.remove('hidden');
    const submitBtn = document.getElementById('skuSubmitBtn');
    submitBtn.innerText = 'Update Code & Stock';
    submitBtn.classList.remove('!bg-emerald-500', 'hover:!bg-emerald-600');
    submitBtn.classList.add('!bg-blue-600', 'hover:!bg-blue-700');
}

window.resetSkuForm = function () {
    document.getElementById('skuForm').reset();
    document.getElementById('skuId').value = '';
    document.getElementById('skuProductId').value = currentManagingProductId;

    // Clear stocks
    document.getElementById('skuStockDavao').value = '';
    document.getElementById('skuStockGensan').value = '';

    document.getElementById('cancelSkuEditBtn').classList.add('hidden');

    const submitBtn = document.getElementById('skuSubmitBtn');
    submitBtn.innerText = 'Add Code & Stock';
    submitBtn.classList.add('!bg-emerald-500', 'hover:!bg-emerald-600');
    submitBtn.classList.remove('!bg-blue-600', 'hover:!bg-blue-700');
}

window.closeSkuManager = function () {
    document.getElementById('skuManagerModal').classList.add('hidden');
    loadAdminProducts();
}

window.handleSkuSubmit = async function (e) {
    e.preventDefault();
    const submitBtn = document.getElementById('skuSubmitBtn');
    const originalText = submitBtn.innerText;
    submitBtn.disabled = true;
    submitBtn.innerText = 'Saving...';

    const skuCode = document.getElementById('skuCode').value;
    const piecesPerCase = document.getElementById('skuPcs').value;

    const data = {
        sku_id: document.getElementById('skuId').value,
        product_id: document.getElementById('skuProductId').value,
        code: skuCode,
        type: document.getElementById('skuType').value,
        sales_price: document.getElementById('skuPrice').value,
        pieces_per_case: piecesPerCase
    };

    try {
        // 1. Save SKU details
        const result = await postData('save_sku', data);
        if (result.success) {

            // 2. Save Inventory Data seamlessly
            const davaoStock = document.getElementById('skuStockDavao').value;
            const gensanStock = document.getElementById('skuStockGensan').value;

            if (davaoStock !== '' || gensanStock !== '') {
                const stockData = {
                    sku: skuCode,
                    pcs: piecesPerCase,
                    stock_davao: davaoStock || 0,
                    stock_gensan: gensanStock || 0
                };
                await postData('update_inventory_row', stockData);
            }

            resetSkuForm();
            manageSkus(data.product_id);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error(error);
        alert('Error saving code or stock');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    }
}

window.deleteSkuById = async function (id) {
    if (!confirm('Delete this code?')) return;
    try {
        const result = await postData('delete_sku_by_id', { id: id });
        if (result.success) manageSkus(currentManagingProductId);
        else alert(result.message);
    } catch (error) { console.error(error); alert('Error deleting code'); }
}

window.submitBulkSalesman = async function () {
    const data = document.getElementById('bulkSalesmanData').value.trim();
    if (!data) return alert("Please paste data first.");

    try {
        const result = await postData('bulk_update_salesmen', { data: data });
        if (result.success) {
            alert(result.message);
            document.getElementById('bulkSalesmanData').value = '';
            document.getElementById('bulkSalesmanModal').classList.add('hidden');
            loadAddressCodes(); // Refresh table
        } else {
            alert(result.message);
        }
    } catch (e) {
        alert("Error updating salesmen.");
    }
}

// Function to load the new Store LVs tab
window.loadCustomerLvs = async function () {
    const tbody = document.getElementById('customerLvsTableBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Loading LVs...</td></tr>';
    try {
        const result = await fetchData('get_customer_lvs');
        if (result.success) {
            tbody.innerHTML = '';
            result.data.forEach(c => {
                const tr = document.createElement('tr');
                tr.className = "hover:bg-gray-50 transition-colors";
                tr.innerHTML = `
                    <td class="px-6 py-3 font-bold text-gray-800">${c.customer_name}</td>
                    <td class="px-6 py-3">
                        <input type="number" step="0.01" value="${c.nutri_limit || ''}" onchange="updateCustomerLv(${c.customer_id}, 'Nutri', this.value)" placeholder="No limit" class="glass-input !py-1 !px-2 text-xs w-28 font-bold text-amber-600">
                    </td>
                    <td class="px-6 py-3">
                        <input type="number" step="0.01" value="${c.health_limit || ''}" onchange="updateCustomerLv(${c.customer_id}, 'Health', this.value)" placeholder="No limit" class="glass-input !py-1 !px-2 text-xs w-28 font-bold text-indigo-600">
                    </td>
                    <td class="px-6 py-3">
                        <input type="number" step="0.01" value="${c.hygiene_limit || ''}" onchange="updateCustomerLv(${c.customer_id}, 'Hygiene', this.value)" placeholder="No limit" class="glass-input !py-1 !px-2 text-xs w-28 font-bold text-emerald-600">
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (e) {
        console.error(e);
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error loading LVs.</td></tr>';
    }
}

// Function to handle the auto-saving of Customer LVs per BU
window.updateCustomerLv = async function (customer_id, bu, limit) {
    try {
        const result = await postData('update_customer_lv', { customer_id: customer_id, bu: bu, lv_limit: limit });
        if (result.success) {
            console.log(`LV Limit updated for Customer #${customer_id}, BU: ${bu}`);
        } else {
            alert(result.message || "Failed to update LV Limit.");
        }
    } catch (e) {
        console.error(e);
        alert("Server error while updating LV Limit.");
    }
}

// ==========================================
// INVOICE TRANSLATOR LOGIC
// ==========================================

document.getElementById("transFileInput")?.addEventListener("change", function (event) {
    const file = event.target.files[0];
    if (file && file.type === "text/plain") {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("transInvoiceData").value = e.target.result;
            parseTranslatorInvoice(); // Automatically parse upon file selection
        };
        reader.readAsText(file);
    } else {
        alert("Please select a valid TXT file.");
    }
});

window.parseTranslatorInvoice = function () {
    const data = document.getElementById("transInvoiceData").value;
    const lines = data.split("\n");
    const tableBody = document.getElementById("transInvoiceTbody");

    if (!data.trim()) {
        tableBody.innerHTML = '<tr><td colspan="9" class="p-8 text-center text-xs text-gray-400 font-medium italic">No data to parse.</td></tr>';
        document.getElementById("transNetDisplay").textContent = "₱0.00";
        if (document.getElementById("addInvoiceStocksBtn")) document.getElementById("addInvoiceStocksBtn").classList.add("hidden");
        return;
    }

    tableBody.innerHTML = "";
    const itemsMap = {};

    lines.forEach((line) => {
        line = line.trim();
        if (!line) return;

        const match = line.match(/^(\d+)\s+(.+?)\s*(CS|PCS|PC|UNIT|EA)\s*([\d.,]+)\s*([\d.,]+)\s*([\d.,]+)\s*([\d.,]+)\s*([\d.,]+)\s*([\d.,]+)/i);

        if (match) {
            const itemCode = match[1];
            const description = match[2];
            const unit = match[3];
            const qtyUnit = parseFloat(match[4].replace(/,/g, "")) || 0;
            const qtyCS = parseFloat(match[5].replace(/,/g, "")) || 0;
            const unitPrice = parseFloat(match[6].replace(/,/g, "")) || 0;
            const netAmount = parseFloat(match[7].replace(/,/g, "")) || 0;
            const vat = parseFloat(match[8].replace(/,/g, "")) || 0;
            const totalAmount = parseFloat(match[9].replace(/,/g, "")) || 0;

            if (itemsMap[itemCode]) {
                itemsMap[itemCode].qtyUnit += qtyUnit;
                itemsMap[itemCode].qtyCS += qtyCS;
                itemsMap[itemCode].netAmount += netAmount;
                itemsMap[itemCode].vat += vat;
                itemsMap[itemCode].totalAmount += totalAmount;
            } else {
                itemsMap[itemCode] = { description, unit, qtyUnit, qtyCS, unitPrice, netAmount, vat, totalAmount };
            }
        }
    });

    Object.keys(itemsMap).forEach((itemCode) => {
        const item = itemsMap[itemCode];
        const row = document.createElement("tr");
        row.className = "hover:bg-gray-50 transition-colors group";

        const numFmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        row.innerHTML = `
            <td class="p-3 font-mono font-bold text-gray-500 group-hover:text-indigo-600 transition-colors">${itemCode}</td>
            <td class="p-3 font-bold text-gray-800">${item.description}</td>
            <td class="p-3 text-center text-[10px] font-black text-gray-400">${item.unit}</td>
            <td class="p-3 text-right font-medium text-gray-700 tabular-nums">${item.qtyUnit}</td>
            <td class="p-3 text-right font-black text-indigo-600 tabular-nums">${item.qtyCS}</td>
            <td class="p-3 text-right font-medium text-gray-400 tabular-nums">${numFmt(item.unitPrice)}</td>
            <td class="p-3 text-right font-bold text-gray-700 tabular-nums">${numFmt(item.netAmount)}</td>
            <td class="p-3 text-right font-medium text-gray-400 tabular-nums">${numFmt(item.vat)}</td>
            <td class="p-3 text-right font-black text-emerald-600 tabular-nums">${numFmt(item.totalAmount)}</td>
        `;
        tableBody.appendChild(row);
    });

    if (Object.keys(itemsMap).length === 0) {
        tableBody.innerHTML = '<tr><td colspan="9" class="p-8 text-center text-xs text-red-400 font-medium italic">Could not read any valid items. Make sure you pasted the raw TXT format.</td></tr>';
        if (document.getElementById("addInvoiceStocksBtn")) {
            document.getElementById("addInvoiceStocksBtn").classList.add("hidden");
            document.getElementById("addInvoiceStocksBtn").classList.remove("flex");
        }
        window.currentParsedInvoiceItems = {};
    } else {
        if (document.getElementById("addInvoiceStocksBtn")) {
            document.getElementById("addInvoiceStocksBtn").classList.remove("hidden");
            document.getElementById("addInvoiceStocksBtn").classList.add("flex");
        }
        window.currentParsedInvoiceItems = itemsMap;
    }

    const netMatch = data.match(/NET INVOICE AMOUNT\s+([0-9,]+\.\d{2})/i);
    if (netMatch) {
        document.getElementById("transNetDisplay").textContent = "₱" + netMatch[1];
    }
};

window.searchTranslatorTable = function () {
    const input = document.getElementById("transSearchInput").value.toUpperCase();
    const rows = document.querySelectorAll("#transInvoiceTbody tr");
    if (rows.length === 1 && rows[0].cells.length === 1) return;

    rows.forEach((row) => {
        const code = row.cells[0].textContent.toUpperCase();
        const desc = row.cells[1].textContent.toUpperCase();
        row.style.display = (code.includes(input) || desc.includes(input)) ? "" : "none";
    });
};

window.copyTranslatorTable = function () {
    const rows = document.querySelectorAll("#transInvoiceTbody tr");
    if (rows.length === 1 && rows[0].cells.length === 1) {
        alert("No data to copy!");
        return;
    }

    let tsv = "Item Code\tDescription\tUnit\tQty(U)\tQty(C)\tPrice\tNet\tVAT\tTotal\n";

    rows.forEach(row => {
        if (row.style.display !== "none") {
            const cells = Array.from(row.cells).map(cell => cell.textContent.trim());
            tsv += cells.join("\t") + "\n";
        }
    });

    const showSuccess = () => {
        const btn = document.getElementById("copyTransTableBtn");
        if (!btn) return;
        const originalText = btn.innerHTML;
        btn.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!`;
        btn.classList.replace("text-indigo-600", "text-emerald-600");
        btn.classList.replace("border-indigo-200", "border-emerald-200");
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.replace("text-emerald-600", "text-indigo-600");
            btn.classList.replace("border-emerald-200", "border-indigo-200");
        }, 2000);
    };

    const fallbackCopy = (text) => {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.opacity = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            if (document.execCommand('copy')) showSuccess();
            else alert("Copy failed. Please manually highlight and copy the table.");
        } catch (err) {
            alert("Browser blocked copying. Please manually highlight and copy the table.");
        }
        document.body.removeChild(textArea);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(tsv).then(showSuccess).catch(() => fallbackCopy(tsv));
    } else {
        fallbackCopy(tsv);
    }
};

window.saveTranslatorInvoice = async function () {
    const data = document.getElementById("transInvoiceData").value;
    if (!data.trim()) { alert("No invoice data to save!"); return; }

    let name = prompt("Enter invoice name (e.g., PO 12345):");
    if (!name) return;

    const deliveryDate = document.getElementById("transDeliveryDate")?.value || '';
    const bu = document.getElementById("transBuSelect")?.value || "Unknown";
    const loc = document.getElementById("transLocSelect")?.value || "Davao";

    try {
        const payload = {
            name: name,
            data: data,
            bu: bu,
            location: loc,
            delivery: deliveryDate
        };
        const result = await postData('save_translator_invoice', payload);
        if (result.success) {
            window.loadSavedInvoices();
        } else {
            alert(result.message || "Failed to save invoice.");
        }
    } catch (e) {
        alert("Server error saving invoice.");
    }
};

window.searchSavedInvoices = function () {
    const input = document.getElementById("transSavedSearch").value.toUpperCase();
    const listItems = document.querySelectorAll("#transSavedInvoices li.saved-invoice-item");

    listItems.forEach(li => {
        const name = li.querySelector(".trans-inv-name").textContent.toUpperCase();
        const bu = li.querySelector(".trans-inv-bu") ? li.querySelector(".trans-inv-bu").textContent.toUpperCase() : "";

        if (name.includes(input) || bu.includes(input)) {
            li.style.display = "";
        } else {
            li.style.display = "none";
        }
    });
};

window.loadSavedInvoices = async function () {
    const list = document.getElementById("transSavedInvoices");
    if (!list) return;

    list.innerHTML = '<li class="text-xs text-center py-4 text-gray-400">Loading saved invoices...</li>';

    try {
        const result = await fetchData('get_saved_invoices');
        if (!result.success) throw new Error();

        const invoices = result.data || [];
        window.cachedInvoices = invoices;
        list.innerHTML = "";

        if (invoices.length === 0) {
            list.innerHTML = '<li class="text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center py-6">No saved invoices</li>';
            return;
        }

        invoices.forEach((inv) => {
            let displayDate = "";
            if (inv.delivery_date) {
                const d = new Date(inv.delivery_date);
                displayDate = `Del: ${d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
            } else {
                const d = new Date(inv.created_at);
                displayDate = `Saved: ${d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
            }

            const isReceived = inv.is_received == 1;
            const borderClass = isReceived ? 'border-emerald-400 bg-emerald-50/50' : 'border-gray-100 bg-gray-50/80';

            // DAVAO OR GENSAN BADGE
            const safeLocation = inv.location || 'Davao';
            const locClass = safeLocation === 'Gensan' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-blue-50 text-blue-600 border-blue-200';
            const locBadge = `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-black uppercase border ${locClass}">${safeLocation}</span>`;

            // BU BADGE
            const buBadge = inv.bu ? `<span class="trans-inv-bu inline-block px-1.5 py-0.5 bg-indigo-50/80 rounded text-[8px] font-black text-indigo-600 uppercase border border-indigo-100">${inv.bu}</span>` : '';

            const statusBadge = isReceived
                ? `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[8px] font-black bg-emerald-100 text-emerald-700 uppercase"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> RECEIVED</span>`
                : `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[8px] font-black bg-amber-100 text-amber-700 uppercase"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> IN TRANSIT</span>`;

            const li = document.createElement("li");
            li.className = `saved-invoice-item p-3 rounded-xl border ${borderClass} flex flex-col gap-2 hover:bg-white transition-colors`;
            li.innerHTML = `
                <div class="flex justify-between items-start">
                    <div class="min-w-0 flex-1 pr-2">
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            ${statusBadge}
                            ${locBadge}
                        </div>
                        <h4 class="font-bold text-gray-800 text-xs truncate trans-inv-name" title="${inv.name}">${inv.name}</h4>
                        <div class="flex items-center gap-2 mt-1.5">
                            ${buBadge}
                            <p class="text-[9px] text-gray-500 font-mono uppercase font-bold tracking-wider">${displayDate}</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 shrink-0">
                        <div class="flex gap-1 justify-end">
                            <button onclick="renameTranslatorInvoice(${inv.id}, '${inv.name.replace(/'/g, "\\'")}')" class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors bg-white border border-gray-200 rounded-md shadow-sm" title="Rename"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                            <button onclick="downloadTranslatorInvoice(${inv.id})" class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors bg-white border border-gray-200 rounded-md shadow-sm" title="Download"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg></button>
                            <button onclick="deleteTranslatorInvoice(${inv.id}, '${inv.name.replace(/'/g, "\\'")}')" class="p-1.5 text-gray-400 hover:text-red-500 transition-colors bg-white border border-gray-200 rounded-md shadow-sm" title="Delete"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 mt-1">
                    <button onclick="openTranslatorInvoice(${inv.id})" class="flex-1 bg-white border border-gray-200 hover:border-indigo-300 hover:text-indigo-700 text-indigo-600 font-black text-[10px] uppercase tracking-widest py-2 rounded-lg transition-colors shadow-sm">Load Data</button>
                    <button onclick="toggleTranslatorInvoiceReceived(${inv.id}, ${isReceived ? 0 : 1})" class="flex-1 ${isReceived ? 'bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100'} font-black text-[10px] uppercase tracking-widest py-2 rounded-lg transition-colors shadow-sm">${isReceived ? 'Set Transit' : 'Set Rcvd'}</button>
                </div>
            `;
            list.appendChild(li);
        });
    } catch (e) {
        console.error(e);
        list.innerHTML = '<li class="text-xs text-red-500 text-center py-4">Error loading invoices</li>';
    }
};

window.openTranslatorInvoice = function (id) {
    const inv = window.cachedInvoices.find(i => i.id == id);
    if (!inv) return;

    // Load data silently
    document.getElementById("transInvoiceData").value = inv.invoice_data;

    // Parse it immediately
    parseTranslatorInvoice();
};

window.renameTranslatorInvoice = async function (id, currentName) {
    let newName = prompt("New name:", currentName);
    if (newName && newName !== currentName) {
        try {
            await postData('rename_saved_invoice', { id: id, name: newName });
            window.loadSavedInvoices();
        } catch (e) { }
    }
};

window.downloadTranslatorInvoice = function (id) {
    const inv = window.cachedInvoices.find(i => i.id == id);
    if (!inv) return;

    const blob = new Blob([inv.invoice_data], { type: "text/plain" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = inv.name.replace(/[^a-z0-9]/gi, "_").toLowerCase() + ".txt";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
};

window.deleteTranslatorInvoice = async function (id, name) {
    if (confirm(`Delete invoice "${name}"? This cannot be undone.`)) {
        try {
            await postData('delete_saved_invoice', { id: id });
            window.loadSavedInvoices();
        } catch (e) { }
    }
};

window.toggleTranslatorInvoiceReceived = async function (id, status) {
    try {
        await postData('toggle_invoice_received', { id: id, is_received: status });
        window.loadSavedInvoices();
    } catch (e) {
        alert("Failed to update status");
    }
};

window.addInvoiceToInventory = async function () {
    if (!window.currentParsedInvoiceItems || Object.keys(window.currentParsedInvoiceItems).length === 0) {
        return alert("No parsed items to add.");
    }

    const locSelect = document.getElementById("transLocSelect");
    const loc = locSelect ? locSelect.value : "Davao"; // Default fallback
    if (!loc) return alert("Please select a Location (Davao/Gensan).");

    const btn = document.getElementById("addInvoiceStocksBtn");
    const ogText = btn ? btn.innerHTML : "Add to Stocks";
    if (btn) { btn.disabled = true; btn.innerHTML = "Checking SKUs..."; }

    try {
        // 1. Fetch known products to compare SKUs
        const result = await fetchData('get_admin_products');
        if (!result.success) throw new Error("Failed to load products for verification.");

        const knownCodes = new Map();
        result.data.forEach(p => {
            if (p.codes) p.codes.forEach(c => knownCodes.set(c.code.toString().toUpperCase(), c));
        });

        const invoiceItems = Object.keys(window.currentParsedInvoiceItems);
        const missingCodes = [];
        const pcsUpdates = [];
        let dataString = "";

        // 2. Loop over every item in the parsed invoice
        invoiceItems.forEach(code => {
            const item = window.currentParsedInvoiceItems[code];
            const upperCode = code.toString().toUpperCase();

            if (!knownCodes.has(upperCode)) {
                // SKU is NOT in the database
                missingCodes.push(`${code} - ${item.description}`);
            } else {
                // SKU is valid! 
                // Format for bulk_add_stock_no_price expects: SKU \t Description \t Qty(U)
                dataString += `${code}\t${item.description}\t${item.qtyUnit}\n`;

                // Calculate Pieces Per Case = Qty(U) / Qty(C)
                if (item.qtyCS > 0 && item.qtyUnit > 0) {
                    let calculatedPcs = Math.round(item.qtyUnit / item.qtyCS);
                    if (calculatedPcs > 0) {
                        pcsUpdates.push({ code: code, pcs: calculatedPcs });
                    }
                }
            }
        });

        if (dataString.length === 0) {
            alert("No valid SKUs found in this invoice to add to inventory.");
            if (btn) { btn.disabled = false; btn.innerHTML = ogText; }
            return;
        }

        // 3. Prompt user if missing SKUs are found
        if (missingCodes.length > 0) {
            const msg = `WARNING: ${missingCodes.length} SKU(s) are NOT registered in your system:\n\n` +
                missingCodes.slice(0, 10).join("\n") +
                (missingCodes.length > 10 ? `\n...and ${missingCodes.length - 10} more.` : "") +
                `\n\nThese unregistered items will be IGNORED. Do you want to proceed and add the recognized items?`;

            if (!confirm(msg)) {
                if (btn) { btn.disabled = false; btn.innerHTML = ogText; }
                return;
            }
        } else {
            if (!confirm(`Are you sure you want to ADD these quantities to ${loc} Warehouse?`)) {
                if (btn) { btn.disabled = false; btn.innerHTML = ogText; }
                return;
            }
        }

        // 4. Send Stock Additions to Backend
        if (btn) btn.innerHTML = "Adding Stocks...";
        const addResult = await postData('bulk_add_stock_no_price', { data: dataString, location: loc });

        // 5. Send 'Pieces per Case' updates to Backend
        if (pcsUpdates.length > 0 && addResult.success) {
            if (btn) btn.innerHTML = "Updating Pcs/Case...";
            await postData('bulk_update_sku_pcs', { updates: JSON.stringify(pcsUpdates) });
        }

        alert(addResult.success ? addResult.message + (pcsUpdates.length > 0 ? `\nAuto-updated ${pcsUpdates.length} 'Pieces Per Case' configurations.` : "") : "Error: " + addResult.message);

    } catch (e) {
        console.error(e);
        alert("An error occurred while adding stocks.");
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = ogText; }
    }
};

document.addEventListener("DOMContentLoaded", window.loadSavedInvoices);