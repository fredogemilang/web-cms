<div class="space-y-6">
    <!-- Filters & Actions -->
    <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="Search terms..."
                class="h-12 w-full md:w-[320px] rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"
            >
        </div>
        
        <!-- Per Page -->
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-[#6F767E]">Display:</span>
            <select 
                wire:model.live="perPage"
                class="h-12 rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-4 pr-10 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer"
            >
                <option value="10">10 Rows</option>
                <option value="25">25 Rows</option>
                <option value="50">50 Rows</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden relative">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <button wire:click="sortBy('name')" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                Name
                                @if($sortField === 'name')
                                    <span class="material-symbols-outlined text-sm">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <button wire:click="sortBy('slug')" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                Slug
                                @if($sortField === 'slug')
                                    <span class="material-symbols-outlined text-sm">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Count</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($terms as $term)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2" style="padding-left: {{ ($term->depth ?? 0) * 1.5 }}rem">
                                    @if(($term->depth ?? 0) > 0)
                                        <span class="material-symbols-outlined text-gray-400 text-base shrink-0 select-none">subdirectory_arrow_right</span>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors">{{ $term->name }}</div>
                                        
                                        <!-- WP Style Hover Actions -->
                                        <div class="flex items-center gap-2 mt-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                            <button 
                                                wire:click="$dispatch('edit-term', { id: {{ $term->id }} })"
                                                class="text-[11px] font-bold text-blue-600 hover:text-blue-700 uppercase tracking-tighter"
                                            >
                                                Edit
                                            </button>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <button 
                                                wire:click="confirmDelete({{ $term->id }})"
                                                class="text-[11px] font-bold text-red-500 hover:text-red-700 uppercase tracking-tighter"
                                            >
                                                Delete
                                            </button>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <a href="#" class="text-[11px] font-bold text-[#6F767E] hover:text-gray-900 dark:hover:text-white uppercase tracking-tighter">
                                                View
                                            </a>
                                        </div>

                                        @if($term->description)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs mt-1">{{ $term->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <code class="px-2 py-1 bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-gray-300 rounded text-sm font-mono">{{ $term->slug }}</code>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ $term->entries_count }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                        <span class="material-symbols-outlined text-3xl text-gray-400">category</span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No terms found</h3>
                                    <p class="text-gray-500 dark:text-gray-400">Use the form on the left to add a new term.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($terms->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $terms->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div 
        x-data="{ show: @entangle('showDeleteModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500/75 dark:bg-[#1A1A1A]/75 backdrop-blur-sm transition-opacity"
        ></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div 
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-[#1A1A1A] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg ring-1 ring-black/5 dark:ring-white/10"
                @click.away="show = false; $wire.cancelDelete()"
            >
                <div class="bg-white dark:bg-[#1A1A1A] px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400">warning</span>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">Delete Term</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to delete this term? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-[#1A1A1A]/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100 dark:border-[#272B30]">
                    <button 
                        type="button" 
                        wire:click="performDelete"
                        class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-all"
                    >
                        Delete
                    </button>
                    <button 
                        type="button" 
                        wire:click="cancelDelete"
                        class="mt-3 inline-flex w-full justify-center rounded-xl bg-white dark:bg-[#272B30] px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-[#2C3035] sm:mt-0 sm:w-auto transition-all"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
