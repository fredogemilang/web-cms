<div class="space-y-4 text-slate-800 dark:text-[#FCFCFC]">

    {{-- ── Filters & Search Row 1 ────────────────────────────────────────── --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        {{-- Left: Search & Date Filters --}}
        <div class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative group w-full sm:w-auto">
                <input
                    wire:model.live.debounce.300ms="search"
                    class="h-12 w-full sm:w-[320px] rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"
                    placeholder="Search name, email, company or title..." type="text" />
                <span
                    class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#6F767E] group-focus-within:text-[#2563EB] transition-colors">search</span>
            </div>

            <!-- Date From -->
            <input wire:model.live="dateFrom" type="date"
                class="h-12 rounded-xl border-none bg-white dark:bg-[#1A1A1A] px-4 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer">
            
            <span class="text-xs text-[#6F767E] uppercase tracking-widest font-bold px-1">to</span>
            
            <!-- Date To -->
            <input wire:model.live="dateTo" type="date"
                class="h-12 rounded-xl border-none bg-white dark:bg-[#1A1A1A] px-4 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer">

            @if($search || $dateFrom || $dateTo)
            <button
                wire:click="clearFilters"
                class="h-12 px-4 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-medium text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">close</span>
                Clear
            </button>
            @endif
        </div>

        {{-- Right: Rows Display & Export --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-[#6F767E]">Display:</span>
                <select 
                    wire:model.live="perPage"
                    class="h-12 rounded-xl border-none bg-white dark:bg-[#1A1A1A] pl-4 pr-10 text-sm font-bold text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all cursor-pointer"
                >
                    <option value="25">25 Rows</option>
                    <option value="50">50 Rows</option>
                    <option value="100">100 Rows</option>
                </select>
            </div>
            
            <button wire:click="exportExcel" type="button"
                class="flex items-center justify-center rounded-xl bg-[#2563EB] hover:bg-blue-700 px-6 py-3 text-sm font-bold text-white transition-all shadow-lg shadow-blue-500/20 h-12 whitespace-nowrap cursor-pointer">
                <span class="material-symbols-outlined mr-2">download</span>
                Export Excel
            </button>
        </div>
    </div>

    {{-- ── Status Tab Pill Bar Row 2 ──────────────────────────────────────── --}}
    <div class="mb-4">
         <div class="inline-flex w-fit items-center bg-gray-100/50 dark:bg-[#0B0B0B]/30 p-1 rounded-2xl ring-1 ring-gray-200 dark:ring-[#272B30]">
            @php
                $statuses = [
                    'all'      => ['label' => 'All',      'count' => $guestCounts['all']],
                    'pending'  => ['label' => 'Pending',  'count' => $guestCounts['pending']],
                    'approved' => ['label' => 'Approved', 'count' => $guestCounts['approved']],
                    'rejected' => ['label' => 'Rejected', 'count' => $guestCounts['rejected']],
                ];
            @endphp

            @foreach($statuses as $value => $data)
                <button
                    wire:click="$set('activeTab', '{{ $value }}')"
                    class="h-10 px-4 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $activeTab === $value ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-gray-200 dark:ring-[#272B30]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                    {{ $data['label'] }}
                    <span class="px-2 py-0.5 rounded-lg {{ $activeTab === $value ? 'bg-blue-50 dark:bg-blue-900/20 text-[#2563EB]' : 'bg-gray-200/50 dark:bg-[#272B30] text-[#6F767E]' }} text-[10px] font-bold">
                        {{ $data['count'] }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Guests Table Card ──────────────────────────────────────────────── --}}
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
                            <button wire:click="sortBy('full_name')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Name
                                @if($sortField === 'full_name') 
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> 
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-6">
                            <button wire:click="sortBy('email')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Contact
                                @if($sortField === 'email') 
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> 
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Company</th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Status</th>
                        <th class="px-4 py-6">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest hover:text-[#2563EB] transition-colors">
                                Registered
                                @if($sortField === 'created_at') 
                                    <span class="material-symbols-outlined text-base">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> 
                                @else
                                    <span class="material-symbols-outlined text-base opacity-30">unfold_more</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Verified By</th>
                        <th class="px-8 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none">
                    @forelse($registrations as $reg)
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors" wire:key="guest-{{ $reg->id }}">
                        {{-- Checkbox --}}
                        <td class="px-8 py-5">
                            <input
                                wire:model.live="selectedItems"
                                value="{{ $reg->id }}"
                                class="custom-checkbox"
                                type="checkbox" />
                        </td>

                        {{-- Name & Job Title --}}
                        <td class="px-4 py-5">
                            <div class="font-bold text-[#111827] dark:text-[#FCFCFC] text-[15px]">{{ $reg->full_name ?? $reg->name }}</div>
                            <div class="text-xs text-[#6F767E] mt-0.5">{{ $reg->job_title }}</div>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @if($reg->walk_in)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-wider bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">Walk-in</span>
                                @endif
                                @if($reg->check_in)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-wider bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">Checked In</span>
                                @endif
                            </div>
                        </td>

                        {{-- Contact Details --}}
                        <td class="px-4 py-5">
                            <div class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $reg->email }}</div>
                            @if($reg->mobile_phone ?? $reg->phone)
                                <div class="text-xs text-[#6F767E] mt-0.5">{{ $reg->mobile_phone ?? $reg->phone }}</div>
                            @endif
                        </td>

                        {{-- Company Details --}}
                        <td class="px-4 py-5">
                            <div class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $reg->company_name ?? $reg->organization }}</div>
                            @if($reg->company_type)
                                <div class="text-xs text-[#6F767E] mt-0.5">{{ $reg->company_type }}</div>
                            @endif
                        </td>

                        {{-- Status Pill --}}
                        <td class="px-4 py-5">
                            @php
                                $statusClasses = [
                                    'pending'   => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                                    'confirmed' => 'bg-[#3F8C5826] text-[#83BF6E]',
                                    'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                                    'attended'  => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                                ];
                                $statusLabels = [
                                    'pending'   => 'Pending',
                                    'confirmed' => 'Approved',
                                    'cancelled' => 'Rejected',
                                    'attended'  => 'Attended',
                                ];
                                $cls = $statusClasses[$reg->status] ?? 'bg-gray-100 text-[#6F767E]';
                                $displayStatus = $statusLabels[$reg->status] ?? ucfirst($reg->status);
                            @endphp
                            
                            <button @click="$dispatch('open-change-status-modal', { registrationId: {{ $reg->id }}, currentStatus: '{{ $reg->status }}' })"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider {{ $cls }} cursor-pointer transition-all hover:scale-[1.02]">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $displayStatus }}
                            </button>
                            
                            @if($reg->verified_type)
                                <div class="text-[10px] text-[#6F767E] font-semibold uppercase tracking-wider mt-1.5">{{ $reg->verified_type }}</div>
                            @endif
                        </td>

                        {{-- Registered date --}}
                        <td class="px-4 py-5 text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">
                            <div>{{ $reg->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-[#6F767E] mt-0.5">{{ $reg->created_at->format('H:i') }}</div>
                        </td>

                        {{-- Verified by --}}
                        <td class="px-4 py-5 text-xs text-[#6F767E]">
                            @if($reg->verifiedBy)
                                <div class="font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $reg->verifiedBy->name }}</div>
                                <div class="text-[10px] mt-0.5 text-[#6F767E]">{{ $reg->verified_at?->format('M d, Y H:i') }}</div>
                                @if($reg->verified_note)
                                    <div class="italic mt-1 px-2 py-1 rounded bg-[#0B0B0B]/20 text-[10px] border border-[#272B30] truncate max-w-[140px]" title="{{ $reg->verified_note }}">{{ $reg->verified_note }}</div>
                                @endif
                            @else
                                <span class="text-[#6F767E]">—</span>
                            @endif
                        </td>

                        {{-- Row Action Buttons --}}
                        <td class="px-8 py-5 text-right">
                            <div class="flex gap-2 items-center justify-end">
                                <button wire:click="editGuest({{ $reg->id }})" type="button"
                                    class="relative group/edit w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors cursor-pointer"
                                    title="Edit Guest">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button wire:click="deleteGuest({{ $reg->id }})" wire:confirm="Are you sure you want to delete this attendee?" type="button"
                                    class="relative group/delete w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#FF6A55] transition-colors cursor-pointer"
                                    title="Delete Guest">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-16 w-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-[#6F767E]">group</span>
                                </div>
                                <p class="text-[#6F767E] font-medium">
                                    @if($search || $activeTab !== 'all')
                                        No guests found matching your criteria
                                    @else
                                        No guests registered yet for this event
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Custom Segmented Page Controls ───────────────────────────────── --}}
        @if($registrations->hasPages())
        <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30] flex items-center justify-between">
            <p class="text-sm font-medium text-[#6F767E]">
                Showing {{ $registrations->firstItem() }} to {{ $registrations->lastItem() }} of {{ $registrations->total() }} guests
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

    {{-- ── Bottom Floating Bulk Action Bar ───────────────────────────────── --}}
    @if(count($selectedItems) > 0)
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">
        <div class="bg-[#2563EB] dark:bg-[#1A1A1A] border border-[#2563EB] dark:border-[#272B30] rounded-2xl shadow-2xl px-6 py-3 flex items-center gap-6">
            <div class="flex items-center gap-3 border-r border-blue-400/30 dark:border-[#272B30] pr-6">
                <span class="bg-white text-[#2563EB] text-xs font-bold px-2.5 py-1 rounded-full min-w-[24px] text-center">{{ count($selectedItems) }}</span>
                <span class="text-sm font-semibold text-white">Selected</span>
            </div>
            <div class="flex items-center gap-4">
                <button 
                    x-data
                    @click="$dispatch('open-bulk-approve-modal')"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    Approve
                </button>
                <button 
                    x-data
                    @click="$dispatch('open-bulk-reject-modal')"
                    class="flex items-center gap-2 text-sm font-bold text-white/70 hover:text-[#FF6A55] transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                    Reject
                </button>
                <button 
                    x-data
                    @click="$dispatch('open-bulk-delete-modal')"
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

    {{-- ── Single Change Status Modal ─────────────────────────────────────── --}}
    <div
        x-data="changeStatusModal()"
        x-show="open"
        x-cloak
        @open-change-status-modal.window="openWith($event.detail.registrationId, $event.detail.currentStatus)"
        class="fixed inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">

        <div @click.away="open = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="bg-white dark:bg-[#1A1A1A] border border-slate-100 dark:border-[#272B30] rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-[#FCFCFC]">Update Attendee Status</h3>
                    <p class="text-xs text-slate-400 dark:text-[#6F767E] mt-0.5">Select a new status and specify reasons if required.</p>
                </div>
                <button @click="open = false" class="p-1.5 hover:bg-slate-100 dark:hover:bg-[#272B30] rounded-xl transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-slate-500 dark:text-[#6F767E]">close</span>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-2.5">
                    <!-- Pending Option -->
                    <button type="button" @click="selectedStatus = 'pending'"
                        :class="selectedStatus === 'pending'
                            ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 ring-2 ring-amber-500/20 font-bold'
                            : 'border-slate-200 dark:border-[#272B30] text-slate-500 dark:text-[#6F767E] hover:bg-slate-50 dark:hover:bg-[#272B30]'"
                        class="flex flex-col items-center justify-center p-3.5 rounded-2xl border text-center transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-xl mb-1.5">hourglass_empty</span>
                        <span class="text-xs">Pending</span>
                    </button>

                    <!-- Approve Option -->
                    <button type="button" @click="selectedStatus = 'confirmed'"
                        :class="selectedStatus === 'confirmed'
                            ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 ring-2 ring-emerald-500/20 font-bold'
                            : 'border-slate-200 dark:border-[#272B30] text-slate-500 dark:text-[#6F767E] hover:bg-slate-50 dark:hover:bg-[#272B30]'"
                        class="flex flex-col items-center justify-center p-3.5 rounded-2xl border text-center transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-xl mb-1.5">check_circle</span>
                        <span class="text-xs">Approve</span>
                    </button>

                    <!-- Reject Option -->
                    <button type="button" @click="selectedStatus = 'cancelled'"
                        :class="selectedStatus === 'cancelled'
                            ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 ring-2 ring-rose-500/20 font-bold'
                            : 'border-slate-200 dark:border-[#272B30] text-slate-500 dark:text-[#6F767E] hover:bg-slate-50 dark:hover:bg-[#272B30]'"
                        class="flex flex-col items-center justify-center p-3.5 rounded-2xl border text-center transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-xl mb-1.5">cancel</span>
                        <span class="text-xs">Reject</span>
                    </button>
                </div>

                {{-- Approval Type Options --}}
                <div x-show="selectedStatus === 'confirmed'" class="space-y-4 pt-2 border-t border-slate-100 dark:border-[#272B30]" x-cloak>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1.5">Approval Type <span class="text-rose-500">*</span></label>
                        <select x-model="approvalTypeId"
                            class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            <option value="">— Select approval type —</option>
                            @foreach($approvalTypes['approved'] ?? [] as $type)
                                <option value="{{ $type['id'] }}">{{ $type['type_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1.5">Note (optional)</label>
                        <textarea x-model="note" rows="2"
                            class="w-full rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 py-2.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none transition-all"
                            placeholder="Add reference notes..."></textarea>
                    </div>
                </div>

                {{-- Reject Reason Options --}}
                <div x-show="selectedStatus === 'cancelled'" class="space-y-4 pt-2 border-t border-slate-100 dark:border-[#272B30]" x-cloak>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1.5">Rejection Reason <span class="text-rose-500">*</span></label>
                        <select x-model="approvalTypeId"
                            class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all">
                            <option value="">— Select rejection reason —</option>
                            @foreach($approvalTypes['rejected'] ?? [] as $type)
                                <option value="{{ $type['id'] }}">{{ $type['type_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1.5">Note (optional)</label>
                        <textarea x-model="note" rows="2"
                            class="w-full rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 py-2.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none transition-all"
                            placeholder="Add reference notes..."></textarea>
                    </div>
                </div>

                {{-- Warning Alert --}}
                <template x-if="selectedStatus !== currentStatus && (currentStatus === 'confirmed' || currentStatus === 'cancelled')">
                    <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-100/50 dark:border-amber-900/30 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-base mt-0.5 select-none">warning</span>
                        <div class="leading-relaxed">
                            <strong>Note:</strong> Since this attendee has already been processed (Approved/Rejected), switching their status may trigger another email notification and generate double confirmations.
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-[#272B30]">
                <button @click="open = false" type="button"
                    class="px-4.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-500 hover:bg-slate-50 dark:text-[#6F767E] dark:hover:bg-[#272B30] transition-all cursor-pointer">
                    Cancel
                </button>
                <button @click="submit" type="button"
                    class="px-5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-[#111827] dark:hover:bg-[#1F1F1F] shadow-md hover:shadow-lg transition-all cursor-pointer">
                    Save Status
                </button>
            </div>
        </div>
    </div>

    {{-- ── Bulk Approve Modal ──────────────────────────────────────────────── --}}
    <div 
        x-data="{ show: false, approvalTypeId: '', note: '' }"
        @open-bulk-approve-modal.window="show = true; approvalTypeId = ''; note = '';"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div 
            @click.outside="show = false"
            x-show="show"
            x-transition
            class="w-full max-w-[440px] bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#272B30] rounded-3xl shadow-2xl p-8 space-y-6">
            
            <div class="flex flex-col items-center text-center">
                <div class="h-16 w-16 rounded-full bg-[#83BF6E]/10 flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[#83BF6E] text-3xl">check_circle</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC]">Bulk Approve</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed">
                    Are you sure you want to approve <span class="font-bold">{{ count($selectedItems) }}</span> selected attendee(s)?
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1.5">Approval Type <span class="text-rose-500">*</span></label>
                    <select x-model="approvalTypeId"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        <option value="">— Select approval type —</option>
                        @foreach($approvalTypes['approved'] ?? [] as $type)
                            <option value="{{ $type['id'] }}">{{ $type['type_name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1.5">Note (optional)</label>
                    <textarea x-model="note" rows="2"
                        class="w-full rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 py-2.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none transition-all"
                        placeholder="Add reference notes..."></textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full pt-2">
                <button @click="show = false"
                    class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all cursor-pointer">
                    Cancel
                </button>
                <button 
                    @click="if (!approvalTypeId) { alert('Please select approval type'); return; } $wire.bulkUpdateStatus('confirmed', parseInt(approvalTypeId), note); show = false;"
                    class="flex-1 h-12 rounded-xl bg-[#83BF6E] text-white text-sm font-bold hover:bg-[#6fa85a] transition-all shadow-lg shadow-[#83BF6E]/20 cursor-pointer">
                    Approve
                </button>
            </div>
        </div>
    </div>

    {{-- ── Bulk Reject Modal ──────────────────────────────────────────────── ── --}}
    <div 
        x-data="{ show: false, approvalTypeId: '', note: '' }"
        @open-bulk-reject-modal.window="show = true; approvalTypeId = ''; note = '';"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div 
            @click.outside="show = false"
            x-show="show"
            x-transition
            class="w-full max-w-[440px] bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#272B30] rounded-3xl shadow-2xl p-8 space-y-6">
            
            <div class="flex flex-col items-center text-center">
                <div class="h-16 w-16 rounded-full bg-[#FF6A55]/10 flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[#FF6A55] text-3xl">cancel</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC]">Bulk Reject</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed">
                    Are you sure you want to reject <span class="font-bold">{{ count($selectedItems) }}</span> selected attendee(s)?
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1.5">Rejection Reason <span class="text-rose-500">*</span></label>
                    <select x-model="approvalTypeId"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all">
                        <option value="">— Select rejection reason —</option>
                        @foreach($approvalTypes['rejected'] ?? [] as $type)
                            <option value="{{ $type['id'] }}">{{ $type['type_name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1.5">Note (optional)</label>
                    <textarea x-model="note" rows="2"
                        class="w-full rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 py-2.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none transition-all"
                        placeholder="Add reference notes..."></textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full pt-2">
                <button @click="show = false"
                    class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all cursor-pointer">
                    Cancel
                </button>
                <button 
                    @click="if (!approvalTypeId) { alert('Please select rejection reason'); return; } $wire.bulkUpdateStatus('cancelled', parseInt(approvalTypeId), note); show = false;"
                    class="flex-1 h-12 rounded-xl bg-[#FF6A55] text-white text-sm font-bold hover:bg-[#E55F4D] transition-all shadow-lg shadow-[#FF6A55]/20 cursor-pointer">
                    Reject
                </button>
            </div>
        </div>
    </div>

    {{-- ── Bulk Delete Modal ──────────────────────────────────────────────── ── --}}
    <div 
        x-data="{ show: false }"
        @open-bulk-delete-modal.window="show = true"
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
                <h3 class="text-2xl font-bold text-gray-900 dark:text-[#FCFCFC] mb-3">Delete Attendees</h3>
                <p class="text-gray-500 dark:text-[#6F767E] leading-relaxed mb-8">
                    Are you sure you want to delete <span class="font-bold">{{ count($selectedItems) }}</span> selected attendee(s)? This action cannot be undone.
                </p>
                <div class="flex items-center gap-3 w-full">
                    <button @click="show = false"
                        class="flex-1 h-12 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button 
                        @click="$wire.deleteSelected(); show = false;"
                        class="flex-1 h-12 rounded-xl bg-[#FF6A55] text-white text-sm font-bold hover:bg-[#E55F4D] transition-all shadow-lg shadow-[#FF6A55]/20 cursor-pointer">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Edit Modal ─────────────────────────────────────────────────────── --}}
    <div
        x-data="{ open: @entangle('showEditModal') }"
        x-show="open"
        x-cloak
        class="fixed inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">

        <div @click.away="$wire.closeEditModal()"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="bg-white dark:bg-[#1A1A1A] border border-slate-100 dark:border-[#272B30] rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-5">

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-[#FCFCFC]">Edit Attendee Details</h3>
                    <p class="text-xs text-slate-400 dark:text-[#6F767E] mt-0.5">Modify information fields for this attendee record.</p>
                </div>
                <button @click="$wire.closeEditModal()" class="p-1.5 hover:bg-slate-100 dark:hover:bg-[#272B30] rounded-xl transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-slate-500 dark:text-[#6F767E]">close</span>
                </button>
            </div>

            <div class="space-y-3.5 max-h-[60vh] overflow-y-auto pr-1">
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1">Full Name</label>
                    <input wire:model="editFullName" type="text"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1">Email</label>
                    <input wire:model="editEmail" type="email"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1">Phone</label>
                    <input wire:model="editPhone" type="text"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1">Company</label>
                    <input wire:model="editCompany" type="text"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1">Job Title</label>
                    <input wire:model="editJobTitle" type="text"
                        class="w-full h-10 rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-[#6F767E] uppercase tracking-widest mb-1">Notes</label>
                    <textarea wire:model="editNotes" rows="2"
                        class="w-full rounded-xl border border-slate-200 dark:border-[#272B30] bg-slate-50/50 dark:bg-[#0B0B0B] px-3.5 py-2.5 text-sm text-slate-800 dark:text-[#FCFCFC] focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-indigo-500 focus:border-transparent resize-none transition-all"
                        placeholder="Add special notes or requests..."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-[#272B30]">
                <button @click="$wire.closeEditModal()" type="button"
                    class="px-4.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-500 hover:bg-slate-50 dark:text-[#6F767E] dark:hover:bg-[#272B30] transition-all cursor-pointer">
                    Cancel
                </button>
                <button wire:click="saveGuest" type="button"
                    class="px-5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-[#111827] dark:hover:bg-zinc-100 shadow-md hover:shadow-lg transition-all cursor-pointer">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function changeStatusModal() {
    return {
        open: false,
        registrationId: null,
        currentStatus: '',
        selectedStatus: '',
        approvalTypeId: '',
        note: '',
        openWith(id, currentStatus) {
            this.registrationId = id;
            this.currentStatus = currentStatus;
            this.selectedStatus = currentStatus;
            this.approvalTypeId = '';
            this.note = '';
            this.open = true;
        },
        submit() {
            if (this.selectedStatus === 'confirmed' || this.selectedStatus === 'cancelled') {
                if (!this.approvalTypeId) {
                    alert('Please select a reason/approval type.');
                    return;
                }
            }
            
            // Dispatch a visual toast
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { type: 'info', message: 'Saving status...' } }));
            
            this.$wire.updateStatus(
                this.registrationId, 
                this.selectedStatus, 
                this.approvalTypeId ? parseInt(this.approvalTypeId) : null, 
                this.note
            ).then(() => {
                this.open = false;
            });
        }
    }
}
</script>
