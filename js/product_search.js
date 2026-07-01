import { postData, fetchData } from './api.js';

let debounceTimer;
let suggestTimer;
let currentLocation = 'all';

async function handleSearch() {
    // We removed the auto-hide logic here so your suggestions stay open 
    // until you explicitly click one or click away from the search bar.
    
    const input = document.getElementById('product-search-input');
    const resultsBody = document.getElementById('search-results-body');
    const searchTerm = input.value.trim();

    // Read new filter values
    const filterData = {
        term: searchTerm,
        month: document.getElementById('psMonthFilter').value,
        year: document.getElementById('psYearFilter').value,
        customer: document.getElementById('psCustomerFilter').value,
        location: currentLocation,
        address: document.getElementById('psAddressFilter').value.trim()
    };

    if (searchTerm.length < 3) {
        resultsBody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-slate-500">Please enter at least 3 characters to search.</td></tr>`;
        document.getElementById('psTotalPO').textContent = '0';
        document.getElementById('psTotalServedPO').textContent = '0';
        document.getElementById('psTotalUnservedPO').textContent = '0';
        document.getElementById('psTotalQty').textContent = '0';
        document.getElementById('psTotalServedQty').textContent = '0';
        document.getElementById('psTotalUnservedQty').textContent = '0';
        document.getElementById('psTotalAmount').textContent = '₱0.00';
        document.getElementById('psTotalServedAmount').textContent = '₱0.00';
        document.getElementById('psTotalUnservedAmount').textContent = '₱0.00';
        return;
    }

    resultsBody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-slate-500">Searching...</td></tr>`;

    try {
        // Send filters to the API
        const result = await postData('search_pos_by_product', filterData); 
        if (result.success) {
            renderResults(result.data || []);
        } else {
            resultsBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-red-500">Error: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error("Search failed:", error);
        resultsBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-red-500">An error occurred during the search.</td></tr>`;
    }
}

function renderResults(items) {
    const resultsBody = document.getElementById('search-results-body');
    const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { style: 'currency', currency: 'PHP' });

    // Calculate Totals
    let totalQty = 0, totalServedQty = 0, totalUnservedQty = 0;
    let totalAmount = 0, totalServedAmount = 0, totalUnservedAmount = 0;
    
    // Track unique POs
    let uniquePOs = new Set();
    let servedPOs = new Set();
    let unservedPOs = new Set();

    items.forEach(item => {
        const quantity = parseInt(item.quantity) || 0;
        const price = parseFloat(item.price) || 0;
        const orderId = item.order_id; // Using order_id as the unique PO identifier
        
        // Add to absolute totals
        uniquePOs.add(orderId);
        totalQty += quantity;
        totalAmount += price;
        
        if (item.status === 'served') {
            servedPOs.add(orderId);
            totalServedQty += quantity;
            totalServedAmount += price;
        } else {
            unservedPOs.add(orderId);
            totalUnservedQty += quantity;
            totalUnservedAmount += price;
        }
    });

    // Update Summary Cards
    document.getElementById('psTotalPO').textContent = uniquePOs.size.toLocaleString('en-US');
    document.getElementById('psTotalServedPO').textContent = servedPOs.size.toLocaleString('en-US');
    document.getElementById('psTotalUnservedPO').textContent = unservedPOs.size.toLocaleString('en-US');
    
    document.getElementById('psTotalQty').textContent = totalQty.toLocaleString('en-US');
    document.getElementById('psTotalServedQty').textContent = totalServedQty.toLocaleString('en-US');
    document.getElementById('psTotalUnservedQty').textContent = totalUnservedQty.toLocaleString('en-US');
    
    document.getElementById('psTotalAmount').textContent = formatCurrency(totalAmount);
    document.getElementById('psTotalServedAmount').textContent = formatCurrency(totalServedAmount);
    document.getElementById('psTotalUnservedAmount').textContent = formatCurrency(totalUnservedAmount);

    // Render table
    if (items.length === 0) {
        resultsBody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-slate-500">No purchase orders found for this item with the selected filters.</td></tr>`;
        return;
    }

    resultsBody.innerHTML = items.map(item => {
        const statusClass = item.status === 'served' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        return `
            <tr>
                <td data-label="Order Date">${new Date(item.order_date).toLocaleDateString()}</td>
                <td data-label="Customer / PO">
                    <div class="font-bold text-slate-800">${item.customer_name}</div>
                    <div class="text-xs text-slate-500">PO: ${item.po_number}</div>
                </td>
                <td data-label="Location">
                    <span class="px-2 py-1 text-xs font-medium bg-slate-100 text-slate-600 rounded-md border border-slate-200">${item.location || 'N/A'}</span>
                </td>
                <td data-label="Matched Item">
                    <div>${item.description}</div>
                    <div class="font-mono text-xs text-slate-500">${item.sku}</div>
                </td>
                <td data-label="Qty" class="text-center">${item.quantity}</td>
                <td data-label="Status" class="text-center">
                    <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full ${statusClass}">
                        ${item.status}
                    </span>
                </td>
                <td data-label="Action" class="text-right">
                    <a href="view_order.php?id=${item.order_id}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">View Order</a>
                </td>
            </tr>
        `;
    }).join('');
} // <-- This closing } was missing

