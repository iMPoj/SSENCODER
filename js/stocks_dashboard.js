import { appState } from './state.js';
import { fetchData } from './api.js';

export async function loadAndRenderStocks() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) loadingIndicator.classList.remove('hidden');

    try {
        const productsResponse = await fetchData('get_products');
        if (productsResponse && Array.isArray(productsResponse.data)) {
            appState.products = {};
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
            try {
                sessionStorage.setItem('app_products', JSON.stringify(appState.products));
            } catch (e) { }
        }
    } catch (error) {
        console.error("Failed to fetch fresh stocks:", error);
    } finally {
        if (loadingIndicator) loadingIndicator.classList.add('hidden');
    }

    renderStocksDashboard();
}

export function renderStocksDashboard() {
    const list = document.getElementById('stocksDashboardList');
    if (!list) return;

    // Inject skeleton loader instantly before processing
    let skeletonHTML = '';
    for (let i = 0; i < 5; i++) {
        skeletonHTML += `
            <tr class="animate-pulse border-b border-slate-100">
                <td class="p-4"><div class="space-y-2"><div class="h-4 bg-slate-200 rounded w-24"></div><div class="flex gap-1"><div class="h-3 bg-slate-200 rounded w-12"></div><div class="h-3 bg-slate-200 rounded w-12"></div></div></div></td>
                <td class="p-4"><div class="space-y-2"><div class="h-4 bg-slate-200 rounded w-3/4"></div><div class="h-3 bg-slate-200 rounded w-16 rounded-full"></div></div></td>
                <td class="p-4"><div class="space-y-2"><div class="flex justify-between"><div class="h-3 bg-slate-200 rounded w-12"></div><div class="h-4 bg-slate-200 rounded w-16 rounded-full"></div></div><div class="flex justify-between"><div class="h-3 bg-slate-200 rounded w-12"></div><div class="h-4 bg-slate-200 rounded w-16 rounded-full"></div></div></div></td>
                <td class="p-4"><div class="space-y-2 flex flex-col items-end"><div class="h-4 bg-slate-200 rounded w-20"></div><div class="h-4 bg-slate-200 rounded w-20"></div></div></td>
            </tr>`;
    }
    list.innerHTML = skeletonHTML;

    // We use setTimeout so the browser renders the skeleton BEFORE freezing the CPU with data filtering
    setTimeout(() => {

        // Helper to format currency
        const formatCurrency = (val) => (parseFloat(val) || 0).toLocaleString('en-US', { style: 'currency', currency: 'PHP' });

        const selectedLoc = document.getElementById('locFilterStocks').value;
        const selectedBu = document.getElementById('buFilter').value;
        const selectedStatus = document.getElementById('stockStatusFilter').value;
        const searchTerm = document.getElementById('stockSearchInput').value.trim().toLowerCase();

        const productsById = {};
        for (const code in appState.products) {
            const productData = appState.products[code];
            const pid = productData.productId;
            if (!productsById[pid]) {
                productsById[pid] = { id: pid, description: productData.description, bu: productData.bu, codes: [] };
            }
            productsById[pid].codes.push({ ...productData, sku: code });
        }

        let filteredProducts = Object.values(productsById).filter(productGroup => {
            const buMatch = selectedBu === 'all' || productGroup.bu === selectedBu;
            const searchMatch = !searchTerm || productGroup.description.toLowerCase().includes(searchTerm) || productGroup.codes.some(code => code.sku.toLowerCase().includes(searchTerm));
            if (!buMatch || !searchMatch) return false;

            if (selectedStatus === 'all') return true;

            const totalStockForGroup = productGroup.codes
                .filter(c => c.type === 'sku')
                .reduce((sum, sku) => {
                    const invEntry = sku.inventory.find(inv => inv.location === selectedLoc);
                    return sum + (invEntry ? parseInt(invEntry.stock) : 0);
                }, 0);

            if (selectedStatus === 'in_stock') return totalStockForGroup > 10;
            if (selectedStatus === 'low_stock') return totalStockForGroup > 0 && totalStockForGroup <= 10;
            if (selectedStatus === 'no_stock') return totalStockForGroup === 0;

            return false;
        });

        if (filteredProducts.length === 0) {
            list.innerHTML = `
                <tr>
                    <td colspan="4" class="py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center">
                                <svg class="w-7 h-7 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <p class="font-bold text-slate-500">No products match your filters</p>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        // Update header KPI counts
        let inCount = 0, lowCount = 0, outCount = 0;

        let finalHtml = '';
        filteredProducts
            .sort((a, b) => a.description.localeCompare(b.description))
            .forEach(productGroup => {
                const barcode = productGroup.codes.find(c => c.type === 'barcode');
                const skus = productGroup.codes.filter(c => c.type === 'sku').sort((a, b) => a.sku.localeCompare(b.sku));

                // Compute total stock for the group to count KPIs
                const totalGroupStock = skus.reduce((sum, sku) => {
                    const inv = sku.inventory.find(i => i.location === selectedLoc);
                    return sum + (inv ? parseInt(inv.stock) : 0);
                }, 0);
                if (totalGroupStock > 10) inCount++;
                else if (totalGroupStock > 0) lowCount++;
                else outCount++;

                const buColors = { Health: 'indigo', Hygiene: 'emerald', Nutri: 'amber' };
                const buColor = buColors[productGroup.bu] || 'slate';

                finalHtml += `
                    <tr class="group hover:bg-emerald-50/30 transition-colors duration-150">
                        <td class="px-6 py-4 align-top" data-label="Barcode / SKU">
                            <div class="font-black text-slate-800 text-sm tracking-tight">${barcode ? barcode.sku : '<span class="text-slate-300 font-bold text-xs">NO BARCODE</span>'}</div>
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                ${skus.map(sku => `<span class="inline-block font-mono text-[10px] font-bold text-slate-500 bg-slate-100 rounded-lg px-2 py-0.5 group-hover:bg-emerald-100 group-hover:text-emerald-700 transition-colors">${sku.sku}</span>`).join('')}
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top" data-label="Description">
                            <div class="font-bold text-slate-700 leading-snug">${productGroup.description}</div>
                            <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-${buColor}-100 text-${buColor}-700">${productGroup.bu}</span>
                        </td>
                        <td class="px-6 py-4 align-top" data-label="Stock on Hand">
                            <div class="space-y-1.5">
                            ${skus.map(sku => {
                    const invEntry = sku.inventory.find(inv => inv.location === selectedLoc);
                    const stock = invEntry ? parseInt(invEntry.stock) : 0;
                    let badge, dot;
                    if (stock === 0) {
                        badge = 'bg-slate-100 text-slate-500';
                        dot = 'bg-slate-400';
                    } else if (stock <= 10) {
                        badge = 'bg-amber-100 text-amber-700';
                        dot = 'bg-amber-500';
                    } else {
                        badge = 'bg-emerald-100 text-emerald-700';
                        dot = 'bg-emerald-500';
                    }
                    return `
                                    <div class="flex items-center justify-between gap-3 py-1">
                                        <span class="font-mono text-[10px] text-slate-400 w-20 flex-shrink-0">${sku.sku}</span>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black ${badge}">
                                            <span class="w-1.5 h-1.5 rounded-full ${dot}"></span>
                                            ${stock.toLocaleString('en-US')}
                                        </span>
                                    </div>`;
                }).join('')}
                            </div>
                        </td>
                        <td class="px-6 py-4 align-top text-right" data-label="Price">
                            <div class="space-y-1.5">
                            ${skus.map(sku => {
                    const price = sku.sales_price || 0;
                    return `
                                    <div class="flex items-center justify-end py-1">
                                        <span class="font-bold text-slate-700 tabular-nums text-sm">${formatCurrency(price)}</span>
                                    </div>`;
                }).join('')}
                            </div>
                        </td>
                    </tr>
                `;
            });

        list.innerHTML = finalHtml;

        // Update header KPI chips
        const elIn = document.getElementById('inStockCount');
        const elLow = document.getElementById('lowStockCount');
        const elOut = document.getElementById('outOfStockCount');
        if (elIn) elIn.textContent = inCount.toLocaleString('en-US');
        if (elLow) elLow.textContent = lowCount.toLocaleString('en-US');
        if (elOut) elOut.textContent = outCount.toLocaleString('en-US');

    }, 50); // <-- THIS IS THE FIX: Closes the setTimeout we started at the top
}

export function initStocksDashboard() {
    const selectFilterIds = ['locFilterStocks', 'buFilter', 'stockStatusFilter'];
    selectFilterIds.forEach(id => {
        document.getElementById(id)?.addEventListener('change', renderStocksDashboard);
    });

    // Debounce the text input to prevent UI freezing while typing
    let stockSearchTimer;
    document.getElementById('stockSearchInput')?.addEventListener('input', () => {
        clearTimeout(stockSearchTimer);
        stockSearchTimer = setTimeout(renderStocksDashboard, 350);
    });
}