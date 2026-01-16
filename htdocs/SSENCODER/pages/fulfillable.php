<div id="fulfillablePage" class="hidden space-y-6">
    <div class="content-card">
        <div class="md:flex justify-between items-start border-b pb-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Fulfillable Opportunities</h2>
                <p class="text-slate-500">Unserved priority items that now have stock available.</p>
            </div>
            <div class="text-left md:text-right mt-4 md:mt-0">
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wide">Total Value to Fulfill</h3>
                <p id="fulfillableGrandTotal" class="text-2xl font-bold text-emerald-600">₱0.00</p>
            </div>
        </div>
        
        <details class="group border rounded-lg overflow-hidden mb-6">
            <summary class="p-4 font-semibold cursor-pointer flex justify-between items-center text-slate-700 hover:bg-slate-50">
                <span>Show Filters</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform duration-200 group-open:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
            </summary>
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fulfillableLocFilter" class="block text-sm font-medium text-slate-700">Location</label>
                        <select id="fulfillableLocFilter" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"><option value="all">All Locations</option><option value="Davao">Davao</option><option value="Gensan">Gensan</option></select>
                    </div>
                    <div>
                        <label for="fulfillableBuFilter" class="block text-sm font-medium text-slate-700">Business Unit</label>
                        <select id="fulfillableBuFilter" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"><option value="all">All BUs</option><option value="Health">Health</option><option value="Hygiene">Hygiene</option><option value="Nutri">Nutri</option></select>
                    </div>
                    <div>
                         <label for="fulfillableCustomerFilter" class="block text-sm font-medium text-slate-700">Customer</label>
                        <select id="fulfillableCustomerFilter" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"><option value="all">All Customers</option></select>
                    </div>
                </div>
            </div>
        </details>

        <div class="overflow-x-auto">
            <table class="data-table w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-bold">
                    <tr>
                        <th class="px-6 py-4">Customer / PO</th>
                        <th class="px-6 py-4">Item Details</th>
                        <th class="px-6 py-4 text-center">Qty Needed</th>
                        <th class="px-6 py-4">Available Stock</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="fulfillableList" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
        <div id="fulfillableEmptyState" class="hidden text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-slate-500 font-medium">All priority orders are fulfilled!</p>
        </div>
    </div>
</div>