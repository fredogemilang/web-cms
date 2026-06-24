<div class="space-y-4">

    {{-- ── Tab Navigation ─────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-1 border-b border-gray-200 dark:border-[#272B30]">
        @foreach([
            'all'      => ['label' => 'All',      'count' => $guestCounts['all'],      'color' => 'text-[#111827] dark:text-[#FCFCFC]'],
            'pending'  => ['label' => 'Pending',   'count' => $guestCounts['pending'],  'color' => 'text-amber-600'],
            'approved' => ['label' => 'Approved',  'count' => $guestCounts['approved'], 'color' => 'text-green-600'],
            'rejected' => ['label' => 'Rejected',  'count' => $guestCounts['rejected'], 'color' => 'text-red-600'],
        ] as $tab => $cfg)
        <button wire:click="$set('activeTab', '{{ $tab }}')" type="button"
            class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold border-b-2 transition-all
                {{ $activeTab === $tab
                    ? 'border-[#2563EB] text-[#2563EB]'
                    : 'border-transparent '.$cfg['color'].' hover:text-[#2563EB]' }}">
            {{ $cfg['label'] }}
            <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold
                {{ $activeTab === $tab ? 'bg-[#2563EB] text-white' : 'bg-gray-100 dark:bg-[#272B30] text-[#6F767E]' }}">
                {{ $cfg['count'] }}
            </span>
        </button>
        @endforeach
    </div>

    {{-- ── Filters Row ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-[220px]">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-[#6F767E]">search</span>
            <input wire:model.live.debounce.300ms="search" type="search"
                placeholder="Search name, email, company…"
                class="w-full h-11 pl-10 pr-4 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition-all">
        </div>

        {{-- Date From --}}
        <input wire:model.live="dateFrom" type="date"
            class="h-11 px-3 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
        <span class="text-xs text-[#6F767E]">to</span>
        <input wire:model.live="dateTo" type="date"
            class="h-11 px-3 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">

        {{-- Clear Filters --}}
        @if($search || $dateFrom || $dateTo)
        <button wire:click="clearFilters" type="button"
            class="h-11 px-4 rounded-xl text-sm font-semibold text-[#6F767E] hover:text-red-500 border border-gray-200 dark:border-[#272B30] transition-all">
            <span class="material-symbols-outlined text-base align-middle">filter_alt_off</span>
        </button>
        @endif

        {{-- Per-page --}}
        <select wire:model.live="perPage"
            class="h-11 pl-4 pr-8 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-sm font-bold text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    {{-- ── Bulk Action Bar (shows when items selected) ─────────────────────── --}}
    @if(count($selectedItems) > 0)
    <div class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
        <span class="text-sm font-bold text-blue-700 dark:text-blue-300">
            {{ count($selectedItems) }} selected
        </span>
        <button wire:click="$dispatch('open-bulk-approve-modal')" type="button"
            class="px-4 py-1.5 rounded-lg text-sm font-bold text-white bg-green-600 hover:bg-green-700 transition-all">
            Approve All
        </button>
        <button wire:click="$dispatch('open-bulk-reject-modal')" type="button"
            class="px-4 py-1.5 rounded-lg text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
            Reject All
        </button>
        <button wire:click="deleteSelected" wire:confirm="Are you sure you want to delete the selected attendees?" type="button"
            class="px-4 py-1.5 rounded-lg text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
            Delete Selected
        </button>
        <button wire:click="clearSelection" type="button"
            class="ml-auto text-sm text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all">
            Clear
        </button>
    </div>
    @endif

    {{-- ── Table ───────────────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A]">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B]">
                    <th class="w-10 px-4 py-3 text-left">
                        <input wire:model.live="selectAll" type="checkbox"
                            class="rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                    </th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('full_name')" class="flex items-center gap-1 text-xs font-bold text-[#6F767E] uppercase tracking-wide hover:text-[#2563EB] transition-colors">
                            Name
                            @if($sortField === 'full_name') <span class="material-symbols-outlined text-xs">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('email')" class="flex items-center gap-1 text-xs font-bold text-[#6F767E] uppercase tracking-wide hover:text-[#2563EB] transition-colors">
                            Email
                            @if($sortField === 'email') <span class="material-symbols-outlined text-xs">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-[#6F767E] uppercase tracking-wide">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-[#6F767E] uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('created_at')" class="flex items-center gap-1 text-xs font-bold text-[#6F767E] uppercase tracking-wide hover:text-[#2563EB] transition-colors">
                            Registered
                            @if($sortField === 'created_at') <span class="material-symbols-outlined text-xs">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span> @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-[#6F767E] uppercase tracking-wide">Verified By</th>
                    <th class="px-4 py-3 text-right text-xs font-bold text-[#6F767E] uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-[#272B30]">
                @forelse($registrations as $reg)
                <tr class="hover:bg-gray-50 dark:hover:bg-[#1F1F1F] transition-colors group">
                    <td class="px-4 py-3">
                        <input wire:model.live="selectedItems" type="checkbox" value="{{ $reg->id }}"
                            class="rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                    </td>

                    {{-- Name (inline-editable) --}}
                    <td class="px-4 py-3">
                        <div class="font-semibold text-[#111827] dark:text-[#FCFCFC]">
                            {{ $reg->full_name ?? $reg->name }}
                        </div>
                        <div class="text-xs text-[#6F767E]">{{ $reg->job_title }}</div>
                        @if($reg->walk_in)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 mt-0.5">Walk-in</span>
                        @endif
                        @if($reg->check_in)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 mt-0.5">Checked In</span>
                        @endif
                    </td>

                    {{-- Email --}}
                    <td class="px-4 py-3">
                        <div class="text-sm text-[#111827] dark:text-[#FCFCFC]">{{ $reg->email }}</div>
                        @if($reg->mobile_phone ?? $reg->phone)
                            <div class="text-xs text-[#6F767E]">{{ $reg->mobile_phone ?? $reg->phone }}</div>
                        @endif
                    </td>

                    {{-- Company --}}
                    <td class="px-4 py-3">
                        <div class="text-sm text-[#111827] dark:text-[#FCFCFC]">{{ $reg->company_name ?? $reg->organization }}</div>
                        @if($reg->company_type)
                            <div class="text-xs text-[#6F767E]">{{ $reg->company_type }}</div>
                        @endif
                    </td>

                    {{-- Status Badge --}}
                    <td class="px-4 py-3">
                        @php
                            $statusMap = [
                                'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                'confirmed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                'attended'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            ];
                            $statusLabels = [
                                'pending'   => 'Pending',
                                'confirmed' => 'Approved',
                                'cancelled' => 'Rejected',
                                'attended'  => 'Attended',
                            ];
                            $cls = $statusMap[$reg->status] ?? 'bg-gray-100 text-gray-600';
                            $displayStatus = $statusLabels[$reg->status] ?? ucfirst($reg->status);
                        @endphp
                        
                        <button @click="$dispatch('open-change-status-modal', { registrationId: {{ $reg->id }}, currentStatus: '{{ $reg->status }}' })"
                            type="button"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $cls }} cursor-pointer hover:opacity-80 transition-all">
                            {{ $displayStatus }}
                        </button>
                        
                        @if($reg->verified_type)
                            <div class="text-[10px] text-[#6F767E] mt-0.5">{{ $reg->verified_type }}</div>
                        @endif
                    </td>

                    {{-- Registered At --}}
                    <td class="px-4 py-3 text-xs text-[#6F767E]">
                        {{ $reg->created_at->format('d M Y') }}<br>
                        <span class="text-[10px]">{{ $reg->created_at->format('H:i') }}</span>
                    </td>

                    {{-- Verified By --}}
                    <td class="px-4 py-3 text-xs text-[#6F767E]">
                        @if($reg->verifiedBy)
                            <div class="font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $reg->verifiedBy->name }}</div>
                            <div>{{ $reg->verified_at?->format('d M Y H:i') }}</div>
                            @if($reg->verified_note)
                                <div class="italic mt-0.5 truncate max-w-[120px]" title="{{ $reg->verified_note }}">{{ $reg->verified_note }}</div>
                            @endif
                        @else
                            <span class="text-[#6F767E]">—</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <button wire:click="editGuest({{ $reg->id }})" type="button" title="Edit"
                                class="p-1.5 rounded-lg text-[#6F767E] hover:text-[#2563EB] hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all">
                                <span class="material-symbols-outlined text-base">edit</span>
                            </button>
                            <button wire:click="deleteGuest({{ $reg->id }})" wire:confirm="Are you sure you want to delete this attendee?" type="button" title="Delete"
                                class="p-1.5 rounded-lg text-[#6F767E] hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <span class="material-symbols-outlined text-4xl text-[#6F767E] block mb-2">group_off</span>
                        <p class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC]">No guests found</p>
                        <p class="text-xs text-[#6F767E] mt-1">
                            @if($search || $activeTab !== 'all')
                                Try adjusting your filters
                            @else
                                No registrations yet for this event
                            @endif
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ───────────────────────────────────────────────────────── --}}
    @if($registrations->hasPages())
    <div class="flex items-center justify-between px-1">
        <p class="text-xs text-[#6F767E]">
            Showing {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }} of {{ $registrations->total() }} guests
        </p>
        <div>{{ $registrations->links() }}</div>
    </div>
    @endif

    {{-- ══════════════════════════════════════ CHANGE STATUS MODAL ══ --}}
    <div
        x-data="changeStatusModal()"
        x-show="open"
        x-cloak
        @open-change-status-modal.window="openWith($event.detail.registrationId, $event.detail.currentStatus)"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-[70] p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">

        <div @click.away="open = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white dark:bg-[#1A1A1A] rounded-2xl max-w-md w-full shadow-xl p-6 space-y-4">

            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Update Status</h3>
                <button @click="open = false" class="p-1.5 hover:bg-gray-100 dark:hover:bg-[#272B30] rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[#6F767E]">close</span>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-3">
                    <!-- Pending Option -->
                    <button type="button" @click="selectedStatus = 'pending'"
                        :class="selectedStatus === 'pending'
                            ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 ring-2 ring-amber-500/20'
                            : 'border-gray-200 dark:border-[#272B30] text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#272B30]'"
                        class="flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-xl mb-1">hourglass_empty</span>
                        <span class="text-xs font-bold">Pending</span>
                    </button>

                    <!-- Approve Option -->
                    <button type="button" @click="selectedStatus = 'confirmed'"
                        :class="selectedStatus === 'confirmed'
                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 ring-2 ring-green-500/20'
                            : 'border-gray-200 dark:border-[#272B30] text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#272B30]'"
                        class="flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-xl mb-1">check_circle</span>
                        <span class="text-xs font-bold">Approve</span>
                    </button>

                    <!-- Reject Option -->
                    <button type="button" @click="selectedStatus = 'cancelled'"
                        :class="selectedStatus === 'cancelled'
                            ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 ring-2 ring-red-500/20'
                            : 'border-gray-200 dark:border-[#272B30] text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#272B30]'"
                        class="flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-xl mb-1">cancel</span>
                        <span class="text-xs font-bold">Reject</span>
                    </button>
                </div>

                <div x-show="selectedStatus === 'confirmed'" class="space-y-3 pt-2" x-cloak>
                    <div>
                        <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Approval Type <span class="text-red-500">*</span></label>
                        <select x-model="approvalTypeId"
                            class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-green-500">
                            <option value="">— Select approval type —</option>
                            @foreach($approvalTypes['approved'] ?? [] as $type)
                                <option value="{{ $type['id'] }}">{{ $type['type_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Note (optional)</label>
                        <textarea x-model="note" rows="2"
                            class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-green-500 resize-none"
                            placeholder="Optional note..."></textarea>
                    </div>
                </div>

                <div x-show="selectedStatus === 'cancelled'" class="space-y-3 pt-2" x-cloak>
                    <div>
                        <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Rejection Reason <span class="text-red-500">*</span></label>
                        <select x-model="approvalTypeId"
                            class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-red-500">
                            <option value="">— Select rejection reason —</option>
                            @foreach($approvalTypes['rejected'] ?? [] as $type)
                                <option value="{{ $type['id'] }}">{{ $type['type_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Note (optional)</label>
                        <textarea x-model="note" rows="2"
                            class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-red-500 resize-none"
                            placeholder="Optional note..."></textarea>
                    </div>
                </div>

                <template x-if="selectedStatus !== currentStatus && (currentStatus === 'confirmed' || currentStatus === 'cancelled')">
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                        <span class="material-symbols-outlined text-sm mt-0.5 select-none">warning</span>
                        <div>
                            <strong>Attention:</strong> Since this attendee has already been processed (Approved/Rejected), changing their status may send another confirmation email. The attendee will receive duplicate/double email confirmations.
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button @click="open = false" type="button"
                    class="px-5 py-2 rounded-xl text-sm font-semibold text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                    Cancel
                </button>
                <button @click="submit" type="button"
                    class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-[#1D4ED8] shadow-sm transition-all">
                    Save Status
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════ EDIT MODAL ══ --}}
    <div
        x-data="{ open: @entangle('showEditModal') }"
        x-show="open"
        x-cloak
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-[70] p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">

        <div @click.away="$wire.closeEditModal()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white dark:bg-[#1A1A1A] rounded-2xl max-w-md w-full shadow-xl p-6 space-y-4">

            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Edit Attendee</h3>
                <button @click="$wire.closeEditModal()" class="p-1.5 hover:bg-gray-100 dark:hover:bg-[#272B30] rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[#6F767E]">close</span>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Full Name</label>
                    <input wire:model="editFullName" type="text"
                        class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Email</label>
                    <input wire:model="editEmail" type="email"
                        class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Phone</label>
                    <input wire:model="editPhone" type="text"
                        class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Company</label>
                    <input wire:model="editCompany" type="text"
                        class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Job Title</label>
                    <input wire:model="editJobTitle" type="text"
                        class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wide mb-1">Notes</label>
                    <textarea wire:model="editNotes" rows="2"
                        class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] resize-none"
                        placeholder="Additional notes..."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button @click="$wire.closeEditModal()" type="button"
                    class="px-5 py-2 rounded-xl text-sm font-semibold text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                    Cancel
                </button>
                <button wire:click="saveGuest" type="button"
                    class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-[#1D4ED8] shadow-sm transition-all">
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
