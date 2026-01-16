// FILE: js/dashboard.js
import { appState } from './state.js';
import { postData } from './api.js';
import { showLoader, hideLoader, showMessage } from './ui.js';

let charts = {}; 
let currentChartMode = 'price'; // 'price' or 'qty'
let dashboardData = null; // Store full data for toggling

// Formatters
const formatFullCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
const formatFullQuantity = (val) => (parseInt(val) || 0).toLocaleString('en-US');

// --- SET CHART DEFAULTS ---
if (window.Chart) {
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#64748b'; 
    Chart.defaults.scale.grid.color = '#f1f5f9'; 
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(30, 41, 59, 0.9)';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
}

// --- CENTER TEXT PLUGIN ---
const centerTextPlugin = {
  id: 'centerText',
  afterDraw: (chart) => {
    if (chart.config.type !== 'doughnut' || !chart.config.options.plugins.centerText) return;
    const { text } = chart.config.options.plugins.centerText;
    const ctx = chart.ctx;
    const {top, left, width, height} = chart.chartArea;
    const fontSize = Math.min(Math.max(height / 5, 16), 40);
    ctx.save();
    ctx.font = `bold ${fontSize}px Inter, sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#1e293b'; 
    ctx.fillText(text, left + width / 2, top + height / 2);
    ctx.restore();
  }
};

async function fetchDashboardData() {
    showLoader();
    const filterData = {
        location: document.getElementById('locFilterDashboard').value,
        bu: document.getElementById('buFilterDashboard').value,
        customer: document.getElementById('customerFilter').value,
    };
    try {
        const [dashboardResult, salesSummaryResult] = await Promise.all([
             postData('get_dashboard_data', filterData),
             postData('get_sales_summary_data', filterData)
        ]);
        
        if (dashboardResult.success && dashboardResult.data) {
            dashboardData = dashboardResult.data; // Store for later
            updateDashboardUI(dashboardData);
        } else {
            showMessage(dashboardResult.message || 'Failed to load dashboard data.', true);
        }
        
        if (salesSummaryResult.success && salesSummaryResult.data) {
             renderSalesSummaryCards(salesSummaryResult.data);
        } else {
             document.getElementById('sales-summary-container').innerHTML = `<p class="lg:col-span-3 text-center text-slate-500 py-8">Could not load sales summary.</p>`;
        }

    } catch (e) {
        console.error("Dashboard fetch error:", e);
        showMessage('An error occurred while fetching dashboard data.', true);
    } finally {
        hideLoader();
    }
}

function renderSalesSummaryCards(buData = []) {
    const container = document.getElementById('sales-summary-container');
    if (!container) return;
    const bus = ['Nutri', 'Health', 'Hygiene'];
    
    if (buData.length === 0) {
        container.innerHTML = `<p class="lg:col-span-3 text-center text-slate-500 py-8">No sales data found.</p>`;
        return;
    }

    const cardsHtml = bus.map(buName => {
        const data = buData.find(b => b.bu === buName) || {
            bu: buName, 
            total_gross: 0, total_net: 0, total_pristine: 0,
            served_gross: 0, served_net: 0, served_pristine: 0,
            unserved_gross: 0, unserved_net: 0, unserved_pristine: 0
        };

        return `
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow duration-200">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="w-2 h-6 bg-${getBuColorName(buName)} rounded-full"></span>
                    ${buName} Sales Summary
                </h3>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        <tr class="bg-slate-50"><td colspan="2" class="py-1 px-2 text-xs font-bold text-slate-500 uppercase tracking-wider pt-3">Total (All Orders)</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">Sales Price (Orig)</td><td class="py-1 text-right font-bold text-slate-900">${formatFullCurrency(data.total_gross)}</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">PO Amount (w/ Disc)</td><td class="py-1 text-right font-medium text-slate-800">${formatFullCurrency(data.total_net)}</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">Pristine (Orig/1.12)</td><td class="py-1 text-right font-medium text-slate-500">${formatFullCurrency(data.total_pristine)}</td></tr>

                        <tr class="bg-emerald-50"><td colspan="2" class="py-1 px-2 text-xs font-bold text-emerald-700 uppercase tracking-wider pt-3">Served (Fulfilled)</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">Sales Price (Orig)</td><td class="py-1 text-right font-bold text-emerald-600">${formatFullCurrency(data.served_gross)}</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">PO Amount (w/ Disc)</td><td class="py-1 text-right font-medium text-slate-800">${formatFullCurrency(data.served_net)}</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">Pristine (Orig/1.12)</td><td class="py-1 text-right font-medium text-slate-500">${formatFullCurrency(data.served_pristine)}</td></tr>

                        <tr class="bg-red-50"><td colspan="2" class="py-1 px-2 text-xs font-bold text-red-700 uppercase tracking-wider pt-3">Unserved (Missed)</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">Sales Price (Orig)</td><td class="py-1 text-right font-bold text-red-600">${formatFullCurrency(data.unserved_gross)}</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">PO Amount (w/ Disc)</td><td class="py-1 text-right font-medium text-slate-800">${formatFullCurrency(data.unserved_net)}</td></tr>
                        <tr><td class="py-1 pl-2 text-slate-600">Pristine (Orig/1.12)</td><td class="py-1 text-right font-medium text-slate-500">${formatFullCurrency(data.unserved_pristine)}</td></tr>
                    </tbody>
                </table>
            </div>
        `;
    }).join('');

    container.innerHTML = cardsHtml;
}

function getBuColorName(bu) {
    if (bu === 'Health') return 'indigo-600';
    if (bu === 'Hygiene') return 'emerald-500';
    if (bu === 'Nutri') return 'amber-500';
    return 'slate-500';
}

function updateDashboardUI(data) {
    const stats = data.stats || {};

    // KPIs
    const servedVal = parseFloat(stats.totalServedValue) || 0;
    const unservedVal = parseFloat(stats.totalUnservedValue) || 0;
    const totalPo = servedVal + unservedVal;
    document.getElementById('stat-total-po-amount').textContent = formatFullCurrency(totalPo);
    document.getElementById('stat-total-served').textContent = formatFullCurrency(stats.totalServedValue);
    document.getElementById('stat-total-qty').textContent = formatFullQuantity(stats.totalServedQty);
    document.getElementById('stat-qty-fill-rate-by-po').textContent = `${parseFloat(stats.quantityFillRateByPo || 0).toFixed(1)}%`;
    document.getElementById('stat-unserved-skus').textContent = (stats.unservedSkuCount || 0).toLocaleString();
    document.getElementById('stat-total-unserved-value').textContent = formatFullCurrency(stats.totalUnservedValue);

    // Doughnuts
    const servedQty = parseInt(stats.totalServedQty) || 0;
    const unservedQty = parseInt(stats.totalUnservedQty) || 0;
    const totalQty = servedQty + unservedQty;
    renderDoughnut('fulfillmentChartPrice', servedVal, unservedVal, totalPo);
    renderDoughnut('fulfillmentChartQty', servedQty, unservedQty, totalQty);
    renderMonthlySalesChart(data.monthlySalesData || []);

    // Top Products (Toggle Logic)
    renderTopProductsByMode(data);

    // Unserved Tables (Split into 3)
    renderUnservedSplitTables(data.topUnserved || []);
    
    renderTopCustomersTable(data.topCustomers || []);
    renderCustomerDashboards(data.topCustomers || []);
}

function renderTopProductsByMode(data) {
    if (currentChartMode === 'price') {
        renderMergedTopProductsChart(
            data.topProductsHealth || [],
            data.topProductsHygiene || [],
            data.topProductsNutri || [],
            'price'
        );
        updateToggleButtons('price');
    } else {
        renderMergedTopProductsChart(
            data.topProductsHealthQty || [],
            data.topProductsHygieneQty || [],
            data.topProductsNutriQty || [],
            'qty'
        );
        updateToggleButtons('qty');
    }
}

function updateToggleButtons(mode) {
    const btnPrice = document.getElementById('btn-mode-price');
    const btnQty = document.getElementById('btn-mode-qty');
    
    if (mode === 'price') {
        btnPrice.classList.add('bg-white', 'shadow-sm', 'text-indigo-600');
        btnPrice.classList.remove('text-slate-500');
        btnQty.classList.remove('bg-white', 'shadow-sm', 'text-indigo-600');
        btnQty.classList.add('text-slate-500');
    } else {
        btnQty.classList.add('bg-white', 'shadow-sm', 'text-indigo-600');
        btnQty.classList.remove('text-slate-500');
        btnPrice.classList.remove('bg-white', 'shadow-sm', 'text-indigo-600');
        btnPrice.classList.add('text-slate-500');
    }
}

function renderUnservedSplitTables(items) {
    const renderTable = (id, bu) => {
        const tbody = document.getElementById(id);
        if (!tbody) return;
        
        // 1. Filter by Business Unit
        const filtered = items.filter(item => item.bu === bu); 
        
        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="2" class="text-center py-4 text-slate-400 italic text-[10px]">No unserved items.</td></tr>`;
            return;
        }

        // 2. Group by Description
        const groups = {};
        filtered.forEach(item => {
            // Normalize description to ensure matching
            const desc = item.description || 'Unknown Product';
            if (!groups[desc]) {
                groups[desc] = { 
                    description: desc, 
                    total_group_value: 0, 
                    items: [] 
                };
            }
            groups[desc].total_group_value += parseFloat(item.total_value);
            groups[desc].items.push(item);
        });

        // 3. Sort Groups by Total Value (Highest first)
        const sortedGroups = Object.values(groups).sort((a, b) => b.total_group_value - a.total_group_value);

        // 4. Generate HTML
        let html = '';
        sortedGroups.forEach(group => {
            // Group Header (Product Description)
            html += `
                <tr class="bg-slate-50 border-b border-slate-100">
                    <td class="py-2 px-2 font-bold text-slate-800 text-xs" colspan="2">
                        ${group.description}
                    </td>
                </tr>
            `;
            
            // Individual SKUs under this product
            // Sort SKUs by value within the group
            group.items.sort((a, b) => parseFloat(b.total_value) - parseFloat(a.total_value));
            
            group.items.forEach(item => {
                html += `
                    <tr class="hover:bg-slate-50 transition-colors border-b border-slate-50">
                        <td class="py-2 px-2 pl-4 align-top">
                            <span class="font-mono text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                ${item.sku}
                            </span>
                        </td>
                        <td class="py-2 px-2 text-right align-top">
                            <div class="font-bold text-slate-700 text-xs">${formatFullCurrency(item.total_value)}</div>
                            <div class="text-[10px] text-slate-400 font-medium">${parseInt(item.total_quantity).toLocaleString()} pcs</div>
                        </td>
                    </tr>
                `;
            });
        });

        tbody.innerHTML = html;
    };

    renderTable('unservedListHealth', 'Health');
    renderTable('unservedListHygiene', 'Hygiene');
    renderTable('unservedListNutri', 'Nutri');
}

