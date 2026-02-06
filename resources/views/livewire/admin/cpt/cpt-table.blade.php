<div class="space-y-6">
    <!-- Filters & Actions -->
    <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="Search post types..."
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
        <div class="flex gap-2">
            <a href="{{ route('admin.cpt.wordpress-migration') }}" 
            class="flex items-center justify-center rounded-xl bg-purple-500 px-6 py-3 text-sm font-bold text-white hover:bg-purple-600 transition-all shadow-lg shadow-purple-500/20">
                <span class="material-symbols-outlined mr-2">cloud_download</span>
                <span>Import from WP</span>
            </a>
            <a href="{{ route('admin.cpt.create') }}" 
            class="flex items-center justify-center rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                <span>Add Post Type</span>
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden relative">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <button wire:click="sortBy('plural_label')" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                Post Type
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
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fields</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($postTypes as $cpt)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                                        <span class="material-symbols-outlined text-white">{{ $cpt->icon }}</span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $cpt->plural_label }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">ID : {{ $cpt->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <code class="px-2 py-1 bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-gray-300 rounded text-sm font-mono">{{ $cpt->slug }}</code>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 dark:bg-[#272B30] text-gray-600 dark:text-gray-400 rounded-lg text-sm">
                                    <span class="material-symbols-outlined text-sm">list</span>
                                    {{ $cpt->metaFields->count() }} fields
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button 
                                    wire:click="toggleStatus({{ $cpt->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all {{ $cpt->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}"
                                >
                                    <span class="material-symbols-outlined text-sm">{{ $cpt->is_active ? 'check_circle' : 'cancel' }}</span>
                                    {{ $cpt->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a 
                                        href="{{ route('admin.cpt.entries.index', $cpt->slug) }}"
                                        class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-all"
                                        title="View {{ $cpt->plural_label }}"
                                    >
                                        <span class="material-symbols-outlined">folder_open</span>
                                    </a>
                                    <a 
                                        href="{{ route('admin.cpt.edit', $cpt->id) }}"
                                        class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all"
                                        title="Edit"
                                    >
                                        <span class="material-symbols-outlined">edit</span>
                                    </a>
                                    <button 
                                        wire:click="confirmDelete({{ $cpt->id }})"
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
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                        <span class="material-symbols-outlined text-3xl text-gray-400">layers</span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No post types yet</h3>
                                    <p class="text-gray-500 dark:text-gray-400 mb-4">Create your first custom post type to get started</p>
                                    <a href="{{ route('admin.cpt.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all">
                                        <span class="material-symbols-outlined text-lg">add</span>
                                        <span>Create Post Type</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($postTypes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $postTypes->links() }}
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
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">Delete Post Type</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to delete this post type? This action cannot be undone and will permanently remove all associated meta fields and entries.
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
