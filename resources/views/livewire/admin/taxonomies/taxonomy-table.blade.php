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
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <button wire:click="sortBy('plural_label')" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                Taxonomy
                                @if($sortField === 'plural_label')
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
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Post Types</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($taxonomies as $taxonomy)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                        <span class="material-symbols-outlined text-white">{{ $taxonomy->is_hierarchical ? 'account_tree' : 'label' }}</span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $taxonomy->plural_label }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">ID : {{ $taxonomy->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <code class="px-2 py-1 bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-gray-300 rounded text-sm font-mono">{{ $taxonomy->slug }}</code>
                            </td>
                            <td class="px-6 py-4">
                                @if(count($taxonomy->post_types ?? []) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($taxonomy->post_types as $postType)
                                            <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-medium border border-blue-100 dark:border-blue-900/30">{{ $postType }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">Not assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 {{ $taxonomy->is_hierarchical ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900/30' : 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 border border-orange-100 dark:border-orange-900/30' }} rounded-lg text-xs font-medium">
                                    <span class="material-symbols-outlined text-[16px]">{{ $taxonomy->is_hierarchical ? 'account_tree' : 'label' }}</span>
                                    {{ $taxonomy->is_hierarchical ? 'Hierarchical' : 'Flat' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button 
                                    wire:click="toggleStatus({{ $taxonomy->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all {{ $taxonomy->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}"
                                >
                                    <span class="material-symbols-outlined text-sm">{{ $taxonomy->is_active ? 'check_circle' : 'cancel' }}</span>
                                    {{ $taxonomy->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                     <a 
                                        href="{{ route('admin.taxonomies.terms.index', $taxonomy->id) }}"
                                        class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-all"
                                        title="Manage Terms"
                                    >
                                        <span class="material-symbols-outlined">list</span>
                                    </a>
                                    <a 
                                        href="{{ route('admin.taxonomies.edit', $taxonomy->id) }}"
                                        class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all"
                                        title="Edit"
                                    >
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                    <button 
                                        wire:click="delete({{ $taxonomy->id }})"
                                        wire:confirm="Are you sure you want to delete this taxonomy?"
                                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                                        title="Delete"
                                    >
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                        <span class="material-symbols-outlined text-3xl text-gray-400">category</span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No taxonomies yet</h3>
                                    <p class="text-gray-500 dark:text-gray-400 mb-4">Create your first taxonomy to get started</p>
                                    <a href="{{ route('admin.taxonomies.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all">
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
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $taxonomies->links() }}
            </div>
        @endif
    </div>
</div>