function renderDoughnut(canvasId, served, unserved, total) {
    const percentage = total > 0 ? Math.round((served / total) * 100) : 0;
    const config = {
        type: 'doughnut',
        data: {
            labels: ['Served', 'Unserved'],
            datasets: [{
                data: [served, unserved],
                backgroundColor: ['#10b981', '#f1f5f9'], 
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '85%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                centerText: { text: `${percentage}%` },
                tooltip: { callbacks: { label: (ctx) => ctx.raw > 1000 ? formatFullCurrency(ctx.raw) : ctx.raw.toLocaleString() } }
            }
        }
    };
    renderChart(canvasId, config);
}

function renderMonthlySalesChart(apiData) {
    const buColors = { 'Health': '#4f46e5', 'Hygiene': '#10b981', 'Nutri': '#f59e0b' };
    const bus = ['Health', 'Hygiene', 'Nutri'];
    
    const labels = [...new Set(apiData.map(d => d.month))].sort();
    const datasets = bus.map(bu => ({
        label: bu,
        data: labels.map(month => {
            const entry = apiData.find(d => d.month === month && d.bu === bu);
            return entry ? parseFloat(entry.total_sales) : 0;
        }),
        backgroundColor: buColors[bu],
        borderRadius: 4,
        barThickness: 'flex',
        maxBarThickness: 40
    }));

    const config = {
        type: 'bar',
        data: { labels, datasets },
        options: {
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } },
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, border: { display: false } }
            }
        }
    };
    renderChart('monthlySalesChart', config);
}

