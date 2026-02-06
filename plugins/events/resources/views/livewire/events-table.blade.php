<div>
    <!-- Filters & Search -->
    <div class="space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left: Search & Filters -->
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative group w-full sm:w-auto">
                    <input
                        wire:model.live.debounce.300ms="search"
                        class="h-12 w-full sm:w-[320px] rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"
                        placeholder="Search events by title..." type="text" />
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

                <!-- Category Filter -->
                <select
                    wire:model.live="categoryFilter"
                    class="h-12 w-full sm:w-auto rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-4 pr-10 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <!-- Event Type Filter -->
                <select
                    wire:model.live="typeFilter"
                    class="h-12 w-full sm:w-auto rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-4 pr-10 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer">
                    <option value="">All Types</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="hybrid">Hybrid</option>
                </select>

                <!-- Time Filter -->
                <select
                    wire:model.live="timeFilter"
                    class="h-12 w-full sm:w-auto rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-4 pr-10 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer">
                    <option value="">All Time</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="past">Past</option>
                </select>

                @if($search || $statusFilter || $categoryFilter || $typeFilter || $timeFilter)
                <button
                    wire:click="clearFilters"
                    class="h-12 px-4 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-medium text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">close</span>
                    Clear
                </button>
                @endif
            </div>

            <!-- Right: Display & Add New -->
            <div class="flex flex-wrap items-center gap-3">
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
                
                @can('events.create')
                <div class="flex gap-2">
                    <a href="{{ route('admin.events.migration.wordpress') }}" wire:navigate
                        class="flex items-center justify-center rounded-xl bg-purple-500 px-6 py-3 text-sm font-bold text-white hover:bg-purple-600 transition-all shadow-lg shadow-purple-500/20 h-12 whitespace-nowrap">
                        <span class="material-symbols-outlined mr-2">cloud_download</span>
                        Import from WP
                    </a>
                    <a href="{{ route('admin.events.create') }}" wire:navigate
                        class="flex items-center justify-center rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 h-12 whitespace-nowrap">
                        Add Event
                    </a>
                </div>
                @endcan
            </div>
        </div>

        <!-- Row 2: Status Filter Buttons -->
        <div class="mb-4">
             <div class="inline-flex w-fit items-center bg-gray-100/50 dark:bg-[#0B0B0B]/30 p-1 rounded-2xl ring-1 ring-gray-200 dark:ring-[#272B30]">
                @php
                    $statuses = [
                        '' => ['label' => 'All', 'count' => $statusCounts['all']],
                        'published' => ['label' => 'Published', 'count' => $statusCounts['published']],
                        'draft' => ['label' => 'Draft', 'count' => $statusCounts['draft']],
                        'cancelled' => ['label' => 'Cancelled', 'count' => $statusCounts['cancelled']],
                        'completed' => ['label' => 'Completed', 'count' => $statusCounts['completed']],
                        'trashed' => ['label' => 'Trash', 'count' => $statusCounts['trashed']],
                    ];
                @endphp

                @foreach($statuses as $value => $data)
                    <button
                        wire:click="$set('statusFilter', '{{ $value }}')"
                        class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $statusFilter === $value ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                        {{ $data['label'] }}
                        <span class="px-2 py-0.5 rounded-lg {{ $statusFilter === $value ? 'bg-blue-50 dark:bg-blue-900/20 text-[#2563EB]' : 'bg-gray-200/50 dark:bg-[#272B30] text-[#6F767E]' }} text-[10px] font-bold">
                            {{ $data['count'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Events Table -->
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
                            <input
                                wire:model.live="selectAll"
                                class="custom-checkbox"
                                type="checkbox" />
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
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Category</th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Type</th>
                        <th class="px-4 py-6">
                            <button wire:click="sortBy('start_date')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Start Date
                                @if($sortField === 'start_date')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-6">
                            <button wire:click="sortBy('registered_count')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Registrations
                                @if($sortField === 'registered_count')
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Status</th>
                        <th class="px-8 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none">
                    @forelse($events as $event)
                    @php
                        $statusClasses = [
                            'published' => 'bg-[#3F8C5826] text-[#83BF6E]',
                            'draft' => 'bg-gray-100 dark:bg-[#272B30] text-[#6F767E]',
                            'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                            'completed' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                        ];
                        
                        $typeClasses = [
                            'online' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
                            'offline' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                            'hybrid' => 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors" wire:key="event-{{ $event->id }}">
                        <td class="px-8 py-5">
                            <input
                                wire:model.live="selectedEvents"
                                value="{{ $event->id }}"
                                class="custom-checkbox"
                                type="checkbox" />
                        </td>
                        <td class="px-4 py-5">
                            <div class="flex items-center gap-4">
                                @if($event->featuredImage)
                                <img src="{{ $event->featuredImage->url }}" alt="" class="h-12 w-16 rounded-xl object-cover">
                                @else
                                <div class="h-12 w-16 rounded-xl bg-gray-100 dark:bg-[#272B30] flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[#6F767E]">event</span>
                                </div>
                                @endif
                                <div>
                                    <p class="text-[15px] font-bold text-[#111827] dark:text-[#FCFCFC] line-clamp-1">{{ $event->title }}</p>
                                    <p class="text-xs text-[#6F767E] line-clamp-1">{{ Str::limit($event->description, 50) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-5">
                            @if($event->category)
                                <span class="inline-flex items-center rounded-lg bg-gray-100 dark:bg-[#272B30] px-2 py-1 text-[10px] font-bold text-[#6F767E] dark:text-[#FCFCFC] uppercase tracking-wider">{{ $event->category->name }}</span>
                            @else
                                <span class="text-xs text-[#6F767E]">No category</span>
                            @endif
                        </td>
                        <td class="px-4 py-5">
                            <span class="inline-flex items-center gap-1.5 rounded-lg {{ $typeClasses[$event->event_type] ?? $typeClasses['offline'] }} px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">
                                @if($event->event_type === 'online')
                                    <span class="material-symbols-outlined text-sm">videocam</span>
                                @elseif($event->event_type === 'offline')
                                    <span class="material-symbols-outlined text-sm">location_on</span>
                                @else
                                    <span class="material-symbols-outlined text-sm">hub</span>
                                @endif
                                {{ ucfirst($event->event_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-5">
                            <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $event->start_date->format('M d, Y') }}</p>
                            <p class="text-xs text-[#6F767E]">{{ $event->start_date->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-5">
                            @if($event->requires_registration)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $event->registered_count }}</span>
                                    @if($event->max_participants)
                                        <span class="text-xs text-[#6F767E]">/ {{ $event->max_participants }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-[#6F767E]">No registration</span>
                            @endif
                        </td>
                        <td class="px-4 py-5">
                            <span class="inline-flex items-center gap-1.5 rounded-lg {{ $statusClasses[$event->status] ?? $statusClasses['draft'] }} px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex gap-2 items-center justify-end">
                                @if($statusFilter === 'trashed')
                                    @can('events.delete')
                                    <button 
                                        wire:click="restore({{ $event->id }})"
                                        class="relative group/restore w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors"
                                        title="Restore">
                                        <span class="material-symbols-outlined text-[20px]">restore_from_trash</span>
                                    </button>
                                    
                                    <button 
                                        x-data
                                        @click="$dispatch('open-force-delete-modal', { eventId: {{ $event->id }}, eventTitle: '{{ addslashes($event->title) }}' })"
                                        class="relative group/delete w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#FF6A55] transition-colors"
                                        title="Delete Permanently">
                                        <span class="material-symbols-outlined text-[20px]">delete_forever</span>
                                    </button>
                                    @endcan
                                @else
                                    @can('events.view')
                                    <a href="{{ route('events.show', $event->slug) }}" target="_blank"
                                        class="relative group/view w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    @endcan
                                    
                                    @can('events.edit')
                                    <a href="{{ route('admin.events.edit', $event->id) }}" wire:navigate
                                        class="relative group/edit w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    @endcan
                                    
                                    @can('events.delete')
                                    <button 
                                        x-data
                                        @click="$dispatch('open-delete-modal', { eventId: {{ $event->id }}, eventTitle: '{{ addslashes($event->title) }}' })"
                                        class="relative group/delete w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#FF6A55] transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-16 w-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-[#6F767E]">event</span>
                                </div>
                                <p class="text-[#6F767E] font-medium">
                                    @if($search || $statusFilter || $categoryFilter || $typeFilter || $timeFilter)
                                        No events found matching your criteria
                                    @else
                                        No events yet. Create your first event!
                                    @endif
                                </p>
                                @if($search || $statusFilter || $categoryFilter || $typeFilter || $timeFilter)
                                <button wire:click="clearFilters" class="mt-3 text-sm text-[#2563EB] hover:underline">Clear filters</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($events->hasPages())
        <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30] flex items-center justify-between">
            <p class="text-sm font-medium text-[#6F767E]">
                Showing {{ $events->firstItem() }} to {{ $events->lastItem() }} of {{ $events->total() }} events
            </p>
            <div class="flex items-center gap-2">
                @if($events->onFirstPage())
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

                @foreach($events->getUrlRange(max(1, $events->currentPage() - 2), min($events->lastPage(), $events->currentPage() + 2)) as $page => $url)
                    @if($page == $events->currentPage())
                    <button class="h-10 w-10 rounded-xl bg-[#2563EB] text-white flex items-center justify-center text-sm font-bold shadow-lg shadow-blue-500/20">{{ $page }}</button>
                    @else
                    <button wire:click="gotoPage({{ $page }})" class="h-10 w-10 rounded-xl bg-white dark:bg-[#1A1A1A] flex items-center justify-center text-sm font-bold text-[#6F767E] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if($events->hasMorePages())
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
    @if(count($selectedEvents) > 0)
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">
        <div class="bg-[#2563EB] dark:bg-[#1A1A1A] border border-[#2563EB] dark:border-[#272B30] rounded-2xl shadow-2xl px-6 py-3 flex items-center gap-6">
            <div class="flex items-center gap-3 border-r border-blue-400/30 dark:border-[#272B30] pr-6">
                <span class="bg-white text-[#2563EB] text-xs font-bold px-2.5 py-1 rounded-full min-w-[24px] text-center">{{ count($selectedEvents) }}</span>
                <span class="text-sm font-semibold text-white">Selected</span>
            </div>
            <div class="flex items-center gap-4">
                @if($statusFilter === 'trashed')
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
                    @click="$dispatch('open-cancel-modal')"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                    Cancel
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
        x-data="{ show: false, eventId: null, eventTitle: '', bulk: false }"
        @open-delete-modal.window="show = true; eventId = $event.detail.eventId; eventTitle = $event.detail.eventTitle; bulk = false"
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
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC] mb-3">Delete Event</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed mb-8">
                    <template x-if="bulk">
                        <span>Are you sure you want to delete <span class="font-bold">{{ count($selectedEvents) }}</span> selected event(s)?</span>
                    </template>
                    <template x-if="!bulk">
                        <span>Are you sure you want to delete "<span class="font-bold" x-text="eventTitle"></span>"?</span>
                    </template>
                </p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="show = false"
                        class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all">
                        Cancel
                    </button>
                    <button 
                        @click="if (bulk) { $wire.deleteSelected(); } else { $wire.deleteEvent(eventId); } show = false;"
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
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC] mb-3">Publish Events</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed mb-8">
                    Are you sure you want to publish <span class="font-bold">{{ count($selectedEvents) }}</span> selected event(s)?
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
                    Are you sure you want to move <span class="font-bold">{{ count($selectedEvents) }}</span> selected event(s) to draft?
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

    <!-- Cancel Modal -->
    <div 
        x-data="{ show: false }"
        @open-cancel-modal.window="show = true"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div 
            @click.outside="show = false"
            x-show="show"
            x-transition
            class="w-full max-w-[440px] bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#272B30] rounded-3xl shadow-2xl p-8">
            <div class="flex flex-col items-center text-center">
                <div class="h-16 w-16 rounded-full bg-orange-500/10 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-orange-500 text-3xl">cancel</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC] mb-3">Cancel Events</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed mb-8">
                    Are you sure you want to cancel <span class="font-bold">{{ count($selectedEvents) }}</span> selected event(s)?
                </p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="show = false"
                        class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all">
                        No, Keep Them
                    </button>
                    <button 
                        @click="$wire.cancelSelected(); show = false;"
                        class="flex-1 h-12 rounded-xl bg-orange-500 text-white text-sm font-bold hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/20">
                        Yes, Cancel Events
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Force Delete Modal -->
    <div 
        x-data="{ show: false, eventId: null, eventTitle: '', bulk: false }"
        @open-force-delete-modal.window="show = true; eventId = $event.detail.eventId; eventTitle = $event.detail.eventTitle; bulk = false"
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
                        <span>Are you sure you want to PERMANENTLY delete <span class="font-bold">{{ count($selectedEvents) }}</span> selected event(s)? This action cannot be undone.</span>
                    </template>
                    <template x-if="!bulk">
                        <span>Are you sure you want to PERMANENTLY delete "<span class="font-bold" x-text="eventTitle"></span>"? This action cannot be undone.</span>
                    </template>
                </p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="show = false"
                        class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all">
                        Cancel
                    </button>
                    <button 
                        @click="if (bulk) { $wire.forceDeleteSelected(); } else { $wire.forceDelete(eventId); } show = false;"
                        class="flex-1 h-12 rounded-xl bg-[#FF6A55] text-white text-sm font-bold hover:bg-[#E55F4D] transition-all shadow-lg shadow-[#FF6A55]/20">
                        Delete Permanently
                    </button>
                </div>
            </div>
    </div>
</div>

