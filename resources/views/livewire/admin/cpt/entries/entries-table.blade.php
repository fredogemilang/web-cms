<div class="space-y-6">
    <!-- Header -->
    <!-- Filters & Search -->
    <div class="space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left: Search & Category -->
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative group w-full sm:w-auto">
                    <input
                        wire:model.live.debounce.300ms="search"
                        class="h-12 w-full sm:w-[320px] rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"
                        placeholder="Search {{ strtolower($postType->plural_label) }}..." type="text" />
                    <span
                        class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#6F767E] group-focus-within:text-[#2563EB] transition-colors">search</span>
                    
                    <!-- Loading indicator -->
                    <div wire:loading wire:target="search" class="absolute right-4 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-[#2563EB]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Right: Display & Add New -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-[#6F767E]">Display:</span>
                    <select
                        wire:model.live="perPage"
                        class="h-12 rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-4 pr-10 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer">
                        <option value="10">10 Rows</option>
                        <option value="25">25 Rows</option>
                        <option value="50">50 Rows</option>
                    </select>
                </div>
                
                <a href="{{ route('admin.cpt.entries.create', $postType->slug) }}" 
                   class="flex items-center justify-center rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 h-12 whitespace-nowrap">
                    Add {{ $postType->singular_label }}
                </a>
            </div>
        </div>

        <!-- Status Filter Buttons -->
        <div class="mb-4">
             <div class="inline-flex w-fit items-center bg-gray-100/50 dark:bg-[#0B0B0B]/30 p-1 rounded-2xl ring-1 ring-gray-200 dark:ring-[#272B30]">
                <!-- All -->
                <button
                    wire:click="$set('status', '')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === '' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                    All
                    <span class="px-2 py-0.5 rounded-lg {{ $status === '' ? 'bg-blue-50 dark:bg-blue-900/20 text-[#2563EB]' : 'bg-gray-200/50 dark:bg-[#272B30] text-[#6F767E]' }} text-[10px] font-bold">
                        {{ $statusCounts['all'] }}
                    </span>
                </button>

                <!-- Published -->
                <button
                    wire:click="$set('status', 'published')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === 'published' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                    Published
                    <span class="px-2 py-0.5 rounded-lg {{ $status === 'published' ? 'bg-blue-50 dark:bg-blue-900/20 text-[#2563EB]' : 'bg-gray-200/50 dark:bg-[#272B30] text-[#6F767E]' }} text-[10px] font-bold">
                        {{ $statusCounts['published'] }}
                    </span>
                </button>

                <!-- Draft -->
                <button
                    wire:click="$set('status', 'draft')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === 'draft' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                    Draft
                    <span class="px-2 py-0.5 rounded-lg {{ $status === 'draft' ? 'bg-blue-50 dark:bg-blue-900/20 text-[#2563EB]' : 'bg-gray-200/50 dark:bg-[#272B30] text-[#6F767E]' }} text-[10px] font-bold">
                        {{ $statusCounts['draft'] }}
                    </span>
                </button>

                <!-- Scheduled -->
                @if($statusCounts['scheduled'] > 0)
                <button
                    wire:click="$set('status', 'scheduled')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === 'scheduled' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                    Scheduled
                    <span class="px-2 py-0.5 rounded-lg {{ $status === 'scheduled' ? 'bg-blue-50 dark:bg-blue-900/20 text-[#2563EB]' : 'bg-gray-200/50 dark:bg-[#272B30] text-[#6F767E]' }} text-[10px] font-bold">
                        {{ $statusCounts['scheduled'] }}
                    </span>
                </button>
                @endif

                <!-- Trash -->
                @if($statusCounts['trash'] > 0)
                <button
                    wire:click="$set('status', 'trash')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === 'trash' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                    Trash
                    <span class="px-2 py-0.5 rounded-lg {{ $status === 'trash' ? 'bg-blue-50 dark:bg-blue-900/20 text-[#2563EB]' : 'bg-gray-200/50 dark:bg-[#272B30] text-[#6F767E]' }} text-[10px] font-bold">
                        {{ $statusCounts['trash'] }}
                    </span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden relative">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                        <th class="px-6 py-6 w-10">
                            <input
                                wire:model.live="selectAll"
                                class="custom-checkbox"
                                type="checkbox" />
                        </th>
                        <th class="px-6 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">
                            <button wire:click="sortBy('title')" class="flex items-center gap-1 hover:text-[#2563EB] transition-colors">
                                Title
                                @if($sortField === 'title')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Author</th>
                        <th class="px-6 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Status</th>
                        <th class="px-6 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-[#2563EB] transition-colors">
                                Date
                                @if($sortField === 'created_at')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors" wire:key="entry-{{ $entry->id }}">
                            <td class="px-6 py-5">
                                <input
                                    wire:model.live="selectedEntries"
                                    value="{{ $entry->id }}"
                                    class="custom-checkbox"
                                    type="checkbox" />
                            </td>
                            <td class="px-6 py-5">
                                <div>
                                    <a href="{{ route('admin.cpt.entries.edit', [$postType->slug, $entry->id]) }}" class="text-[15px] font-bold text-[#111827] dark:text-[#FCFCFC] hover:text-[#2563EB] dark:hover:text-[#2563EB] transition-colors line-clamp-1">
                                        {{ $entry->title }}
                                    </a>
                                    <div class="text-xs text-[#6F767E] mt-0.5">{{ $entry->slug }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 text-xs font-bold">
                                        {{ strtoupper(substr($entry->author->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $entry->author->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @php 
                                    $badge = $entry->status_badge;
                                    // Map badge colors to our design system
                                    $badgeClasses = match($badge['color']) {
                                        'green' => 'bg-[#3F8C5826] text-[#83BF6E]',
                                        'blue' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                                        'amber' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                                        default => 'bg-gray-100 dark:bg-[#272B30] text-[#6F767E]',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-lg {{ $badgeClasses }} px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $entry->created_at->format('M d, Y') }}</p>
                                <p class="text-xs text-[#6F767E]">{{ $entry->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($status === 'trash')
                                        <button 
                                            wire:click="restore({{ $entry->id }})"
                                            class="relative group/restore w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors"
                                            title="Restore">
                                            <span class="material-symbols-outlined text-[20px]">restore_from_trash</span>
                                        </button>
                                        <button 
                                            x-data
                                            @click="$dispatch('open-force-delete-modal', { entryId: {{ $entry->id }}, entryTitle: '{{ addslashes($entry->title) }}' })"
                                            class="relative group/delete w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#FF6A55] transition-colors"
                                            title="Delete Permanently">
                                            <span class="material-symbols-outlined text-[20px]">delete_forever</span>
                                        </button>
                                    @else
                                        <a 
                                            href="{{ route('admin.cpt.entries.edit', [$postType->slug, $entry->id]) }}"
                                            class="relative group/edit w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors"
                                            title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <button 
                                            x-data
                                            @click="$dispatch('open-delete-modal', { entryId: {{ $entry->id }}, entryTitle: '{{ addslashes($entry->title) }}' })"
                                            class="relative group/delete w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#FF6A55] transition-colors"
                                            title="Move to Trash">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="h-16 w-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                        <span class="material-symbols-outlined text-3xl text-[#6F767E]">{{ $postType->icon }}</span>
                                    </div>
                                    @if($status === 'trash')
                                        <h3 class="text-[#111827] dark:text-[#FCFCFC] font-medium mb-1">Trash is empty</h3>
                                        <p class="text-[#6F767E] text-sm">No {{ strtolower($postType->plural_label) }} in trash</p>
                                    @else
                                        <h3 class="text-[#111827] dark:text-[#FCFCFC] font-medium mb-1">No {{ strtolower($postType->plural_label) }} yet</h3>
                                        <p class="text-[#6F767E] text-sm mb-6">Create your first {{ strtolower($postType->singular_label) }} to get started</p>
                                        <a href="{{ route('admin.cpt.entries.create', $postType->slug) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#2563EB] hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/20">
                                            <span class="material-symbols-outlined text-lg">add</span>
                                            <span>Add {{ $postType->singular_label }}</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($entries->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

    <!-- Bulk Action Bar -->
    @if(count($selectedEntries) > 0)
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">
        <div class="bg-[#2563EB] dark:bg-[#1A1A1A] border border-[#2563EB] dark:border-[#272B30] rounded-2xl shadow-2xl px-6 py-3 flex items-center gap-6">
            <div class="flex items-center gap-3 border-r border-blue-400/30 dark:border-[#272B30] pr-6">
                <span class="bg-white text-[#2563EB] text-xs font-bold px-2.5 py-1 rounded-full min-w-[24px] text-center">{{ count($selectedEntries) }}</span>
                <span class="text-sm font-semibold text-white">Selected</span>
            </div>
            <div class="flex items-center gap-4">
                @if($status === 'trash')
                <button 
                    wire:click="restoreSelected"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">restore_from_trash</span>
                    Restore
                </button>
                <button 
                    x-data
                    @click="$dispatch('open-bulk-force-delete-modal')"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-[#FF6A55] transition-colors">
                    <span class="material-symbols-outlined text-[20px]">delete_forever</span>
                    Delete Permanently
                </button>
                @else
                <button 
                    @click="$dispatch('open-publish-modal')"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">publish</span>
                    Publish
                </button>
                <button 
                    @click="$dispatch('open-draft-modal')"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">edit_document</span>
                    Draft
                </button>
                <button 
                    x-data
                    @click="$dispatch('open-bulk-delete-modal')"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-[#FF6A55] transition-colors">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                    Delete
                </button>
                @endif
            </div>
            <button wire:click="clearSelection" class="ml-2 w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 text-white/70 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    </div>
    @endif

    <!-- Delete Modal -->
    <div 
        x-data="{ show: false, entryId: null, entryTitle: '', bulk: false }"
        @open-delete-modal.window="show = true; entryId = $event.detail.entryId; entryTitle = $event.detail.entryTitle; bulk = false"
        @open-bulk-delete-modal.window="show = true; bulk = true"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div 
            @click.outside="show = false"
            x-show="show"
            x-transition
            class="w-full max-w-[440px] bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#272B30] rounded-3xl shadow-2xl p-8">
            <div class="flex flex-col items-center text-center">
                <div class="h-16 w-16 rounded-full bg-[#FF6A55]/10 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-[#FF6A55] text-3xl">delete_forever</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC] mb-3">Delete Item</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed mb-8">
                    <template x-if="bulk">
                        <span>Are you sure you want to delete <span class="font-bold">{{ count($selectedEntries) }}</span> selected item(s)?</span>
                    </template>
                    <template x-if="!bulk">
                        <span>Are you sure you want to delete "<span class="font-bold" x-text="entryTitle"></span>"?</span>
                    </template>
                </p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="show = false"
                        class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all">
                        Cancel
                    </button>
                    <button 
                        @click="if (bulk) { $wire.deleteSelected(); } else { $wire.delete(entryId); } show = false;"
                        class="flex-1 h-12 rounded-xl bg-[#FF6A55] text-white text-sm font-bold hover:bg-[#E55F4D] transition-all shadow-lg shadow-[#FF6A55]/20">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Publish Modal -->
    <div 
        x-data="{ show: false }"
        @open-publish-modal.window="show = true"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div 
            @click.outside="show = false"
            x-show="show"
            x-transition
            class="w-full max-w-[440px] bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#272B30] rounded-3xl shadow-2xl p-8">
            <div class="flex flex-col items-center text-center">
                <div class="h-16 w-16 rounded-full bg-[#83BF6E]/10 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-[#83BF6E] text-3xl">publish</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC] mb-3">Publish Items</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed mb-8">
                    Are you sure you want to publish <span class="font-bold">{{ count($selectedEntries) }}</span> selected item(s)?
                </p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="show = false"
                        class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all">
                        Cancel
                    </button>
                    <button 
                        @click="$wire.publishSelected(); show = false;"
                        class="flex-1 h-12 rounded-xl bg-[#83BF6E] text-white text-sm font-bold hover:bg-[#6fa85a] transition-all shadow-lg shadow-[#83BF6E]/20">
                        Publish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Draft Modal -->
    <div 
        x-data="{ show: false }"
        @open-draft-modal.window="show = true"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div 
            @click.outside="show = false"
            x-show="show"
            x-transition
            class="w-full max-w-[440px] bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#272B30] rounded-3xl shadow-2xl p-8">
            <div class="flex flex-col items-center text-center">
                <div class="h-16 w-16 rounded-full bg-amber-500/10 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-amber-500 text-3xl">edit_document</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC] mb-3">Move to Draft</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed mb-8">
                    Are you sure you want to move <span class="font-bold">{{ count($selectedEntries) }}</span> selected item(s) to draft?
                </p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="show = false"
                        class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all">
                        Cancel
                    </button>
                    <button 
                        @click="$wire.draftSelected(); show = false;"
                        class="flex-1 h-12 rounded-xl bg-amber-500 text-white text-sm font-bold hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20">
                        Move to Draft
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Force Delete Modal -->
    <div 
        x-data="{ show: false, entryId: null, entryTitle: '', bulk: false }"
        @open-force-delete-modal.window="show = true; entryId = $event.detail.entryId; entryTitle = $event.detail.entryTitle; bulk = false"
        @open-bulk-force-delete-modal.window="show = true; bulk = true"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div 
            @click.outside="show = false"
            x-show="show"
            x-transition
            class="w-full max-w-[440px] bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#272B30] rounded-3xl shadow-2xl p-8">
            <div class="flex flex-col items-center text-center">
                <div class="h-16 w-16 rounded-full bg-[#FF6A55]/10 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-[#FF6A55] text-3xl">delete_forever</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC] mb-3">Delete Permanently</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed mb-8">
                    <template x-if="bulk">
                        <span>Are you sure you want to PERMANENTLY delete <span class="font-bold">{{ count($selectedEntries) }}</span> selected item(s)? This action cannot be undone.</span>
                    </template>
                    <template x-if="!bulk">
                        <span>Are you sure you want to PERMANENTLY delete "<span class="font-bold" x-text="entryTitle"></span>"? This action cannot be undone.</span>
                    </template>
                </p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="show = false"
                        class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all">
                        Cancel
                    </button>
                    <button 
                        @click="if (bulk) { $wire.forceDeleteSelected(); } else { $wire.forceDelete(entryId); } show = false;"
                        class="flex-1 h-12 rounded-xl bg-[#FF6A55] text-white text-sm font-bold hover:bg-[#E55F4D] transition-all shadow-lg shadow-[#FF6A55]/20">
                        Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