// NEW: Function to load customer data for the filter
async function loadPageData() {
    const result = await fetchData('get_customers');
    if (result.success && Array.isArray(result.data)) {
        const customerFilter = document.getElementById('psCustomerFilter');
        // Check if customerFilter exists to prevent errors
        if (customerFilter) {
            customerFilter.innerHTML = '<option value="all">All Customers</option>'; // Reset options
            customerFilter.innerHTML += result.data
                .map(c => `<option value="${c.name}">${c.name}</option>`)
                .join('');
        }
    }
}

function init() {
    loadPageData(); // Load customers when page inits

    const searchInput = document.getElementById('product-search-input');
    const suggestionsBox = document.getElementById('search-suggestions');

    // Handle Input for both Suggestions and Main Search
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.trim();
        
        // Handle Suggestions Request
        clearTimeout(suggestTimer);
        if (term.length >= 2) {
            suggestTimer = setTimeout(() => fetchSuggestions(term), 300);
        } else {
            suggestionsBox.classList.add('hidden');
        }

        // Handle Main Table Search 
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(handleSearch, 600);
    });

    let addressSuggestTimer;
    const addressInput = document.getElementById('psAddressFilter');
    const addressSuggestionsBox = document.getElementById('address-suggestions');

    // Handle Address Input and Auto-suggest
    if (addressInput) {
        addressInput.addEventListener('input', (e) => {
            const term = e.target.value.trim();
            
            clearTimeout(addressSuggestTimer);
            if (term.length >= 2) {
                addressSuggestTimer = setTimeout(() => fetchAddressSuggestions(term), 300);
            } else {
                addressSuggestionsBox.classList.add('hidden');
            }

            // Also trigger the main table search when typing
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(handleSearch, 600);
        });
    }

    // Close suggestions dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.classList.add('hidden');
        }
        if (addressInput && addressSuggestionsBox && !addressInput.contains(e.target) && !addressSuggestionsBox.contains(e.target)) {
            addressSuggestionsBox.classList.add('hidden');
        }
    });

    // Handle Location Button UI & Logic
    document.querySelectorAll('.loc-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.loc-btn').forEach(b => {
                b.classList.remove('active-loc', 'bg-white', 'shadow', 'text-pink-600');
                b.classList.add('text-slate-500');
            });
            e.target.classList.remove('text-slate-500');
            e.target.classList.add('active-loc', 'bg-white', 'shadow', 'text-pink-600');
            
            currentLocation = e.target.dataset.loc;
            handleSearch();
        });
    });

    ['psMonthFilter', 'psYearFilter', 'psCustomerFilter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.addEventListener('change', handleSearch); }
    });
}

// Function to fetch and render autocomplete search suggestions
async function fetchSuggestions(term) {
    const suggestionsBox = document.getElementById('search-suggestions');
    const result = await postData('get_product_suggestions', { term: term });
    
    // Check if the input was cleared while the API request was still loading
    const currentInput = document.getElementById('product-search-input').value.trim();
    if (currentInput.length < 2) {
        suggestionsBox.classList.add('hidden');
        return;
    }
    
    if (result.success && result.data && result.data.length > 0) {
        suggestionsBox.innerHTML = result.data.map(item => {
            const safeDesc = item.description ? item.description.replace(/"/g, '&quot;') : '';
            return `
            <div class="suggestion-item p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0 transition-colors" data-desc="${safeDesc}">
                <div class="font-bold text-sm text-slate-800">${item.description}</div>
            </div>
        `}).join('');
        
        // Add click listeners to apply the chosen suggestion
        document.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const desc = e.currentTarget.dataset.desc;
                document.getElementById('product-search-input').value = desc; // Autofill product name
                suggestionsBox.classList.add('hidden');
                handleSearch(); // Trigger actual table search instantly
            });
        });
        
        suggestionsBox.classList.remove('hidden');
    } else {
        // Show a "Not Found" state instead of collapsing to prevent UI blinking
        suggestionsBox.innerHTML = `
            <div class="p-4 text-sm text-slate-500 text-center italic bg-slate-50">
                No matching products found.
            </div>
        `;
        suggestionsBox.classList.remove('hidden');
    }
}

// Function to fetch and render address suggestions
async function fetchAddressSuggestions(term) {
    const suggestionsBox = document.getElementById('address-suggestions');
    const result = await postData('get_address_suggestions', { term: term });
    
    // Check if input was cleared while loading
    const currentInput = document.getElementById('psAddressFilter').value.trim();
    if (currentInput.length < 2) {
        suggestionsBox.classList.add('hidden');
        return;
    }
    
    if (result.success && result.data && result.data.length > 0) {
        suggestionsBox.innerHTML = result.data.map(item => {
            const safeAddr = item.address ? item.address.replace(/"/g, '&quot;') : '';
            return `
            <div class="address-suggestion-item p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0 transition-colors text-sm text-slate-800" data-addr="${safeAddr}">
                ${item.address}
            </div>
        `}).join('');
        
        // Add click listeners to apply the chosen address
        document.querySelectorAll('.address-suggestion-item').forEach(item => {
            item.addEventListener('click', (e) => {
                document.getElementById('psAddressFilter').value = e.currentTarget.dataset.addr;
                suggestionsBox.classList.add('hidden');
                handleSearch(); // Trigger main search
            });
        });
        
        suggestionsBox.classList.remove('hidden');
    } else {
        // Not Found state
        suggestionsBox.innerHTML = `
            <div class="p-3 text-sm text-slate-500 text-center italic bg-slate-50">
                No addresses found.
            </div>
        `;
        suggestionsBox.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', init);