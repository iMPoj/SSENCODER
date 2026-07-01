<div id="readyOrdersPage" class="hidden drill-enter">
    <div class="max-w-7xl mx-auto space-y-6 pb-12">
        
        <div class="glass-card p-6 md:p-8 border-l-4 border-l-amber-500">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-[#0D111A] tracking-tight uppercase">Ready Orders (Drafts)</h1>
                    <p class="text-[#6B7280] mt-2 font-medium">
                        Orders saved as drafts. Review stock availability and post them to the Order Book.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button id="refreshReadyOrdersBtn" class="btn-secondary !py-2 px-4 shadow-sm text-xs uppercase font-bold tracking-wider">
                        Refresh List
                    </button>
                </div>
            </div>
        </div>

        <div class="glass-card p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label for="readyLocFilter" class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Location</label>
                    <select id="readyLocFilter" class="glass-input text-sm font-bold">
                        <option value="all">All Locations</option>
                        <option value="Davao">Davao</option>
                        <option value="Gensan">Gensan</option>
                    </select>
                </div>
                <div>
                    <label for="readyBuFilter" class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-widest mb-1">Business Unit</label>
                    <select id="readyBuFilter" class="glass-input text-sm">
                        <option value="all">All BUs</option>
                        <option value="Health">Health</option>
                        <option value="Hygiene">Hygiene</option>
                        <option value="Nutri">Nutri</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="glass-card overflow-hidden flex flex-col min-h-[400px]">
            <div class="px-6 py-4 border-b border-[rgba(13,17,26,0.08)] bg-[#F9FAFB] flex items-center justify-between">
                <h3 class="text-sm font-bold text-[#6B7280] uppercase tracking-wider flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                    Draft Orders Found
                </h3>
                <span id="readyOrdersCountBadge" class="text-xs font-black text-amber-600 bg-amber-100 px-3 py-1 rounded-full border border-amber-200">0 Drafts</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-white text-[#6B7280] font-semibold border-b border-[rgba(13,17,26,0.08)] sticky top-0 z-10">
                        <tr>
                            <th class="py-4 px-6 text-[10px] uppercase tracking-wider font-bold">PO Number / Date</th>
                            <th class="py-4 px-6 text-[10px] uppercase tracking-wider font-bold">Customer</th>
                            <th class="py-4 px-6 text-[10px] uppercase tracking-wider font-bold">Items Details</th>
                            <th class="py-4 px-6 text-[10px] uppercase tracking-wider font-bold text-center">Stock Status</th>
                            <th class="py-4 px-6 text-[10px] uppercase tracking-wider font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="readyOrdersList" class="divide-y divide-[rgba(13,17,26,0.08)]">
                        <tr>
                            <td colspan="5" class="text-center py-12 text-[#6B7280]">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-widest opacity-60">Loading Drafts...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>