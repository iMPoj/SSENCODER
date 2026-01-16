import { postData, fetchData } from './api.js';

let debounceTimer;

async function handleSearch() {
    const input = document.getElementById('product-search-input');
    const resultsBody = document.getElementById('search-results-body');
    const searchTerm = input.value.trim();

    // Read new filter values
    const filterData = {
        term: searchTerm,
        month: document.getElementById('psMonthFilter').value,
        year: document.getElementById('psYearFilter').value,
        customer: document.getElementById('psCustomerFilter').value
    };

    if (searchTerm.length < 3) {
        resultsBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-slate-500">Please enter at least 3 characters to search.</td></tr>`;
        document.getElementById('psTotalServedQty').textContent = '0';
        document.getElementById('psTotalServedAmount').textContent = '₱0.00';
        document.getElementById('psTotalUnservedAmount').textContent = '₱0.00';
        return;
    }

    resultsBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-slate-500">Searching...</td></tr>`;

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
    let totalServedQty = 0;
    let totalServedAmount = 0;
    let totalUnservedAmount = 0;

    items.forEach(item => {
        const quantity = parseInt(item.quantity) || 0;
        const price = parseFloat(item.price) || 0; // 'price' is now available from the API
        
        if (item.status === 'served') {
            totalServedQty += quantity;
            totalServedAmount += price;
        } else {
            totalUnservedAmount += price;
        }
    });

    // Update Summary Cards
    document.getElementById('psTotalServedQty').textContent = totalServedQty.toLocaleString('en-US');
    document.getElementById('psTotalServedAmount').textContent = formatCurrency(totalServedAmount);
    document.getElementById('psTotalUnservedAmount').textContent = formatCurrency(totalUnservedAmount);

    // Render table
    if (items.length === 0) {
        resultsBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-slate-500">No purchase orders found for this item with the selected filters.</td></tr>`;
        return; // This 'return' was missing
    }

    // This block of code was missing
    resultsBody.innerHTML = items.map(item => {
        const statusClass = item.status === 'served' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        return `
            <tr>
                <td data-label="Order Date">${new Date(item.order_date).toLocaleDateString()}</td>
                <td data-label="Customer / PO">
                    <div class="font-bold text-slate-800">${item.customer_name}</div>
                    <div class="text-xs text-slate-500">PO: ${item.po_number}</div>
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

    // Add listeners to all filters
    const searchInput = document.getElementById('product-search-input');
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(handleSearch, 500);
    });

    ['psMonthFilter', 'psYearFilter', 'psCustomerFilter'].forEach(id => {
        // Check if element exists before adding listener
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', handleSearch);
        }
    });
}

document.addEventListener('DOMContentLoaded', init);