<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">Pages</h2>
            <p class="text-sm text-[#6F767E] mt-1">Manage your site pages</p>
        </div>
        <a href="{{ route('admin.pages.create') }}"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-primary hover:bg-blue-600 shadow-lg shadow-primary/20 transition-all">
            <span class="material-symbols-outlined text-lg">add</span>
            Add New Page
        </a>
    </div>

    {{-- Status Tabs --}}
    <div class="flex items-center gap-1 border-b border-gray-200 dark:border-[#272B30]">
        <button wire:click="setStatus('')"
            class="px-4 py-3 text-sm font-semibold transition-colors relative {{ $status === '' ? 'text-primary' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
            All <span class="text-xs opacity-60">({{ $statusCounts['all'] }})</span>
            @if($status === '')
                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"></div>
            @endif
        </button>
        <button wire:click="setStatus('published')"
            class="px-4 py-3 text-sm font-semibold transition-colors relative {{ $status === 'published' ? 'text-primary' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
            Published <span class="text-xs opacity-60">({{ $statusCounts['published'] }})</span>
            @if($status === 'published')
                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"></div>
            @endif
        </button>
        <button wire:click="setStatus('draft')"
            class="px-4 py-3 text-sm font-semibold transition-colors relative {{ $status === 'draft' ? 'text-primary' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
            Draft <span class="text-xs opacity-60">({{ $statusCounts['draft'] }})</span>
            @if($status === 'draft')
                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"></div>
            @endif
        </button>
        <button wire:click="setStatus('scheduled')"
            class="px-4 py-3 text-sm font-semibold transition-colors relative {{ $status === 'scheduled' ? 'text-primary' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
            Scheduled <span class="text-xs opacity-60">({{ $statusCounts['scheduled'] }})</span>
            @if($status === 'scheduled')
                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"></div>
            @endif
        </button>
        <button wire:click="setStatus('private')"
            class="px-4 py-3 text-sm font-semibold transition-colors relative {{ $status === 'private' ? 'text-primary' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
            Private <span class="text-xs opacity-60">({{ $statusCounts['private'] }})</span>
            @if($status === 'private')
                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"></div>
            @endif
        </button>
    </div>

    {{-- Search & Actions Bar --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#6F767E]">search</span>
            <input wire:model.live.debounce.300ms="search" type="text"
                class="w-full h-10 pl-10 pr-4 rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-sm text-[#111827] dark:text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-primary focus:border-transparent"
                placeholder="Search pages..." />
        </div>

        @if(count($selectedPages) > 0)
            <div class="flex items-center gap-2">
                <span class="text-sm text-[#6F767E]">{{ count($selectedPages) }} selected</span>
                <button wire:click="bulkPublish"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-green-600 bg-green-50 dark:bg-green-500/10 hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors">
                    Publish
                </button>
                <button wire:click="bulkDraft"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-yellow-600 bg-yellow-50 dark:bg-yellow-500/10 hover:bg-yellow-100 dark:hover:bg-yellow-500/20 transition-colors">
                    Draft
                </button>
                <button wire:click="confirmBulkDelete"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-red-600 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                    Delete
                </button>
            </div>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-200 dark:border-[#272B30] overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-[#272B30]">
                    <th class="w-12 px-4 py-3">
                        <input type="checkbox" wire:model.live="selectAll"
                            class="h-4 w-4 rounded text-primary border-gray-300 dark:border-[#272B30] focus:ring-primary bg-white dark:bg-[#0B0B0B]" />
                    </th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('title')" class="flex items-center gap-1 text-xs font-bold text-[#6F767E] uppercase tracking-wider hover:text-[#111827] dark:hover:text-[#FCFCFC]">
                            Title
                            @if($sortField === 'title')
                                <span class="material-symbols-outlined text-sm">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <span class="text-xs font-bold text-[#6F767E] uppercase tracking-wider">Author</span>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('status')" class="flex items-center gap-1 text-xs font-bold text-[#6F767E] uppercase tracking-wider hover:text-[#111827] dark:hover:text-[#FCFCFC]">
                            Status
                            @if($sortField === 'status')
                                <span class="material-symbols-outlined text-sm">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('updated_at')" class="flex items-center gap-1 text-xs font-bold text-[#6F767E] uppercase tracking-wider hover:text-[#111827] dark:hover:text-[#FCFCFC]">
                            Last Modified
                            @if($sortField === 'updated_at')
                                <span class="material-symbols-outlined text-sm">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="w-32 px-4 py-3 text-right">
                        <span class="text-xs font-bold text-[#6F767E] uppercase tracking-wider">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-[#272B30]">
                @forelse($pages as $page)
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30]/30 transition-colors" wire:key="page-{{ $page->id }}">
                        <td class="px-4 py-3">
                            <input type="checkbox" wire:model.live="selectedPages" value="{{ $page->id }}"
                                class="h-4 w-4 rounded text-primary border-gray-300 dark:border-[#272B30] focus:ring-primary bg-white dark:bg-[#0B0B0B]" />
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <a href="{{ route('admin.pages.edit', $page->id) }}"
                                    class="font-semibold text-[#111827] dark:text-[#FCFCFC] hover:text-primary transition-colors">
                                    {{ $page->title }}
                                </a>
                                <div class="text-xs text-[#6F767E] flex items-center gap-1 mt-0.5">
                                    <span class="material-symbols-outlined text-xs">link</span>
                                    /{{ $page->slug }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-7 w-7 rounded-full bg-gray-200 dark:bg-[#272B30] flex items-center justify-center text-xs font-bold text-[#6F767E]">
                                    {{ substr($page->author?->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="text-sm text-[#6F767E]">{{ $page->author?->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @switch($page->status)
                                @case('published')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        Published
                                    </span>
                                    @break
                                @case('draft')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-yellow-100 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        Draft
                                    </span>
                                    @break
                                @case('scheduled')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        Scheduled
                                    </span>
                                    @break
                                @case('private')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-500/10 text-gray-700 dark:text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        Private
                                    </span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-[#6F767E]">{{ $page->updated_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.pages.edit', $page->id) }}"
                                    class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white flex items-center justify-center transition-colors"
                                    title="Edit">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <button wire:click="duplicate({{ $page->id }})"
                                    class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white flex items-center justify-center transition-colors"
                                    title="Duplicate">
                                    <span class="material-symbols-outlined text-lg">content_copy</span>
                                </button>
                                @if($page->status === 'published')
                                    <a href="{{ url($page->slug) }}" target="_blank"
                                        class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white flex items-center justify-center transition-colors"
                                        title="View">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                @endif
                                <button wire:click="confirmDelete({{ $page->id }})"
                                    class="h-8 w-8 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-[#6F767E] hover:text-red-500 flex items-center justify-center transition-colors"
                                    title="Delete">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-[#272B30] mb-4">description</span>
                                <p class="text-[#6F767E] font-medium">No pages found</p>
                                <p class="text-sm text-[#6F767E] mt-1">Get started by creating your first page.</p>
                                <a href="{{ route('admin.pages.create') }}"
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-primary hover:bg-primary/10 transition-colors">
                                    <span class="material-symbols-outlined text-lg">add</span>
                                    Create Page
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pages->hasPages())
        <div class="flex justify-center">
            {{ $pages->links() }}
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white dark:bg-[#1A1A1A] rounded-2xl shadow-2xl p-6">
            <div class="text-center">
                <div class="h-12 w-12 rounded-full bg-red-100 dark:bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-red-500 text-2xl">delete</span>
                </div>
                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Delete Page</h3>
                <p class="text-sm text-[#6F767E] mb-6">Are you sure you want to delete this page? This action cannot be undone.</p>
                <div class="flex items-center gap-3">
                    <button wire:click="cancelDelete"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-[#111827] dark:text-[#FCFCFC] bg-gray-100 dark:bg-[#272B30] hover:bg-gray-200 dark:hover:bg-[#272B30]/80 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="delete"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-500 hover:bg-red-600 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Delete Modal --}}
    @if($showBulkDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white dark:bg-[#1A1A1A] rounded-2xl shadow-2xl p-6">
            <div class="text-center">
                <div class="h-12 w-12 rounded-full bg-red-100 dark:bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-red-500 text-2xl">delete_sweep</span>
                </div>
                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Delete {{ count($selectedPages) }} Pages</h3>
                <p class="text-sm text-[#6F767E] mb-6">Are you sure you want to delete the selected pages? This action cannot be undone.</p>
                <div class="flex items-center gap-3">
                    <button wire:click="cancelBulkDelete"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-[#111827] dark:text-[#FCFCFC] bg-gray-100 dark:bg-[#272B30] hover:bg-gray-200 dark:hover:bg-[#272B30]/80 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="bulkDelete"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-red-500 hover:bg-red-600 transition-colors">
                        Delete All
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