function renderMergedTopProductsChart(healthData, hygieneData, nutriData, mode = 'price') {
    const canvasId = 'topProductsChart-Merged';
    const legendContainer = document.getElementById('topProductsLegend-Merged');
    
    if (charts[canvasId]) charts[canvasId].destroy();
    
    if (healthData.length === 0 && hygieneData.length === 0 && nutriData.length === 0) {
        if (legendContainer) legendContainer.innerHTML = '<p class="col-span-3 text-center text-slate-400 text-sm italic">No product data available.</p>';
        return;
    }

    const buColors = { Health: '#4f46e5', Hygiene: '#10b981', Nutri: '#f59e0b' };
    const hexToRgba = (hex, alpha) => {
        const r = parseInt(hex.slice(1, 3), 16), g = parseInt(hex.slice(3, 5), 16), b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };
    
    const buildDataArray = (data) => {
        const sales = data.map(d => parseFloat(d.total_val)); // Uses 'total_val' generic key
        while (sales.length < 5) sales.push(null);
        return sales;
    };

    const datasets = [
        { label: 'Health', data: buildDataArray(healthData), backgroundColor: hexToRgba(buColors.Health, 0.1), borderColor: buColors.Health, borderWidth: 2, pointBackgroundColor: buColors.Health },
        { label: 'Hygiene', data: buildDataArray(hygieneData), backgroundColor: hexToRgba(buColors.Hygiene, 0.1), borderColor: buColors.Hygiene, borderWidth: 2, pointBackgroundColor: buColors.Hygiene },
        { label: 'Nutri', data: buildDataArray(nutriData), backgroundColor: hexToRgba(buColors.Nutri, 0.1), borderColor: buColors.Nutri, borderWidth: 2, pointBackgroundColor: buColors.Nutri }
    ];

    const buildLegendList = (title, data, color) => {
        let listItems = '';
        for (let i = 0; i < 5; i++) {
            const item = data[i];
            if(item) {
                const val = parseFloat(item.total_val);
                const displayVal = mode === 'price' ? formatFullCurrency(val) : parseInt(val).toLocaleString();
                listItems += `
                    <div class="flex items-start text-xs text-slate-600 mb-2">
                        <span class="font-bold w-5 flex-shrink-0" style="color: ${color};">${i + 1}.</span>
                        <div class="min-w-0">
                            <div class="truncate" title="${item.description}">${item.description}</div>
                            <div class="text-[10px] text-slate-400">${displayVal}</div>
                        </div>
                    </div>`;
            }
        }
        return `<div><h4 class="font-bold text-sm mb-3 pb-1 border-b" style="border-color: ${hexToRgba(color, 0.2)}; color: ${color};">${title}</h4><div>${listItems || '<span class="text-slate-400 italic">No Data</span>'}</div></div>`;
    };

    if (legendContainer) {
        legendContainer.innerHTML = `
            ${buildLegendList('Health', healthData, buColors.Health)}
            ${buildLegendList('Hygiene', hygieneData, buColors.Hygiene)}
            ${buildLegendList('Nutri', nutriData, buColors.Nutri)}
        `;
    }

    const config = {
        type: 'radar',
        data: { labels: ['1', '2', '3', '4', '5'], datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const buIdx = ctx.datasetIndex;
                            const rankIdx = ctx.dataIndex;
                            const allData = [healthData, hygieneData, nutriData];
                            const item = allData[buIdx][rankIdx];
                            if (!item) return null;
                            const val = parseFloat(item.total_val);
                            const displayVal = mode === 'price' ? formatFullCurrency(val) : parseInt(val).toLocaleString();
                            return `${ctx.dataset.label}: ${item.description} (${displayVal})`;
                        }
                    }
                }
            },
            scales: {
                r: {
                    ticks: { display: false, maxTicksLimit: 5 },
                    grid: { color: '#f1f5f9' },
                    pointLabels: { font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }, color: '#94a3b8' }
                }
            }
        }
    };
    renderChart(canvasId, config);
}

