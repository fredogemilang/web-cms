<div class="space-y-6">
    <!-- Filters & Actions -->
    <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="Search taxonomies..."
                class="h-12 w-full md:w-[320px] rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"
            >
            <!-- Status Filter -->
            <div class="inline-flex w-fit items-center bg-gray-100/50 dark:bg-[#0B0B0B]/30 p-1 rounded-2xl ring-1 ring-gray-200 dark:ring-[#272B30] ml-2">
                <button 
                    wire:click="$set('status', '')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === '' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}"
                >
                    All
                </button>
                <button 
                    wire:click="$set('status', 'active')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === 'active' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}"
                >
                    Active
                </button>
                <button 
                    wire:click="$set('status', 'inactive')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === 'inactive' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}"
                >
                    Inactive
                </button>
            </div>
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

        <!-- Add Button -->
        <a href="{{ route('admin.taxonomies.create') }}" 
           class="flex items-center justify-center rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
            <span>Add Taxonomy</span>
        </a>
    </div>

    <!-- Table -->
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden relative">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                        <th class="px-8 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">
                            <button wire:click="sortBy('plural_label')" class="flex items-center gap-1 hover:text-[#2563EB] transition-colors">
                                Taxonomy
                                @if($sortField === 'plural_label')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">
                            <button wire:click="sortBy('slug')" class="flex items-center gap-1 hover:text-[#2563EB] transition-colors">
                                Slug
                                @if($sortField === 'slug')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Post Types</th>
                        <th class="px-4 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Type</th>
                        <th class="px-4 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30">
                    @forelse($taxonomies as $taxonomy)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30]/30 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                        <span class="material-symbols-outlined text-white text-[20px]">{{ $taxonomy->is_hierarchical ? 'account_tree' : 'label' }}</span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-[#111827] dark:text-[#FCFCFC]">{{ $taxonomy->plural_label }}</div>
                                        <div class="text-xs text-[#6F767E]">ID : {{ $taxonomy->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-5">
                                <code class="px-2.5 py-1 bg-gray-100 dark:bg-[#272B30] text-[#2563EB] rounded-lg text-xs font-mono">{{ $taxonomy->slug }}</code>
                            </td>
                            <td class="px-4 py-5">
                                @if(count($taxonomy->post_types ?? []) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($taxonomy->post_types as $postType)
                                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-[#2563EB] rounded-lg text-xs font-bold uppercase tracking-wider">{{ $postType }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-[#6F767E] text-xs">Not assigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 {{ $taxonomy->is_hierarchical ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400' : 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400' }} rounded-lg text-xs font-bold uppercase tracking-wider">
                                    <span class="material-symbols-outlined text-[16px]">{{ $taxonomy->is_hierarchical ? 'account_tree' : 'label' }}</span>
                                    {{ $taxonomy->is_hierarchical ? 'Hierarchical' : 'Flat' }}
                                </span>
                            </td>
                            <td class="px-4 py-5">
                                <button 
                                    wire:click="toggleStatus({{ $taxonomy->id }})"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wider transition-all {{ $taxonomy->is_active ? 'bg-[#3F8C5826] text-[#83BF6E]' : 'bg-gray-100 dark:bg-[#272B30] text-[#6F767E]' }}"
                                >
                                    <span class="material-symbols-outlined text-sm">{{ $taxonomy->is_active ? 'check_circle' : 'cancel' }}</span>
                                    {{ $taxonomy->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                     <a 
                                        href="{{ route('admin.taxonomies.terms.index', $taxonomy->id) }}"
                                        class="h-9 w-9 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-emerald-500 flex items-center justify-center transition-colors"
                                        title="Manage Terms"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">list</span>
                                    </a>
                                    <a 
                                        href="{{ route('admin.taxonomies.edit', $taxonomy->id) }}"
                                        class="h-9 w-9 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] flex items-center justify-center transition-colors"
                                        title="Edit"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    <button 
                                        wire:click="delete({{ $taxonomy->id }})"
                                        wire:confirm="Are you sure you want to delete this taxonomy?"
                                        class="h-9 w-9 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 text-[#6F767E] hover:text-[#FF6A55] flex items-center justify-center transition-colors"
                                        title="Delete"
                                    >
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                        <span class="material-symbols-outlined text-3xl text-gray-400">category</span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No taxonomies yet</h3>
                                    <p class="text-gray-500 dark:text-gray-400 mb-4">Create your first taxonomy to get started</p>
                                    <a href="{{ route('admin.taxonomies.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#2563EB] hover:bg-blue-600 text-white font-medium rounded-xl transition-all">
                                        <span class="material-symbols-outlined text-lg">add</span>
                                        <span>Create Taxonomy</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($taxonomies->hasPages())
            <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30]">
                {{ $taxonomies->links() }}
            </div>
        @endif
    </div>
</div>
