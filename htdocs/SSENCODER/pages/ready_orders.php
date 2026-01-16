<div id="readyOrdersPage" class="hidden">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Ready for Submission</h1>
                <p class="text-sm text-slate-500">Draft orders waiting for stock or approval.</p>
            </div>
            <button id="refreshReadyOrdersBtn" class="btn bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-bold">
                Refresh List
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-bold">
                        <tr>
                            <th class="px-6 py-4">Customer / PO</th>
                            <th class="px-6 py-4">Draft Items</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="readyOrdersList" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
            <div id="readyOrdersEmptyState" class="hidden text-center py-12">
                <p class="text-slate-500 font-medium">No draft orders found.</p>
            </div>
        </div>

    </div>
</div>