function renderChart(canvasId, config) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (charts[canvasId]) charts[canvasId].destroy();
    if (ctx) {
        charts[canvasId] = new window.Chart(ctx, { ...config, options: { responsive: true, maintainAspectRatio: false, ...config.options } });
    }
}

function renderTopCustomersTable(topCustomers) {
    const list = document.getElementById('topCustomerList');
    if (!list) return;
    list.innerHTML = topCustomers.map(cust => `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="py-3 px-4 text-slate-700 font-medium">${cust.name}</td>
            <td class="py-3 px-4 text-slate-900 font-bold text-right">${formatFullCurrency(cust.value)}</td>
        </tr>
    `).join('');
}

function renderCustomerDashboards(topCustomers) {
    const container = document.getElementById('customerDashboards');
    if (!container) return;
    const customers = appState.customers.filter(c => topCustomers.some(tc => tc.name === c.name));
    
    container.innerHTML = customers.map(customer => `
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-xl font-bold text-slate-800 mb-6 border-b pb-2">${customer.name}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                <div class="md:col-span-1 h-48 relative">
                    <canvas id="customerChart-${customer.id}"></canvas>
                </div>
                <div class="md:col-span-2 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Location</label>
                        <select id="loc-filter-${customer.id}" data-customer-id="${customer.id}" class="customer-filter block w-full rounded-md border-slate-300 shadow-sm text-sm">
                            <option value="all">All Locations</option><option value="Davao">Davao</option><option value="Gensan">Gensan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Filter Business Unit</label>
                        <select id="bu-filter-${customer.id}" data-customer-id="${customer.id}" class="customer-filter block w-full rounded-md border-slate-300 shadow-sm text-sm">
                            <option value="all">All BUs</option><option value="Health">Health</option><option value="Hygiene">Hygiene</option><option value="Nutri">Nutri</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    customers.forEach(customer => updateCustomerChart(customer.id));
    
    document.querySelectorAll('.customer-filter').forEach(filter => {
        filter.addEventListener('change', (e) => updateCustomerChart(e.target.dataset.customerId));
    });
}

async function updateCustomerChart(customerId) {
    const location = document.getElementById(`loc-filter-${customerId}`).value;
    const bu = document.getElementById(`bu-filter-${customerId}`).value;
    const result = await postData('get_customer_dashboard_data', { customer_id: customerId, location, bu });
    if (result.success && result.data) {
        const { totalServedValue, totalUnservedValue } = result.data;
        const served = parseFloat(totalServedValue) || 0;
        const unserved = parseFloat(totalUnservedValue) || 0;
        const total = served + unserved;
        const percent = total > 0 ? Math.round((served / total) * 100) : 0;

        renderChart(`customerChart-${customerId}`, {
            type: 'doughnut',
            data: {
                labels: ['Served', 'Unserved'],
                datasets: [{ data: [served, unserved], backgroundColor: ['#10b981', '#f1f5f9'], borderWidth: 0 }]
            },
            options: { 
                cutout: '80%', 
                plugins: { 
                    legend: { position: 'bottom' }, 
                    centerText: { text: `${percent}%` },
                    tooltip: { callbacks: { label: (ctx) => formatFullCurrency(ctx.raw) } }
                } 
            }
        });
    }
}

export function populateDashboardFilters() {
    const customerFilter = document.getElementById('customerFilter');
    if (customerFilter) {
        customerFilter.innerHTML = '<option value="all">All Customers</option>' + 
            appState.customers.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
    }
}

export function initDashboard() {
    if (window.Chart) {
        window.Chart.register(centerTextPlugin);
    }
    ['locFilterDashboard', 'buFilterDashboard', 'customerFilter'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', fetchDashboardData);
    });

    document.getElementById('copyUnservedBtn')?.addEventListener('click', () => {
        // Copy all 3 tables content
        let text = 'Description\tSKU\tValue\n';
        ['unservedListHealth', 'unservedListHygiene', 'unservedListNutri'].forEach(id => {
            const table = document.getElementById(id);
            if(table) {
                table.querySelectorAll('tr').forEach(row => {
                    const desc = row.querySelector('td:first-child div:first-child')?.textContent;
                    const sku = row.querySelector('td:first-child div:last-child')?.textContent;
                    const val = row.querySelector('td:last-child div:first-child')?.textContent;
                    if(desc) text += `${desc}\t${sku}\t${val}\n`;
                });
            }
        });
        navigator.clipboard.writeText(text)
            .then(() => showMessage('Copied all unserved data!'))
            .catch(() => showMessage('Failed to copy.', true));
    });

    // --- Toggle Handlers ---
    document.getElementById('btn-mode-price')?.addEventListener('click', () => {
        currentChartMode = 'price';
        if(dashboardData) updateDashboardUI(dashboardData);
    });
    document.getElementById('btn-mode-qty')?.addEventListener('click', () => {
        currentChartMode = 'qty';
        if(dashboardData) updateDashboardUI(dashboardData);
    });
}

export function renderDashboard() {
    fetchDashboardData();
}