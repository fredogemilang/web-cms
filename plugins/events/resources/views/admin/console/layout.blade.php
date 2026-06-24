@extends('layouts.admin')

@section('title', $event->title . ' — Event Console')

@section('hide-header', true)

@section('content')
<!-- Inject Plus Jakarta Sans font and custom scrollbar styles -->
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
    /* ─── Console Theme: CSS Variables for Light/Dark ─── */
    .console-theme {
        --c-bg: #F4F5F6;
        --c-bg-alpha: rgba(244, 245, 246, 0.95);
        --c-surface: #FFFFFF;
        --c-surface-lighter: #F3F4F6;
        --c-surface-alpha: rgba(255, 255, 255, 0.85);
        --c-border: #E5E7EB;
        --c-text: #111827;
        --c-text-secondary: #6F767E;
        --c-input: #F9FAFB;
        --c-dot: #D1D5DB;
    }
    .dark .console-theme {
        --c-bg: #0B0B0B;
        --c-bg-alpha: rgba(11, 11, 11, 0.9);
        --c-surface: #161618;
        --c-surface-lighter: #222225;
        --c-surface-alpha: rgba(22, 22, 24, 0.8);
        --c-border: #27272A;
        --c-text: #FCFCFC;
        --c-text-secondary: #9CA3AF;
        --c-input: #111113;
        --c-dot: #27272A;
    }

    /* ─── Utility Classes ─── */
    .console-bg       { background-color: var(--c-bg) !important; }
    .console-bg-alpha { background-color: var(--c-bg-alpha); }
    .glass-panel      { background-color: var(--c-surface-alpha); backdrop-filter: blur(12px); border: 1px solid var(--c-border); }
    .text-text-primary   { color: var(--c-text); }
    .text-text-secondary { color: var(--c-text-secondary); }
    .border-dark-border  { border-color: var(--c-border) !important; }
    .bg-dark-surface         { background-color: var(--c-surface); }
    .bg-dark-surface-lighter { background-color: var(--c-surface-lighter); }
    .bg-dark-bg-console      { background-color: var(--c-bg); }
    .bg-console-input        { background-color: var(--c-input); }
    .divide-dark-border > :not([hidden]) ~ :not([hidden]) { border-color: var(--c-border) !important; }

    /* Make Material Symbols icons respect font-size properly */
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20;
    }
</style>
@endpush

