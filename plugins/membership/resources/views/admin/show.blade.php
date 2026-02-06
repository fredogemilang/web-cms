@extends('layouts.admin')

@section('title', 'Member Details')

@section('content')
<div class="flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <a class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all"
                href="{{ route('admin.membership.index') }}">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Member Details</h1>
                <p class="text-xs text-[#6F767E] mt-0.5">{{ $membership->user->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($membership->status !== 'active')
            <form action="{{ route('admin.membership.approve', $membership) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-green-600 hover:bg-green-700 transition-all">
                    Approve
                </button>
            </form>
            @endif

            @if($membership->status !== 'rejected')
            <form action="{{ route('admin.membership.reject', $membership) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">
                    Reject
                </button>
            </form>
            @endif

            @if($membership->status === 'active')
            <form action="{{ route('admin.membership.suspend', $membership) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-yellow-600 hover:bg-yellow-700 transition-all">
                    Suspend
                </button>
            </form>
            @endif

            @if($membership->status === 'suspended')
            <form action="{{ route('admin.membership.reactivate', $membership) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition-all">
                    Reactivate
                </button>
            </form>
            @endif

            <form action="{{ route('admin.membership.destroy', $membership) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-gray-600 hover:bg-gray-700 transition-all">
                    Delete
                </button>
            </form>
        </div>
    </header>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Member Info Card -->
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-8 shadow-sm">
                <div class="flex items-start gap-6">
                    <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-4xl flex-shrink-0">
                        {{ substr($membership->user->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-extrabold text-[#111827] dark:text-[#FCFCFC]">{{ $membership->user->name }}</h2>
                        <p class="text-[#6F767E] mt-1">{{ $membership->user->email }}</p>
                        
                        <div class="flex items-center gap-3 mt-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
                                    'active' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
                                    'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                                    'suspended' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-bold {{ $statusColors[$membership->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($membership->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Membership Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest mb-4">Membership Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-[#6F767E]">Joined Date</p>
                            <p class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">
                                {{ $membership->joined_at ? $membership->joined_at->format('F d, Y') : 'Not yet approved' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-[#6F767E]">Registration Date</p>
                            <p class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $membership->created_at->format('F d, Y') }}</p>
                        </div>
                        @if($membership->approved_by)
                        <div>
                            <p class="text-xs text-[#6F767E]">Approved By</p>
                            <p class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $membership->approver->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-[#6F767E]">Email</p>
                            <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $membership->user->email }}</p>
                        </div>
                        @if($membership->metadata && isset($membership->metadata['phone']))
                        <div>
                            <p class="text-xs text-[#6F767E]">Phone</p>
                            <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $membership->metadata['phone'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            @if($membership->metadata && count($membership->metadata) > 0)
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest mb-4">Additional Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($membership->metadata as $key => $value)
                    <div>
                        <p class="text-xs text-[#6F767E]">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                        <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $value }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Admin Notes -->
            @if($membership->notes)
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest mb-4">Admin Notes</h3>
                <p class="text-sm text-[#111827] dark:text-[#FCFCFC] whitespace-pre-wrap">{{ $membership->notes }}</p>
            </div>
            @endif

            <!-- Timestamps -->
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest mb-4">Record Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-[#6F767E]">Created</p>
                        <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $membership->created_at->format('F d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#6F767E]">Last Updated</p>
                        <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $membership->updated_at->format('F d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
