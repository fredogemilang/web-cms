<div>
    <!-- Filters & Search -->
    <div class="space-y-4 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left: Search & Filters -->
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative group w-full sm:w-auto">
                    <input
                        wire:model.live.debounce.300ms="search"
                        class="h-12 w-full sm:w-[320px] rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"
                        placeholder="Search by name or email..." type="text" />
                    <span
                        class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#6F767E] group-focus-within:text-[#2563EB] transition-colors">search</span>
                </div>

                <!-- Event Filter -->
                <select
                    wire:model.live="eventFilter"
                    class="h-12 w-full sm:w-auto rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-4 pr-10 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}">{{ $event->title }}</option>
                    @endforeach
                </select>

                @if($search || $eventFilter || $statusFilter)
                <button
                    wire:click="clearFilters"
                    class="h-12 px-4 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-medium text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">close</span>
                    Clear
                </button>
                @endif
            </div>

            <!-- Right: Display -->
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

        <!-- Status Filter Buttons -->
        <div>
             <div class="inline-flex w-fit items-center bg-gray-100/50 dark:bg-[#0B0B0B]/30 p-1 rounded-2xl ring-1 ring-gray-200 dark:ring-[#272B30]">
                @php
                    $statuses = [
                        '' => ['label' => 'All', 'count' => $statusCounts['all']],
                        'pending' => ['label' => 'Pending', 'count' => $statusCounts['pending']],
                        'confirmed' => ['label' => 'Confirmed', 'count' => $statusCounts['confirmed']],
                        'cancelled' => ['label' => 'Cancelled', 'count' => $statusCounts['cancelled']],
                        'attended' => ['label' => 'Attended', 'count' => $statusCounts['attended']],
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

    <!-- Registrations Table -->
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
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Participant</th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Event</th>
                        <th class="px-4 py-6">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Registration Date
                                @if($sortField === 'created_at')
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
                    @forelse($registrations as $registration)
                    @php
                        $statusClasses = [
                            'pending' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                            'confirmed' => 'bg-[#3F8C5826] text-[#83BF6E]',
                            'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                            'attended' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors" wire:key="registration-{{ $registration->id }}">
                        <td class="px-8 py-5">
                            <input
                                wire:model.live="selectedRegistrations"
                                value="{{ $registration->id }}"
                                class="custom-checkbox"
                                type="checkbox" />
                        </td>
                        <td class="px-4 py-5">
                            <div>
                                <p class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $registration->name }}</p>
                                <p class="text-xs text-[#6F767E]">{{ $registration->email }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-5">
                            <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $registration->event->title }}</p>
                            <p class="text-xs text-[#6F767E]">{{ $registration->event->start_date->format('M d, Y') }}</p>
                        </td>
                        <td class="px-4 py-5">
                            <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $registration->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-[#6F767E]">{{ $registration->created_at->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-5">
                            <span class="inline-flex items-center gap-1.5 rounded-lg {{ $statusClasses[$registration->status] ?? $statusClasses['pending'] }} px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ ucfirst($registration->status) }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex gap-2 items-center justify-end">
                                @if($registration->status === 'pending')
                                <button 
                                    wire:click="updateStatus({{ $registration->id }}, 'confirmed')"
                                    class="w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#83BF6E] transition-colors"
                                    title="Confirm">
                                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                </button>
                                @endif
                                
                                @if($registration->status === 'confirmed')
                                <button 
                                    wire:click="updateStatus({{ $registration->id }}, 'attended')"
                                    class="w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors"
                                    title="Mark as Attended">
                                    <span class="material-symbols-outlined text-[20px]">done_all</span>
                                </button>
                                @endif
                                
                                <button 
                                    wire:click="updateStatus({{ $registration->id }}, 'cancelled')"
                                    class="w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#FF6A55] transition-colors"
                                    title="Cancel">
                                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-16 w-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-[#6F767E]">people</span>
                                </div>
                                <p class="text-[#6F767E] font-medium">
                                    @if($search || $eventFilter || $statusFilter)
                                        No registrations found matching your criteria
                                    @else
                                        No registrations yet
                                    @endif
                                </p>
                                @if($search || $eventFilter || $statusFilter)
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
        @if($registrations->hasPages())
        <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30] flex items-center justify-between">
            <p class="text-sm font-medium text-[#6F767E]">
                Showing {{ $registrations->firstItem() }} to {{ $registrations->lastItem() }} of {{ $registrations->total() }} registrations
            </p>
            <div class="flex items-center gap-2">
                @if($registrations->onFirstPage())
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

                @foreach($registrations->getUrlRange(max(1, $registrations->currentPage() - 2), min($registrations->lastPage(), $registrations->currentPage() + 2)) as $page => $url)
                    @if($page == $registrations->currentPage())
                    <button class="h-10 w-10 rounded-xl bg-[#2563EB] text-white flex items-center justify-center text-sm font-bold shadow-lg shadow-blue-500/20">{{ $page }}</button>
                    @else
                    <button wire:click="gotoPage({{ $page }})" class="h-10 w-10 rounded-xl bg-white dark:bg-[#1A1A1A] flex items-center justify-center text-sm font-bold text-[#6F767E] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if($registrations->hasMorePages())
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
    @if(count($selectedRegistrations) > 0)
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">
        <div class="bg-[#2563EB] dark:bg-[#1A1A1A] border border-[#2563EB] dark:border-[#272B30] rounded-2xl shadow-2xl px-6 py-3 flex items-center gap-6">
            <div class="flex items-center gap-3 border-r border-blue-400/30 dark:border-[#272B30] pr-6">
                <span class="bg-white text-[#2563EB] text-xs font-bold px-2.5 py-1 rounded-full min-w-[24px] text-center">{{ count($selectedRegistrations) }}</span>
                <span class="text-sm font-semibold text-white">Selected</span>
            </div>
            <div class="flex items-center gap-4">
                <button 
                    wire:click="confirmSelected"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    Confirm
                </button>
                <button 
                    wire:click="cancelSelected"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-[#FF6A55] transition-colors">
                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                    Cancel
                </button>
                <button 
                    x-data
                    @click="if(confirm('Are you sure you want to delete selected registrations?')) $wire.deleteSelected()"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-[#FF6A55] transition-colors">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                    Delete
                </button>
            </div>
            <button wire:click="clearSelection" class="ml-2 w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 text-white/70 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    </div>
    @endif
</div>

