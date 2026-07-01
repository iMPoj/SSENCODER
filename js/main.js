import { appState } from './state.js';
import { fetchData, postData } from './api.js';
import { showLoader, hideLoader, showMessage } from './ui.js';

import { initDashboard, populateDashboardFilters, renderDashboard } from './dashboard.js';
import { initStocksDashboard, renderStocksDashboard, loadAndRenderStocks } from './stocks_dashboard.js';
import { initOrderBook, populateOrderBookFilters, fetchOrderBookPage } from './order_book.js';
import { initUnservedPage, populateUnservedFilters, fetchUnservedPage } from './unserved.js';
import { initFulfillable, populateFulfillableFilters, fetchFulfillableData } from './fulfillable.js';
import { initReadyOrders } from './ready_orders.js';

const pages = [
    'reckittHome', 'dashboard', 'stocksDashboard', 'orderBook', 'unserved',
    'fulfillable', 'readyOrders', 'admin', 'fulfilled_orders', 'users'
];

const initializedTabs = new Set();

function switchTab(tabId) {
    appState.activeTab = tabId;
    window.location.hash = tabId;

    // Force the browser back to the top every time you switch tabs!
    window.scrollTo(0, 0);

    // Forcefully hide inactive tabs using a bulletproof CSS lock
    pages.forEach(page => {
        const pageElement = document.getElementById(`${page}Page`);
        if (pageElement) {
            if (page === tabId) {
                pageElement.classList.remove('hidden', 'inactive-page');
                pageElement.classList.add('active-page');
                pageElement.style.display = '';
            } else {
                pageElement.classList.add('hidden', 'inactive-page');
                pageElement.classList.remove('active-page');
                pageElement.style.display = 'none';
            }
        }
    });

    document.querySelectorAll('.nav-link, .dropdown-link, .nav-link-mobile').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-tab') === tabId) link.classList.add('active');
    });

    if (!initializedTabs.has(tabId)) {
        try {
            switch (tabId) {
                case 'dashboard': initDashboard(); break;
                case 'stocksDashboard': initStocksDashboard(); break;
                case 'orderBook': initOrderBook(); break;
                case 'unserved': initUnservedPage(); break;
                case 'fulfillable': initFulfillable(); break;
                case 'readyOrders': initReadyOrders(); break;
                case 'admin': if (typeof window.initAdmin === 'function') window.initAdmin(); break;
            }
            initializedTabs.add(tabId);
        } catch (e) {
            console.error(`Error initializing tab ${tabId}:`, e);
        }
    }

    // Load data for the tab
    try {
        switch (tabId) {
            case 'dashboard': renderDashboard(); break;
            case 'stocksDashboard': loadAndRenderStocks(); break;
            case 'orderBook': fetchOrderBookPage(1); break;
            case 'unserved': fetchUnservedPage(1); break;
            case 'fulfillable': fetchFulfillableData(); break;
            case 'fulfilled_orders':
                populateFulfilledCustomers();
                loadFulfilledOrders();
                break;
        }
    } catch (e) { console.error("Error loading tab data:", e); }
}

function setupEventListeners() {
    document.body.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-tab]');
        if (link) {
            e.preventDefault();
            const tabId = link.getAttribute('data-tab');
            if (tabId === 'encoder') { window.location.href = 'create_order.php'; return; }
            switchTab(tabId);
        }
    });

    window.addEventListener('hashchange', () => {
        const hash = window.location.hash.substring(1);
        if (hash && pages.includes(hash)) switchTab(hash);
    });
}

