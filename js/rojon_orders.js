import { postData } from './api.js';

let currentLocation = 'all';
let currentBu = 'all';
let currentPage = 1;
let totalPages = 1;
let currentSearch = '';
let debounceTimer;

function getLoader() {
    return document.getElementById('loadingOverlay') || document.getElementById('loading-overlay');
}

async function fetchData() {
    const loader = getLoader();
    if (loader) {
        loader.classList.remove('hidden');
        loader.style.display = 'flex';
        loader.style.opacity = '1';
    }

    try {
        const formData = new FormData();
        formData.append('location', currentLocation);
        formData.append('bu', currentBu);
        formData.append('page', currentPage);
        formData.append('search', currentSearch);

        const result = await postData('get_rojon_orders', formData);

        if (result.success) {
            renderOrders(result.data || []);
            renderPagination(result.pagination || {});
        } else {
            const tbody = document.getElementById('orders-table-body');
            if (tbody) tbody.innerHTML = `<tr><td colspan="4" class="text-center py-12 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <p class="font-semibold text-slate-500">Error loading orders</p>
                <p class="text-sm mt-1">${result.message || 'Unknown error'}</p>
            </td></tr>`;
        }
    } catch (error) {
        console.error("Error fetching orders:", error);
        const tbody = document.getElementById('orders-table-body');
        if (tbody) tbody.innerHTML = `<tr><td colspan="4" class="text-center py-12 text-slate-400">
            <p class="font-semibold text-slate-500">Connection error</p>
            <p class="text-sm mt-1">Please check your connection and try again.</p>
        </td></tr>`;
    } finally {
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.classList.add('hidden');
                loader.style.display = 'none';
            }, 400);
        }
    }
}

function renderOrders(orders) {
    const tableBody = document.getElementById('orders-table-body');
    if (!tableBody) return;

    if (orders.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            <p class="font-semibold text-slate-500">No orders found</p>
            <p class="text-sm text-slate-400 mt-1">Try adjusting your filters or search term.</p>
        </td></tr>`;
        return;
    }

    tableBody.innerHTML = orders.map(order => {
        let soDisplay = 'N/A';
        if (order.so_number) {
            try {
                const soArray = JSON.parse(order.so_number);
                soDisplay = Array.isArray(soArray) ? (soArray.filter(s => s).join(', ') || 'N/A') : order.so_number;
            } catch (e) { soDisplay = order.so_number; }
        }
        const dateStr = new Date(order.order_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        return `
        <tr class="hover:bg-pink-50/30 transition-colors">
            <td data-label="Date" class="text-slate-500 text-sm">${dateStr}</td>
            <td data-label="PO Number / Address">
                <div class="font-semibold text-slate-800">${order.po_number}</div>
                <div class="text-xs text-slate-400 mt-0.5">${order.customer_address || ''}</div>
            </td>
            <td data-label="SO Number(s)" class="text-xs font-mono text-slate-600 whitespace-pre-wrap">${soDisplay}</td>
            <td class="text-center">
                <a href="view_order.php?id=${order.id}&context=rojon_dashboard" target="_blank"
                   class="inline-flex items-center gap-1 text-[#E42278] hover:text-[#C81E6A] font-semibold text-sm hover:underline transition-colors">
                    View
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </td>
        </tr>`;
    }).join('');
}

function renderPagination(pagination) {
    totalPages = pagination.totalPages || 1;
    currentPage = pagination.page || 1;
    const countEl = document.getElementById('order-count');
    if (countEl) countEl.textContent = `(${pagination.total || 0} orders)`;
    const container = document.getElementById('pagination-controls');
    if (!container || totalPages <= 1) {
        if (container) container.innerHTML = '';
        return;
    }
    container.innerHTML = `
        <button id="prev-page" class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''}>&laquo; Previous</button>
        <span class="text-sm text-slate-700 font-medium">Page ${currentPage} of ${totalPages}</span>
        <button id="next-page" class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''}>Next &raquo;</button>
    `;
    document.getElementById('prev-page')?.addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('next-page')?.addEventListener('click', () => changePage(currentPage + 1));
}

function changePage(page) {
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        fetchData();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function init() {
    document.querySelectorAll('#location-filter-group .filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#location-filter-group .filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentLocation = btn.dataset.location;
            currentPage = 1;
            fetchData();
        });
    });

    document.querySelectorAll('#bu-filter-group .filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#bu-filter-group .filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentBu = btn.dataset.bu;
            currentPage = 1;
            fetchData();
        });
    });

    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = searchInput.value.trim();
                currentPage = 1;
                fetchData();
            }, 400);
        });
    }

    fetchData();
}

document.addEventListener('DOMContentLoaded', init);
