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
                            $cls = $statusMap[$reg->status] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $cls }}">
                            {{ ucfirst($reg->status) }}
                        </span>
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
                            @if($reg->status === 'pending')
                                <button wire:click="approve({{ $reg->id }})" type="button"
                                    title="Approve"
                                    class="p-1.5 rounded-lg text-[#6F767E] hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all">
                                    <span class="material-symbols-outlined text-base">check_circle</span>
                                </button>
                                <button wire:click="reject({{ $reg->id }})" type="button"
                                    title="Reject"
                                    class="p-1.5 rounded-lg text-[#6F767E] hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                    <span class="material-symbols-outlined text-base">cancel</span>
                                </button>
                            @endif
                            @if($reg->status === 'confirmed' && !$reg->check_in)
                                <button wire:click="checkin({{ $reg->id }})" type="button"
                                    title="Check In"
                                    class="p-1.5 rounded-lg text-[#6F767E] hover:text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-all">
                                    <span class="material-symbols-outlined text-base">how_to_reg</span>
                                </button>
                            @endif
                            @if($reg->status === 'confirmed' && !$reg->check_in)
                                <button wire:click="reject({{ $reg->id }})" type="button"
                                    title="Revoke Approval"
                                    class="p-1.5 rounded-lg text-[#6F767E] hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                    <span class="material-symbols-outlined text-base">person_remove</span>
                                </button>
                            @endif
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

    {{-- ══════════════════════════════════════ APPROVE MODAL ══ --}}
    <div
        x-data="approveModal()"
        x-show="open"
        x-cloak
        @open-approve-modal.window="openWith($event.detail.registrationId)"
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
                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Approve Guest</h3>
                <button @click="open = false" class="p-1.5 hover:bg-gray-100 dark:hover:bg-[#272B30] rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[#6F767E]">close</span>
                </button>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-1.5">
                    Approval Type <span class="text-red-500">*</span>
                </label>
                <select x-model="approvalTypeId"
                    class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-green-500">
                    <option value="">— Select approval type —</option>
                    @foreach($approvalTypes->get('approved', collect()) as $type)
                        <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-1.5">Note (optional)</label>
                <textarea x-model="note" rows="2"
                    class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-green-500 resize-none"
                    placeholder="Optional note for this approval…"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button @click="open = false" type="button"
                    class="px-5 py-2 rounded-xl text-sm font-semibold text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                    Cancel
                </button>
                <button @click="submit" type="button"
                    class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 shadow-sm transition-all">
                    Approve Guest
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════ REJECT MODAL ══ --}}
    <div
        x-data="rejectModal()"
        x-show="open"
        x-cloak
        @open-reject-modal.window="openWith($event.detail.registrationId)"
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
                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Reject Guest</h3>
                <button @click="open = false" class="p-1.5 hover:bg-gray-100 dark:hover:bg-[#272B30] rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[#6F767E]">close</span>
                </button>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-1.5">
                    Rejection Reason <span class="text-red-500">*</span>
                </label>
                <select x-model="approvalTypeId"
                    class="w-full h-11 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-red-500">
                    <option value="">— Select reason —</option>
                    @foreach($approvalTypes->get('rejected', collect()) as $type)
                        <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-1.5">Note (optional)</label>
                <textarea x-model="note" rows="2"
                    class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-red-500 resize-none"
                    placeholder="Reason for rejection…"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button @click="open = false" type="button"
                    class="px-5 py-2 rounded-xl text-sm font-semibold text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                    Cancel
                </button>
                <button @click="submit" type="button"
                    class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-sm transition-all">
                    Reject Guest
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function approveModal() {
    return {
        open: false,
        registrationId: null,
        approvalTypeId: '',
        note: '',
        openWith(id) {
            this.registrationId = id;
            this.approvalTypeId = '';
            this.note = '';
            this.open = true;
        },
        submit() {
            if (!this.approvalTypeId) {
                alert('Please select an approval type.');
                return;
            }
            fetch(`{{ url('/'.config('admin.path','admin').'/events') }}/${window._guestEventId}/guests/${this.registrationId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ approval_type_id: this.approvalTypeId, note: this.note }),
            })
            .then(r => r.json())
            .then(data => {
                this.open = false;
                if (data.success) {
                    window.Livewire.dispatch('refresh');
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { type: 'success', message: data.message } }));
                } else {
                    alert(data.message);
                }
            });
        }
    }
}

function rejectModal() {
    return {
        open: false,
        registrationId: null,
        approvalTypeId: '',
        note: '',
        openWith(id) {
            this.registrationId = id;
            this.approvalTypeId = '';
            this.note = '';
            this.open = true;
        },
        submit() {
            if (!this.approvalTypeId) {
                alert('Please select a rejection reason.');
                return;
            }
            fetch(`{{ url('/'.config('admin.path','admin').'/events') }}/${window._guestEventId}/guests/${this.registrationId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ approval_type_id: this.approvalTypeId, note: this.note }),
            })
            .then(r => r.json())
            .then(data => {
                this.open = false;
                if (data.success) {
                    window.Livewire.dispatch('refresh');
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { type: 'success', message: data.message } }));
                } else {
                    alert(data.message);
                }
            });
        }
    }
}
</script>
