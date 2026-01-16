import { appState } from './state.js';

export function renderStocksDashboard() {
    const list = document.getElementById('stocksDashboardList');
    if (!list) return;

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
        list.innerHTML = `<tr><td colspan="4" class="text-center py-12 text-slate-400 italic">No products match your search.</td></tr>`;
        return;
    }

    let finalHtml = '';
    filteredProducts
        .sort((a,b) => a.description.localeCompare(b.description))
        .forEach(productGroup => {
            const barcode = productGroup.codes.find(c => c.type === 'barcode');
            const skus = productGroup.codes.filter(c => c.type === 'sku').sort((a,b) => a.sku.localeCompare(b.sku));

            finalHtml += `
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="py-4 px-6 align-top" data-label="Barcode / SKU">
                        <div class="font-bold text-slate-800">${barcode ? barcode.sku : 'NO BARCODE'}</div>
                        ${skus.map(sku => `<div class="font-mono text-xs text-slate-500 mt-1 group-hover:text-indigo-500 transition-colors">${sku.sku}</div>`).join('')}
                    </td>
                    <td class="py-4 px-6 align-top" data-label="Description">
                        <div class="font-medium text-slate-700">${productGroup.description}</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 mt-2">
                            ${productGroup.bu}
                        </span>
                    </td>
                    <td class="py-4 px-6 align-top" data-label="Stock on Hand">
                        ${skus.map(sku => {
                            const invEntry = sku.inventory.find(inv => inv.location === selectedLoc);
                            const stock = invEntry ? parseInt(invEntry.stock) : 0;
                            // Color logic: 0 = slate, low = amber, good = emerald
                            const stockColor = stock === 0 ? 'text-slate-400' : (stock <= 10 ? 'text-amber-600 font-bold' : 'text-emerald-600 font-bold');
                            
                            return `<div class="flex justify-between items-center gap-4 mt-1 py-1 border-b border-slate-50 last:border-0">
                                        <span class="font-mono text-xs text-slate-400 w-20">${sku.sku}</span>
                                        <span class="${stockColor}">${stock.toLocaleString('en-US')}</span>
                                    </div>`;
                        }).join('')}
                    </td>
                    <td class="py-4 px-6 align-top text-right" data-label="Price">
                        ${skus.map(sku => {
                            const price = sku.sales_price || 0;
                            return `<div class="flex justify-end items-center mt-1 py-1 h-[25px]">
                                        <span class="font-medium text-slate-700">${formatCurrency(price)}</span>
                                    </div>`;
                        }).join('')}
                    </td>
                </tr>
            `;
        });

    list.innerHTML = finalHtml;
}

export function initStocksDashboard() {
    const filterIds = ['locFilterStocks', 'stockSearchInput', 'buFilter', 'stockStatusFilter'];
    filterIds.forEach(id => {
        document.getElementById(id)?.addEventListener('input', renderStocksDashboard);
    });
}