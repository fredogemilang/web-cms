<div>
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.events.index') }}" class="flex items-center gap-1 text-sm text-[#6F767E] hover:text-[#2563EB] transition-colors mb-2">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Back to Events
                </a>
                <h1 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $event->title }}</h1>
                <p class="text-sm text-[#6F767E] mt-1">Guest List Management</p>
            </div>
            <div class="flex items-center gap-3">
                @if($event->registration_requires_approval && $event->max_participants > 0)
                    <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-xl px-4 py-2 text-center">
                        <p class="text-xs text-[#6F767E] font-medium uppercase tracking-wide">Remaining Slots</p>
                        <p class="text-xl font-bold text-[#2563EB]">{{ $remainingSlots ?? $event->max_participants }}</p>
                    </div>
                @endif
                <a href="{{ route('admin.events.admin.guests.export', $event->id) }}"
                   class="h-12 px-5 rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] font-bold text-sm hover:border-[#2563EB] hover:text-[#2563EB] transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Livewire Table Component -->
    <livewire:plugins.event-guests-table
        :event="$event"
        :approvalTypes="$approvalTypes"
        :key="'guests-'.$event->id" />

    {{-- JS: store event ID for AJAX calls in Alpine modals --}}
    <script>window._guestEventId = {{ $event->id }};</script>