async function refreshData() {
    // 1. Try Cache
    const cachedProducts = sessionStorage.getItem('app_products');
    const cachedCustomers = sessionStorage.getItem('app_customers');

    if (cachedProducts && cachedCustomers) {
        try {
            appState.products = JSON.parse(cachedProducts);
            appState.customers = JSON.parse(cachedCustomers);
            appState.isDataLoaded = true;
            finishInit();
            return;
        } catch (e) { console.warn("Cache parse error"); }
    }

    // 2. Fetch Fresh
    try {
        const [productsResponse, customersData] = await Promise.all([
            fetchData('get_products'),
            fetchData('get_customers')
        ]);

        appState.products = {};
        if (productsResponse && Array.isArray(productsResponse.data)) {
            productsResponse.data.forEach(p => {
                if (Array.isArray(p.codes)) {
                    p.codes.forEach(s => {
                        appState.products[s.code] = {
                            productId: p.id, description: p.description, bu: p.bu,
                            inventory: s.inventory || [], sales_price: s.sales_price,
                            pieces_per_case: s.pieces_per_case, type: s.type
                        };
                    });
                }
            });
        }

        appState.customers = Array.isArray(customersData.data) ? customersData.data : [];

        // Update Cache
        try {
            sessionStorage.setItem('app_products', JSON.stringify(appState.products));
            sessionStorage.setItem('app_customers', JSON.stringify(appState.customers));
        } catch (e) { }

        finishInit();
    } catch (error) {
        console.error("Critical Data Load Error:", error);
        // Do NOT show message here, just let it fail silently so UI opens
    }
}

function finishInit() {
    try {
        populateDashboardFilters();
        populateOrderBookFilters();
        populateUnservedFilters();
        populateFulfillableFilters();
    } catch (e) { console.warn("Filter population error:", e); }
}

async function initApp() {
    try {
        setupEventListeners();
        await refreshData();

        const initialTab = window.location.hash.substring(1) || 'reckittHome';
        if (pages.includes(initialTab)) {
            switchTab(initialTab);
        } else {
            switchTab('reckittHome');
        }
    } catch (e) {
        console.error("App Init Failed:", e);
    } finally {
        // Always hide loader regardless of errors
        hideLoader();
    }
}


function populateFulfilledCustomers() {
    const custSelect = document.getElementById('fulfilledCustomerFilter');
    if (custSelect && custSelect.options.length <= 1 && appState.customers) {
        appState.customers.forEach(c => {
            custSelect.add(new Option(c.name, c.name));
        });
    }
}
// Add this to your JS file, ideally where you handle other page initializations

