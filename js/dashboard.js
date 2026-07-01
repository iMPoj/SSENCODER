import { appState } from './state.js';
import { postData } from './api.js';

let dashboardData = null;
let vatMultiplier = 1;
let isGross = true;

// Filters
let globalLocFilter = 'all';
let globalBuFilter = 'all';
let activeTableBuFilter = 'all';

// Charts Instances
let donutCharts = { Nutri: null, Health: null, Hygiene: null };
let salesmanChart = null;
let customerChart = null;

const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
const formatNum = (val) => (parseInt(val) || 0).toLocaleString('en-US');

// ★ NEW: Time Ago formatter (e.g. "5 mins ago")
function timeAgo(dateString) {
    if (!dateString) return '';
    // Fix for cross-browser SQL date parsing
    const cleanDateString = dateString.replace(' ', 'T');
    const date = new Date(cleanDateString);
    const now = new Date();
    const seconds = Math.round((now - date) / 1000);
    const minutes = Math.round(seconds / 60);
    const hours = Math.round(minutes / 60);
    const days = Math.round(hours / 24);

    if (seconds < 60) return 'Just now';
    if (minutes < 60) return `${minutes} min ago`;
    if (hours < 24) return `${hours} hr${hours > 1 ? 's' : ''} ago`;
    if (days === 1) return 'Yesterday';
    return `${days} days ago`;
}

// Chart.js Plugin for Donut Center Text
const centerTextPlugin = {
    id: 'centerText',
    beforeDraw: function (chart) {
        if (chart.config.options.elements?.center) {
            let ctx = chart.ctx;
            let centerConfig = chart.config.options.elements.center;
            let text = centerConfig.text;
            let color = centerConfig.color || '#000';
            ctx.restore();
            let fontSize = (chart.height / 114).toFixed(2);
            ctx.font = "black " + fontSize + "em sans-serif";
            ctx.textBaseline = "middle";
            ctx.fillStyle = color;
            let textX = Math.round((chart.width - ctx.measureText(text).width) / 2);
            let textY = chart.height / 2;
            ctx.fillText(text, textX, textY);
            ctx.save();
        }
    }
};
if (typeof Chart !== 'undefined') Chart.register(centerTextPlugin);

function updateGlobalBtnUI(selector, activeVal) {
    document.querySelectorAll(selector).forEach(btn => {
        if (btn.dataset.val === activeVal) {
            btn.classList.add('bg-white', 'shadow-sm', 'text-[#E42278]');
            btn.classList.remove('text-gray-500');
        } else {
            btn.classList.remove('bg-white', 'shadow-sm', 'text-[#E42278]');
            btn.classList.add('text-gray-500');
        }
    });
}

export function initDashboard() {
    // Global Filter Buttons
    document.querySelectorAll('.global-loc-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            globalLocFilter = e.target.dataset.val;
            updateGlobalBtnUI('.global-loc-btn', globalLocFilter);
            fetchDashboardData();
        });
    });

    document.querySelectorAll('.global-bu-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            globalBuFilter = e.target.dataset.val;
            updateGlobalBtnUI('.global-bu-btn', globalBuFilter);
            fetchDashboardData();
        });
    });

    // Toggle Listeners
    document.getElementById('btn-vat-in')?.addEventListener('click', () => { vatMultiplier = 1; updateTogglesUI(); renderUI(); });
    document.getElementById('btn-vat-ex')?.addEventListener('click', () => { vatMultiplier = 1 / 1.12; updateTogglesUI(); renderUI(); });
    document.getElementById('btn-disc-without')?.addEventListener('click', () => { isGross = true; updateTogglesUI(); renderUI(); });
    document.getElementById('btn-disc-with')?.addEventListener('click', () => { isGross = false; updateTogglesUI(); renderUI(); });

    // Table BU Filter
    document.querySelectorAll('.filter-btn-tbl').forEach(btn => {
        btn.addEventListener('click', (e) => {
            activeTableBuFilter = e.target.dataset.bu;
            document.querySelectorAll('.filter-btn-tbl').forEach(b => {
                if (b.dataset.bu === activeTableBuFilter) {
                    b.classList.add('bg-white', 'shadow-sm', 'text-indigo-600');
                    b.classList.remove('text-gray-500');
                } else {
                    b.classList.remove('bg-white', 'shadow-sm', 'text-indigo-600');
                    b.classList.add('text-gray-500');
                }
            });
            renderFilteredLists();
        });
    });

    setupTabs();

    // Auto-refresh every 30 seconds, BUT ONLY if the tab is currently visible.
    // This saves massive amounts of CPU/RAM on lower-end devices.
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            fetchDashboardData();
        }
    }, 30000);
}

