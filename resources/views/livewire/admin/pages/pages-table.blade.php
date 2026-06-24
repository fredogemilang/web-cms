<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">Pages</h2>
            <p class="text-sm text-[#6F767E] mt-1">Manage your site pages</p>
        </div>
        <a href="{{ route('admin.pages.create') }}"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all h-12">
            <span class="material-symbols-outlined text-lg">add</span>
            Add New Page
        </a>
    </div>

    <!-- Row 2: Status Filter Buttons -->
    <div class="mb-4">
         <div class="inline-flex w-fit items-center bg-gray-100/50 dark:bg-[#0B0B0B]/30 p-1 rounded-2xl ring-1 ring-gray-200 dark:ring-[#272B30]">
            @php
                $statuses = [
                    '' => ['label' => 'All', 'count' => $statusCounts['all']],
                    'published' => ['label' => 'Published', 'count' => $statusCounts['published']],
                    'draft' => ['label' => 'Draft', 'count' => $statusCounts['draft']],
                    'scheduled' => ['label' => 'Scheduled', 'count' => $statusCounts['scheduled']],
                    'private' => ['label' => 'Private', 'count' => $statusCounts['private']],
                ];
            @endphp

            @foreach($statuses as $value => $data)
                <button
                    wire:click="setStatus('{{ $value }}')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $status === $value ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                    {{ $data['label'] }}
                    <span class="px-2 py-0.5 rounded-lg {{ $status === $value ? 'bg-blue-50 dark:bg-blue-900/20 text-[#2563EB]' : 'bg-gray-200/50 dark:bg-[#272B30] text-[#6F767E]' }} text-[10px] font-bold">
                        {{ $data['count'] }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="relative group">
                <input
                    wire:model.live.debounce.300ms="search"
                    class="h-12 w-full md:w-[320px] rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"
                    placeholder="Search pages by title..." type="text" />
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
            
            @if($search || $status)
            <button
                wire:click="setStatus('')"
                class="h-12 px-4 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-medium text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">close</span>
                Clear
            </button>
            @endif
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-[#6F767E]">Display:</span>
            <select
                wire:model.live="perPage"
                class="h-12 rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-4 pr-10 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer">
                <option value="10">10 Rows</option>
                <option value="20">20 Rows</option>
                <option value="50">50 Rows</option>
                <option value="100">100 Rows</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden relative">
        <!-- Loading Bar -->
        <div wire:loading.delay.shortest class="absolute top-0 left-0 right-0 h-1 z-20 overflow-hidden">
            <div class="h-full bg-[#2563EB] animate-indeterminate origin-left"></div>
        </div>

        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                        <th class="px-8 py-6 w-10">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="custom-checkbox" />
                        </th>
                        <th class="px-4 py-6">
                            <button wire:click="sortBy('title')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Title
                                @if($sortField === 'title')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Author</th>
                        <th class="px-4 py-6">
                            <button wire:click="sortBy('status')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Status
                                @if($sortField === 'status')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-6">
                            <button wire:click="sortBy('updated_at')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Last Modified
                                @if($sortField === 'updated_at')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-8 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none">
                    @forelse($pages as $page)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30]/30 transition-colors" wire:key="page-{{ $page->id }}">
                            <td class="px-8 py-5">
                                <input type="checkbox" wire:model.live="selectedPages" value="{{ $page->id }}"
                                    class="custom-checkbox" />
                            </td>
                            <td class="px-4 py-5">
                                <div>
                                    <a href="{{ route('admin.pages.edit', $page->id) }}"
                                        class="font-semibold text-[#111827] dark:text-[#FCFCFC] hover:text-[#2563EB] transition-colors">
                                        {{ $page->title }}
                                    </a>
                                    <div class="text-xs text-[#6F767E] flex items-center gap-1 mt-0.5">
                                        <span class="material-symbols-outlined text-xs">link</span>
                                        /{{ $page->slug }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-5">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-blue-500/10 flex items-center justify-center text-xs font-bold text-[#2563EB]">
                                        {{ substr($page->author?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $page->author?->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-5">
                                @switch($page->status)
                                    @case('published')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#3F8C5826] text-[#83BF6E] uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            Published
                                        </span>
                                        @break
                                    @case('draft')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-gray-100 dark:bg-[#272B30] text-[#6F767E] uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            Draft
                                        </span>
                                        @break
                                    @case('scheduled')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            Scheduled
                                        </span>
                                        @break
                                    @case('private')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-gray-100 dark:bg-gray-500/10 text-gray-700 dark:text-gray-400 uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            Private
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-4 py-5">
                                <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $page->updated_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.pages.edit', $page->id) }}"
                                        class="h-9 w-9 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] flex items-center justify-center transition-colors"
                                        title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button wire:click="duplicate({{ $page->id }})"
                                        class="h-9 w-9 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] flex items-center justify-center transition-colors"
                                        title="Duplicate">
                                        <span class="material-symbols-outlined text-lg">content_copy</span>
                                    </button>
                                    @if($page->status === 'published')
                                        <a href="{{ url($page->slug) }}" target="_blank"
                                            class="h-9 w-9 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] flex items-center justify-center transition-colors"
                                            title="View">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </a>
                                    @endif
                                    <button wire:click="confirmDelete({{ $page->id }})"
                                        class="h-9 w-9 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 text-[#6F767E] hover:text-[#FF6A55] flex items-center justify-center transition-colors"
                                        title="Delete">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="h-16 w-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                        <span class="material-symbols-outlined text-3xl text-[#6F767E]">description</span>
                                    </div>
                                    <p class="text-[#6F767E] font-medium">No pages found</p>
                                    <p class="text-sm text-[#6F767E] mt-1">Get started by creating your first page.</p>
                                    <a href="{{ route('admin.pages.create') }}"
                                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-[#2563EB] hover:underline">
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
        <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30] flex items-center justify-between">
            <p class="text-sm font-medium text-[#6F767E]">
                Showing {{ $pages->firstItem() }} to {{ $pages->lastItem() }} of {{ $pages->total() }} pages
            </p>
            <div class="flex items-center gap-2">
                @if($pages->onFirstPage())
                <button disabled
                    class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] opacity-50 cursor-not-allowed">
                    <span class="material-symbols-outlined text-xl">chevron_left</span>
                </button>
                @else
                <button wire:click="previousPage"
                    class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                    <span class="material-symbols-outlined text-xl">chevron_left</span>
                </button>
                @endif

                @foreach($pages->getUrlRange(max(1, $pages->currentPage() - 2), min($pages->lastPage(), $pages->currentPage() + 2)) as $page => $url)
                    @if($page == $pages->currentPage())
                    <button class="h-10 w-10 rounded-xl bg-[#2563EB] text-white flex items-center justify-center text-sm font-bold shadow-lg shadow-blue-500/20">{{ $page }}</button>
                    @else
                    <button wire:click="gotoPage({{ $page }})" class="h-10 w-10 rounded-xl bg-white dark:bg-[#1A1A1A] flex items-center justify-center text-sm font-bold text-[#6F767E] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if($pages->hasMorePages())
                <button wire:click="nextPage"
                    class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                    <span class="material-symbols-outlined text-xl">chevron_right</span>
                </button>
                @else
                <button disabled
                    class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] opacity-50 cursor-not-allowed">
                    <span class="material-symbols-outlined text-xl">chevron_right</span>
                </button>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Bulk Action Bar -->
    @if(count($selectedPages) > 0)
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">
        <div class="bg-[#2563EB] border border-[#2563EB] rounded-2xl shadow-2xl px-6 py-3 flex items-center gap-6">
            <div class="flex items-center gap-3 border-r border-blue-400/30 pr-6">
                <span class="bg-white text-[#2563EB] text-xs font-bold px-2.5 py-1 rounded-full min-w-[24px] text-center">{{ count($selectedPages) }}</span>
                <span class="text-sm font-semibold text-white">Selected</span>
            </div>
            <div class="flex items-center gap-4">
                <button wire:click="bulkPublish"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">publish</span>
                    Publish
                </button>
                <button wire:click="bulkDraft"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">edit_document</span>
                    Draft
                </button>
                <button wire:click="confirmBulkDelete"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-[#FF6A55] transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                    Delete
                </button>
            </div>
            <button wire:click="clearSelection" class="ml-2 w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 text-white/70 hover:text-white transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
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
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
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