async function loadFulfilledOrders() {
    const container = document.getElementById('fulfilledListContainer');
    if (!container) return; // Not on the page

    container.innerHTML = `<div class="col-span-full flex justify-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-500"></div></div>`;

    const filters = {
        month: document.getElementById('fulfilledMonthFilter')?.value || 'all',
        bu: document.getElementById('fulfilledBuFilter')?.value || 'all',
        customer: document.getElementById('fulfilledCustomerFilter')?.value || 'all',
        location: document.getElementById('fulfilledLocationFilter')?.value || 'all'
    };

    try {
        const result = await postData('get_fulfilled_orders', filters);

        if (result.success) {
            // ★ NEW: Render the totals strictly for Fulfilled items
            const t = result.totals || {};
            const peso = (n) => '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const num = (n) => Number(n || 0).toLocaleString('en-PH');
            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

            setTxt('fulTotalPos', num(t.total_pos));
            setTxt('fulTotalQty', num(t.total_qty));
            setTxt('fulTotalAmount', peso(t.total_amount));

            setTxt('fulfilledHeroTotal', peso(t.total_amount));
            setTxt('fulfilledHeroPOCount', num(t.total_pos));
            setTxt('fulfilledHeroQty', num(t.total_qty));

            if (result.data.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12 bg-white rounded-xl border border-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <h3 class="text-lg font-bold text-slate-600">No fulfilled items found</h3>
                        <p class="text-slate-400 text-sm">Try adjusting your filters.</p>
                    </div>`;
                return;
            }

            container.innerHTML = result.data.map(order => {
                const orderDate = new Date(order.order_date.replace(' ', 'T'));
                const dateStr = orderDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                const timeStr = orderDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

                // Safely parse SO Number & strictly extract ONLY the Fulfilled SOs
                let parsedSO = 'None';
                if (order.so_number) {
                    try {
                        let soArray = JSON.parse(order.so_number);
                        if (!Array.isArray(soArray)) soArray = [order.so_number];

                        // Fulfilled items are appended after the standard pages.
                        // We calculate the number of standard pages based on the original total item count.
                        const totalItemsCount = parseInt(order.total_items_count || 0);
                        const standardPageCount = Math.ceil(totalItemsCount / 12);

                        // Extract ONLY the SO numbers that come after the standard pages
                        let fulfilledSOs = soArray.slice(standardPageCount);

                        let cleanSOs = fulfilledSOs.filter(s => s && String(s).trim() !== '');
                        if (cleanSOs.length > 0) parsedSO = cleanSOs.join(', ');
                    } catch (e) {
                        if (String(order.so_number).trim() !== '') parsedSO = order.so_number;
                    }
                }

                return `
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md hover:border-amber-300 transition-all flex flex-col md:flex-row relative group">
                    <div class="absolute left-0 top-0 md:bottom-0 w-full md:w-1.5 h-1 md:h-full bg-gradient-to-b from-amber-400 to-orange-400"></div>
                    
                    <div class="p-5 md:w-[35%] border-b md:border-b-0 md:border-r border-slate-100 flex flex-col justify-center bg-white z-10 md:pl-6">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-slate-100 text-slate-600 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest">${order.location}</span>
                        </div>
                        <h3 class="font-black text-slate-800 text-lg leading-tight mb-1 truncate" title="${order.customer_name}">${order.customer_name}</h3>
                        <p class="text-xs font-mono text-slate-500 mb-1">PO: <span class="text-slate-800 font-bold">${order.po_number}</span></p>
                        <p class="text-xs font-mono text-slate-500 mb-4">SO: <span class="text-slate-800 font-bold">${parsedSO}</span></p>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 text-xs text-slate-600 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="font-bold">${dateStr}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="font-medium">${timeStr}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 md:flex-1 bg-slate-50/50 flex flex-col justify-center">
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Fulfilled Items</p>
                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">${order.items.length} Total</span>
                        </div>
                        <div class="space-y-1.5 max-h-[140px] overflow-y-auto custom-scrollbar pr-2">
                            ${order.items.map(item => `
                                <div class="flex justify-between items-center text-xs bg-white p-2.5 rounded-lg shadow-sm border border-slate-100 hover:border-amber-200 transition-colors">
                                    <div class="flex-1 pr-3 truncate">
                                        <span class="font-bold text-slate-700">${item.description}</span>
                                        <span class="text-[10px] text-slate-400 font-mono ml-2 hidden sm:inline-block">${item.sku}</span>
                                    </div>
                                    <div class="font-black text-amber-700 bg-amber-100/50 px-2.5 py-1 rounded border border-amber-200/50 flex-shrink-0 tabular-nums">
                                        x${item.quantity}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div class="p-5 md:w-48 flex items-center justify-center border-t md:border-t-0 md:border-l border-slate-100 bg-white">
                        <a href="view_order.php?id=${order.order_id}&context=fulfilled" target="_blank" class="w-full text-center bg-white hover:bg-amber-50 text-amber-600 border-2 border-amber-100 hover:border-amber-400 font-black text-xs py-3 px-4 rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                            VIEW PO
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                    </div>
                </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error("Failed to load fulfilled orders:", error);
    }
}

// Hook up the event listeners
document.addEventListener('DOMContentLoaded', () => {

    // Attach filter listeners
    ['fulfilledMonthFilter', 'fulfilledBuFilter', 'fulfilledCustomerFilter', 'fulfilledLocationFilter'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', loadFulfilledOrders);
    });

    document.getElementById('refreshFulfilledBtn')?.addEventListener('click', loadFulfilledOrders);
});

document.addEventListener('DOMContentLoaded', initApp);