function setupTabs() {
    const tabBtns = document.querySelectorAll('.data-tab-btn');
    const tabContents = document.querySelectorAll('.data-tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active state from all buttons
            tabBtns.forEach(b => {
                b.classList.remove('text-[#E42278]', 'border-[#E42278]');
                b.classList.add('text-gray-500', 'border-transparent');
            });
            // Add active to clicked
            btn.classList.add('text-[#E42278]', 'border-[#E42278]');
            btn.classList.remove('text-gray-500', 'border-transparent');

            // Hide all tables, show target
            const targetId = btn.getAttribute('data-target');
            tabContents.forEach(content => {
                if (content.id === targetId) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });
}

export function populateDashboardFilters() {
    const list = document.getElementById('customerFilterList');
    if (list) {
        let html = `
            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors border border-transparent hover:border-gray-100">
                <input type="checkbox" value="all" class="customer-checkbox w-4 h-4 rounded text-[#E42278] focus:ring-[#E42278]" checked>
                <span class="font-bold text-[#0D111A]">All Customers</span>
            </label>
            <div class="my-1 border-t border-gray-100"></div>
        `;
        html += appState.customers.map(c => `
            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer customer-item-label transition-colors">
                <input type="checkbox" value="${c.name}" class="customer-checkbox w-4 h-4 rounded text-[#E42278] focus:ring-[#E42278]">
                <span class="text-gray-700 truncate" title="${c.name}">${c.name}</span>
            </label>
        `).join('');
        list.innerHTML = html;

        setupCustomerDropdownLogic();
    }
}

function setupCustomerDropdownLogic() {
    const btn = document.getElementById('customerFilterBtn');
    const dropdown = document.getElementById('customerFilterDropdown');
    const search = document.getElementById('customerFilterSearch');
    const checkboxes = document.querySelectorAll('.customer-checkbox');
    const label = document.getElementById('customerFilterLabel');

    if (!btn) return;

    btn.onclick = (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            search.focus();
            search.value = '';
            document.querySelectorAll('.customer-item-label').forEach(lbl => lbl.style.display = 'flex');
        }
    };

    document.addEventListener('click', (e) => {
        if (!document.getElementById('customerFilterContainer').contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    search.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.customer-item-label').forEach(lbl => {
            const text = lbl.textContent.toLowerCase();
            lbl.style.display = text.includes(term) ? 'flex' : 'none';
        });
    });

    const allCheckbox = document.querySelector('.customer-checkbox[value="all"]');
    const otherCheckboxes = Array.from(checkboxes).filter(cb => cb.value !== 'all');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', (e) => {
            if (e.target.value === 'all' && e.target.checked) {
                otherCheckboxes.forEach(c => c.checked = false);
            } else if (e.target.checked) {
                allCheckbox.checked = false;
            }

            const selected = otherCheckboxes.filter(c => c.checked);
            if (selected.length === 0) {
                allCheckbox.checked = true;
                label.textContent = 'All Customers';
                label.classList.remove('font-bold', 'text-[#E42278]');
            } else if (selected.length === 1) {
                label.textContent = selected[0].value;
                label.classList.add('font-bold', 'text-[#E42278]');
            } else {
                label.textContent = `${selected.length} Selected`;
                label.classList.add('font-bold', 'text-[#E42278]');
            }
            fetchDashboardData();
        });
    });
}

function updateTogglesUI() {
    const activeClass = ['bg-white', 'shadow-sm', 'text-[#E42278]'];
    const inactiveClass = ['text-white/70', 'hover:text-white']; // For Header

    const setBtn = (id, isActive) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (isActive) { el.classList.add(...activeClass); el.classList.remove(...inactiveClass); }
        else { el.classList.remove(...activeClass); el.classList.add(...inactiveClass); }
    };

    setBtn('btn-vat-in', vatMultiplier === 1);
    setBtn('btn-vat-ex', vatMultiplier !== 1);
    setBtn('btn-disc-without', isGross === true);
    setBtn('btn-disc-with', isGross === false);
}