<div class="console-theme console-bg text-text-primary font-sans h-full flex flex-col overflow-hidden" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    {{-- ─── STICKY MAIN CONSOLE HEADER ──────────────────────────────── --}}
    <header class="h-20 border-b border-dark-border console-bg-alpha backdrop-blur-md sticky top-0 z-40 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center gap-4">
            <button @click="toggleSidebar()" class="h-10 w-10 flex items-center justify-center rounded-xl bg-dark-surface border border-dark-border text-text-secondary hover:text-text-primary transition-all">
                <span class="material-symbols-outlined text-lg" x-text="sidebarCollapsed ? 'menu' : 'menu_open'">menu_open</span>
            </button>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-lg font-bold text-text-primary leading-tight">{{ $event->title }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide border {{ $event->status === 'published' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-gray-500/10 text-gray-500 dark:text-gray-400 border-gray-500/20' }}">
                        {{ $event->status }}
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-text-secondary mt-0.5">
                    @if($event->start_date)
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_month</span>
                            <span>{{ $event->start_date->format('Y-m-d') }}</span>
                        </span>
                    @endif
                    <span class="h-1 w-1 rounded-full" style="background-color: var(--c-dot);"></span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">location_on</span>
                        <span class="truncate max-w-[200px]">{{ $event->location ?? 'No Location' }}</span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ url('/event/' . $event->slug) }}" target="_blank" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary hover:text-text-primary bg-dark-surface border border-dark-border transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">visibility</span>
                <span>Preview Page</span>
            </a>
            <button onclick="window.dispatchEvent(new CustomEvent('console-save'))" class="px-6 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-900/20 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">save</span>
                <span>Save Changes</span>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        {{-- ─── MAIN WORKSPACE CONTENT ────────────────────────────────── --}}
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6 no-scrollbar">
            
            {{-- ─── STATS ROW ────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Registered Quota --}}
                <div class="glass-panel rounded-2xl p-5 flex flex-col justify-between hover:border-[#2563EB]/40 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Registered Quota</span>
                        <div class="h-8 w-8 rounded-lg bg-[#2563EB]/10 text-[#2563EB] flex items-center justify-center">
                            <span class="material-symbols-outlined text-base">group</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-1">
                            <h3 class="text-2xl font-extrabold text-text-primary">
                                {{ $stats['registered'] }} /
                            </h3>
                            @if($event->limit_by_quota && $stats['quota'])
                                <span class="text-2xl font-extrabold text-text-primary">{{ $stats['quota'] }}</span>
                            @else
                                <span class="material-symbols-outlined text-2xl text-text-primary font-bold select-none" style="font-variation-settings: 'FILL' 1, 'wght' 700, 'GRAD' 0, 'opsz' 24;">all_inclusive</span>
                            @endif
                        </div>
                        @if($event->limit_by_quota && $stats['quota'])
                            <div class="w-full rounded-full h-1.5 mt-3" style="background-color: var(--c-border);">
                                <div class="bg-[#2563EB] h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, ($stats['registered'] / $stats['quota']) * 100) }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Pending Approvals --}}
                <div class="glass-panel rounded-2xl p-5 flex flex-col justify-between hover:border-amber-500/40 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Pending Approvals</span>
                        <div class="h-8 w-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <span class="material-symbols-outlined text-base">hourglass_empty</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-extrabold text-text-primary">{{ $stats['pending'] }} Request(s)</h3>
                        <a href="{{ route('admin.events.console.attendees', $event) }}" wire:navigate class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-2 hover:underline inline-flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">open_in_new</span> Review registration queue
                        </a>
                    </div>
                </div>

                {{-- Approved --}}
                <div class="glass-panel rounded-2xl p-5 flex flex-col justify-between hover:border-emerald-500/40 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Approved</span>
                        <div class="h-8 w-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-extrabold text-text-primary">{{ $stats['approved'] }} Approved</h3>
                        <a href="{{ route('admin.events.console.attendees', $event) }}" wire:navigate class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-2 hover:underline inline-flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">open_in_new</span> View approved attendees
                        </a>
                    </div>
                </div>

                {{-- Checked In Guests --}}
                <div class="glass-panel rounded-2xl p-5 flex flex-col justify-between hover:border-indigo-500/40 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Checked In Guests</span>
                        <div class="h-8 w-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-extrabold text-text-primary">{{ $stats['checkedIn'] }} Checked In</h3>
                        <p class="text-[10px] text-text-secondary mt-2">Attendance Rate: {{ $stats['approved'] > 0 ? round(($stats['checkedIn'] / $stats['approved']) * 100) : 0 }}%</p>
                    </div>
                </div>
            </div>

            {{-- ─── TAB NAVIGATION ────────────────────────────────────── --}}
            <div class="border-b border-dark-border flex items-center gap-1 overflow-x-auto no-scrollbar">
                @php
                    $tabs = [
                        'overview'  => ['label' => 'Console Home',        'icon' => 'grid_view'],
                        'general'   => ['label' => 'General Setup',       'icon' => 'edit_note'],
                        'datetime'  => ['label' => 'Registration & Date',  'icon' => 'calendar_month'],
                        'referrals' => ['label' => 'Referral & Tracking',  'icon' => 'ads_click'],
                        'emails'    => ['label' => 'Email & Templates',   'icon' => 'mail_outline'],
                        'questions' => ['label' => 'Custom Questions',    'icon' => 'quiz'],
                        'attendees' => ['label' => 'Attendee',            'icon' => 'people'],
                        'feedback'  => ['label' => 'Feedback Form',       'icon' => 'rate_review'],
                        'doorprize' => ['label' => 'Doorprize',           'icon' => 'card_membership'],
                    ];
                    $currentTab = $currentTab ?? 'overview';
                @endphp

                @foreach($tabs as $key => $tab)
                    <a href="{{ route('admin.events.console.' . $key, $event) }}" wire:navigate
                       class="px-5 py-3 border-b-2 text-sm font-semibold transition-all flex items-center gap-2 rounded-t-xl shrink-0
                           {{ $currentTab === $key
                               ? 'border-[#2563EB] text-text-primary font-bold bg-dark-surface'
                               : 'border-transparent text-text-secondary hover:text-text-primary' }}">
                        <span class="material-symbols-outlined text-[18px]">{{ $tab['icon'] }}</span>
                        <span>{{ $tab['label'] }}</span>
                    </a>
                @endforeach
            </div>

            {{-- ─── CONSOLE TAB CONTENT ────────────────────────────────── --}}
            <div class="min-h-[400px]">
                @yield('console-content')
            </div>

        </div>
    </div>
</div>
@endsection