export async function fetchDashboardData() {
    let customerVal = 'all';
    const allCheckbox = document.querySelector('.customer-checkbox[value="all"]');
    if (allCheckbox && !allCheckbox.checked) {
        const selected = Array.from(document.querySelectorAll('.customer-checkbox:checked')).map(cb => cb.value);
        if (selected.length > 0) customerVal = JSON.stringify(selected);
    }

    const filterData = { location: globalLocFilter, bu: globalBuFilter, customer: customerVal, action: 'get_dashboard_data' };

    try {
        const res = await postData('get_dashboard_data', filterData);
        if (res.success && res.data) {
            dashboardData = res.data;
            renderUI();
        }
    } catch (e) { console.error("Dashboard fetch error:", e); }
}

function getVal(item, prefix) {
    if (!item) return 0;
    const rawValue = isGross ? parseFloat(item[`${prefix}_gross`] || 0) : parseFloat(item[`${prefix}_net`] || 0);
    return rawValue * vatMultiplier;
}

function renderUI() {
    if (!dashboardData) return;
    const d = dashboardData;
    let totalServed = 0, totalUnserved = 0, totalPrev = 0, totalQty = 0, totalCancelled = 0;

    d.current_bu_stats.forEach(item => {
        totalServed += getVal(item, 'served');
        totalUnserved += getVal(item, 'unserved');
        totalQty += parseInt(item.served_qty || 0);
    });

    d.prev_bu_stats.forEach(item => { totalPrev += getVal(item, 'prev'); });
    if (d.cancelled_orders) d.cancelled_orders.forEach(item => { totalCancelled += getVal(item, 'total'); });

    document.getElementById('kpi-served').textContent = formatCurrency(totalServed);
    document.getElementById('kpi-unserved').textContent = formatCurrency(totalUnserved);
    document.getElementById('kpi-cancelled').textContent = formatCurrency(totalCancelled);
    document.getElementById('kpi-prev').textContent = formatCurrency(totalPrev);
    document.getElementById('kpi-qty').textContent = formatNum(totalQty);

    renderBUBreakdownTable(d.current_bu_stats, d.prev_bu_stats);
    renderDonutCharts(d.current_bu_stats);

    // NEW BAR CHARTS
    renderBarCharts(d.salesmen_raw, d.customers_raw);

    // ★ NEW: RECENT SALES
    renderRecentSales(d.recent_sales);

    renderFilteredLists();
}

function renderDonutCharts(currentStats) {
    const config = {
        Nutri: { id: 'donutNutri', color: '#F59E0B', empty: '#FEF3C7' },
        Health: { id: 'donutHealth', color: '#6366F1', empty: '#E0E7FF' },
        Hygiene: { id: 'donutHygiene', color: '#10B981', empty: '#D1FAE5' }
    };

    ['Nutri', 'Health', 'Hygiene'].forEach(bu => {
        const item = currentStats.find(i => i.bu === bu);
        const served = getVal(item, 'served');
        const unserved = getVal(item, 'unserved');
        const total = served + unserved;
        const pct = total > 0 ? Math.round((served / total) * 100) : 0;

        const ctx = document.getElementById(config[bu].id);
        if (!ctx) return;

        if (donutCharts[bu]) donutCharts[bu].destroy();

        donutCharts[bu] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Served', 'Unserved'],
                datasets: [{ data: [served, unserved], backgroundColor: [config[bu].color, config[bu].empty], borderWidth: 0, borderRadius: 10, spacing: 2, cutout: '75%' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true } }, elements: { center: { text: pct + '%', color: config[bu].color } } }
        });
    });
}

function renderBarCharts(salesmenRaw, customersRaw) {
    // Process Salesman Data
    let sMap = {};
    salesmenRaw.forEach(r => {
        let name = r.salesman_name || 'Unknown';
        sMap[name] = (sMap[name] || 0) + getVal(r, 'total');
    });
    let topSalesmen = Object.entries(sMap).sort((a, b) => b[1] - a[1]).slice(0, 5);

    // Process Customer Data
    let cMap = {};
    customersRaw.forEach(r => {
        let name = r.customer_name || 'Unknown';
        cMap[name] = (cMap[name] || 0) + getVal(r, 'total');
    });
    let topCustomers = Object.entries(cMap).sort((a, b) => b[1] - a[1]).slice(0, 5);

    const sCtx = document.getElementById('salesmanBarChart');
    const cCtx = document.getElementById('customerBarChart');

    if (sCtx) {
        if (salesmanChart) salesmanChart.destroy();
        salesmanChart = new Chart(sCtx, {
            type: 'bar',
            data: {
                labels: topSalesmen.map(s => s[0]),
                datasets: [{
                    label: 'Total Sales',
                    data: topSalesmen.map(s => s[1]),
                    backgroundColor: '#E42278',
                    borderRadius: 4,
                    maxBarThickness: 32
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => formatCurrency(ctx.raw) } } },
                scales: {
                    x: { ticks: { callback: (val) => '₱' + (val / 1000) + 'k', font: { size: 10 } }, grid: { borderDash: [2, 2] } },
                    y: { ticks: { font: { size: 11, weight: 'bold' } }, grid: { display: false } }
                }
            }
        });
    }

    if (cCtx) {
        if (customerChart) customerChart.destroy();
        customerChart = new Chart(cCtx, {
            type: 'bar',
            data: {
                labels: topCustomers.map(c => c[0].length > 15 ? c[0].substring(0, 15) + '...' : c[0]), // Truncate long names
                datasets: [{
                    label: 'Total Sales',
                    data: topCustomers.map(c => c[1]),
                    backgroundColor: '#3B82F6', // Blue for customers
                    borderRadius: 4,
                    maxBarThickness: 32
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => formatCurrency(ctx.raw) } } },
                scales: {
                    x: { ticks: { callback: (val) => '₱' + (val / 1000) + 'k', font: { size: 10 } }, grid: { borderDash: [2, 2] } },
                    y: { ticks: { font: { size: 11, weight: 'bold' } }, grid: { display: false } }
                }
            }
        });
    }
}

function renderBUBreakdownTable(currentStats, prevStats) {
    const container = document.getElementById('buBreakdownTable');
    if (!container) return;

    const getBuVal = (statsList, buName, prefix) => {
        const item = statsList.find(i => i.bu === buName);
        return getVal(item, prefix);
    };

    const cancelledStats = [];
    if (dashboardData && dashboardData.cancelled_orders) {
        ['Nutri', 'Health', 'Hygiene'].forEach(b => {
            const items = dashboardData.cancelled_orders.filter(c => c.bu === b);
            let gross = 0, net = 0;
            items.forEach(i => { gross += parseFloat(i.total_gross || 0); net += parseFloat(i.total_net || 0); });
            cancelledStats.push({ bu: b, cancelled_gross: gross, cancelled_net: net });
        });
    }

    const rows = [
        { label: 'Total Served', prefix: 'served', list: currentStats },
        { label: 'Total Unserved', prefix: 'unserved', list: currentStats },
        { label: 'Total Cancelled', prefix: 'cancelled', list: cancelledStats },
        { label: 'Prev Month Fulfilled', prefix: 'prev', list: prevStats }
    ];

    container.innerHTML = rows.map(r => {
        const nutri = getBuVal(r.list, 'Nutri', r.prefix);
        const health = getBuVal(r.list, 'Health', r.prefix);
        const hygiene = getBuVal(r.list, 'Hygiene', r.prefix);
        const total = nutri + health + hygiene;

        const pNutri = total > 0 ? (nutri / total) * 100 : 0;
        const pHealth = total > 0 ? (health / total) * 100 : 0;
        const pHygiene = total > 0 ? (hygiene / total) * 100 : 0;

        return `
        <div class="mb-3 bg-gray-50/50 p-4 rounded-xl border border-gray-100 shadow-sm transition-transform hover:scale-[1.02]">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-black text-gray-700 uppercase tracking-widest">${r.label}</span>
                <span class="text-sm font-black text-[#0D111A]">${formatCurrency(total)}</span>
            </div>
            <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden flex mb-3">
                <div style="width: ${pNutri}%" class="bg-amber-500 h-full transition-all duration-500"></div>
                <div style="width: ${pHealth}%" class="bg-indigo-500 h-full transition-all duration-500 border-l border-white/20"></div>
                <div style="width: ${pHygiene}%" class="bg-emerald-500 h-full transition-all duration-500 border-l border-white/20"></div>
            </div>
            <div class="flex justify-between text-[10px] font-bold">
                <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded">Nutri: ${formatCurrency(nutri)}</span>
                <span class="text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">Health: ${formatCurrency(health)}</span>
                <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Hygiene: ${formatCurrency(hygiene)}</span>
            </div>
        </div>
        `;
    }).join('');
}


function renderFilteredLists() {
    if (!dashboardData) return;
    const buColors = { Health: 'indigo', Hygiene: 'emerald', Nutri: 'amber', default: 'gray' };
    const getPill = (bu) => {
        const color = buColors[bu] || buColors.default;
        return `<span class="px-2 py-0.5 bg-${color}-50 rounded text-[9px] font-bold text-${color}-600 uppercase border border-${color}-100">${bu || 'N/A'}</span>`;
    };

    // 1. Top Products
    let prods = dashboardData.top_served_products || [];
    if (activeTableBuFilter !== 'all') prods = prods.filter(p => p.bu === activeTableBuFilter);
    prods = prods.sort((a, b) => getVal(b, 'total') - getVal(a, 'total')).slice(0, 10);
    const prodBody = document.getElementById('topProductsTable');
    if (prodBody) {
        prodBody.innerHTML = prods.length ? prods.map(item => `
            <tr class="hover:bg-gray-50/80">
                <td class="py-2 px-4">
                    <div class="font-bold text-gray-800 text-xs">${item.description}</div>
                    <div class="font-mono text-[10px] text-gray-400 mt-0.5">${item.sku}</div>
                </td>
                <td class="py-2 px-4">${getPill(item.bu)}</td>
                <td class="py-2 px-4 text-center font-bold text-gray-700">${formatNum(item.qty)}</td>
                <td class="py-2 px-4 text-right font-black text-[#E42278]">${formatCurrency(getVal(item, 'total'))}</td>
            </tr>
        `).join('') : `<tr><td colspan="4" class="py-6 text-center text-gray-400 text-xs">No data found.</td></tr>`;
    }

    // 2. Unserved Items
    let unserved = dashboardData.unserved_items || [];
    if (activeTableBuFilter !== 'all') unserved = unserved.filter(u => u.bu === activeTableBuFilter);
    unserved = unserved.sort((a, b) => getVal(b, 'total') - getVal(a, 'total'));
    const unBody = document.getElementById('unservedItemsTable');
    if (unBody) {
        unBody.innerHTML = unserved.length ? unserved.map(item => `
            <tr class="hover:bg-gray-50/80">
                <td class="py-2 px-4">
                    <div class="font-bold text-gray-800 text-xs">${item.description}</div>
                    <div class="font-mono text-[10px] text-gray-400 mt-0.5">${item.sku}</div>
                </td>
                <td class="py-2 px-4">${getPill(item.bu)}</td>
                <td class="py-2 px-4 text-center font-bold text-rose-500">${formatNum(item.qty)}</td>
                <td class="py-2 px-4 text-right font-black text-rose-600">${formatCurrency(getVal(item, 'total'))}</td>
            </tr>
        `).join('') : `<tr><td colspan="4" class="py-6 text-center text-gray-400 text-xs">No data found.</td></tr>`;
    }

    // 3. Cancelled Orders
    let cancelled = dashboardData.cancelled_orders || [];
    if (activeTableBuFilter !== 'all') cancelled = cancelled.filter(c => c.bu === activeTableBuFilter);
    cancelled = cancelled.sort((a, b) => getVal(b, 'total') - getVal(a, 'total'));
    const cxBody = document.getElementById('cancelledOrdersTable');
    if (cxBody) {
        cxBody.innerHTML = cancelled.length ? cancelled.map(item => `
            <tr class="hover:bg-gray-50/80">
                <td class="py-2 px-4">
                    <div class="font-bold text-gray-800 text-xs">${item.po_number || 'N/A'}</div>
                    <div class="text-[10px] text-gray-500 mt-0.5 truncate max-w-[200px]">${item.customer_name || 'Unknown'}</div>
                </td>
                <td class="py-2 px-4">${getPill(item.bu)}</td>
                <td class="py-2 px-4 font-bold text-amber-600 text-[10px]">${item.cancel_reason || 'N/A'}</td>
                <td class="py-2 px-4 text-right font-black text-amber-600">${formatCurrency(getVal(item, 'total'))}</td>
            </tr>
        `).join('') : `<tr><td colspan="4" class="py-6 text-center text-gray-400 text-xs">No data found.</td></tr>`;
    }

    // 4. Prev Fulfilled
    let prev = dashboardData.prev_fulfilled_items || [];
    if (activeTableBuFilter !== 'all') prev = prev.filter(p => p.bu === activeTableBuFilter);
    prev = prev.sort((a, b) => getVal(b, 'total') - getVal(a, 'total'));
    const prevBody = document.getElementById('prevFulfilledTable');
    if (prevBody) {
        prevBody.innerHTML = prev.length ? prev.map(item => `
            <tr class="hover:bg-gray-50/80">
                <td class="py-2 px-4">
                    <div class="font-bold text-gray-800 text-xs">${item.description}</div>
                    <div class="font-mono text-[10px] text-gray-400 mt-0.5">${item.sku}</div>
                </td>
                <td class="py-2 px-4 text-[10px] text-gray-600 truncate max-w-[150px]" title="${item.customer_name}">${item.customer_name}</td>
                <td class="py-2 px-4 text-center font-bold text-gray-700">${formatNum(item.qty)}</td>
                <td class="py-2 px-4 text-right font-black text-[#E42278]">${formatCurrency(getVal(item, 'total'))}</td>
            </tr>
        `).join('') : `<tr><td colspan="4" class="py-6 text-center text-gray-400 text-xs">No data found.</td></tr>`;
    }

    // 5. Store LVs Limit
    let lvs = dashboardData.store_lv_stats || [];
    // Filter by BU if a tab filter is selected
    if (activeTableBuFilter !== 'all') lvs = lvs.filter(l => l.bu === activeTableBuFilter);

    const lvBody = document.getElementById('storeLvsTable');
    if (lvBody) {
        const buColors = { Health: 'indigo', Hygiene: 'emerald', Nutri: 'amber', default: 'blue' };

        lvBody.innerHTML = lvs.length ? lvs.map(item => {
            const limit = parseFloat(item.lv_limit);
            const sales = parseFloat(item.current_sales);
            const pct = limit > 0 ? Math.min((sales / limit) * 100, 100) : 0;

            const colorPrefix = buColors[item.bu] || buColors.default;

            let barColor = `bg-${colorPrefix}-500`;
            if (pct > 75) barColor = 'bg-orange-500';
            if (pct >= 95) barColor = 'bg-red-500';

            return `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="py-3 px-4">
                    <div class="font-bold text-gray-800 text-xs truncate max-w-[250px]" title="${item.customer_name}">${item.customer_name}</div>
                    <span class="inline-block mt-1 px-2 py-0.5 bg-${colorPrefix}-50 rounded text-[9px] font-bold text-${colorPrefix}-600 uppercase border border-${colorPrefix}-100">${item.bu || 'N/A'}</span>
                </td>
                <td class="py-3 px-4 text-right font-bold text-gray-800">${formatCurrency(sales)}</td>
                <td class="py-3 px-4 text-right font-black text-${colorPrefix}-600">${formatCurrency(limit)}</td>
                <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden flex-1">
                            <div style="width: ${pct}%" class="${barColor} h-full transition-all"></div>
                        </div>
                        <span class="text-[10px] font-black w-8 text-right ${pct >= 100 ? 'text-red-600' : 'text-gray-500'}">${pct.toFixed(0)}%</span>
                    </div>
                </td>
            </tr>
            `;
        }).join('') : `<tr><td colspan="4" class="py-6 text-center text-gray-400 text-xs italic">No active LV quotas set for the selected filters.</td></tr>`;
    }
}

// ★ NEW: Recent Sales Renderer
function renderRecentSales(salesArray) {
    const container = document.getElementById('recentSalesFeed');
    if (!container) return;

    if (!salesArray || salesArray.length === 0) {
        container.innerHTML = '<div class="text-xs text-gray-400 italic text-center py-6">No recent sales match the selected filters.</div>';
        return;
    }

    container.innerHTML = salesArray.map(item => `
        <div class="bg-gray-50/80 p-3 rounded-xl border border-gray-100 flex items-center justify-between hover:bg-gray-100 transition-colors group">
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-gray-800 text-xs truncate max-w-[140px]" title="${item.customer_name}">${item.customer_name || 'Unknown'}</span>
                    <span class="text-[10px] font-black text-[#E42278]">${formatCurrency(getVal(item, 'total'))}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-mono text-[9px] font-bold text-gray-500">${item.po_number || 'N/A'}</span>
                    <span class="text-[9px] font-bold text-gray-400 group-hover:text-gray-600 transition-colors">${timeAgo(item.order_date)}</span>
                </div>
            </div>
        </div>
    `).join('');
}

export function renderDashboard() { fetchDashboardData(); }