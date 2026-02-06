@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    // Dummy data for demonstration
    $stats = [
        'total_visitors' => '128,430',
        'visitors_change' => '+12.5%',
        'active_pages' => 42,
        'pages_change' => '+2',
        'bounce_rate' => '24.8%',
        'bounce_change' => '-3%',
    ];

    $trafficData = [
        ['day' => 'Mon', 'value' => 12, 'height' => '40%'],
        ['day' => 'Tue', 'value' => 18, 'height' => '65%'],
        ['day' => 'Wed', 'value' => 15, 'height' => '55%'],
        ['day' => 'Thu', 'value' => 24, 'height' => '85%'],
        ['day' => 'Fri', 'value' => 20, 'height' => '70%'],
        ['day' => 'Sat', 'value' => 28, 'height' => '95%'],
        ['day' => 'Sun', 'value' => 16, 'height' => '60%'],
    ];

    $recentActivities = [
        [
            'name' => 'Gladyce',
            'action' => 'updated the',
            'target' => 'Home Page',
            'time' => '2 minutes ago',
            'type' => 'Update',
            'typeColor' => 'text-[#83BF6E] bg-[#83BF6E]/15',
        ],
        [
            'name' => 'Elbert',
            'action' => 'published a new post:',
            'target' => 'Tech Trends 2024',
            'time' => '1 hour ago',
            'type' => 'New Post',
            'typeColor' => 'text-blue-500 bg-blue-500/15',
        ],
        [
            'name' => 'Sarah',
            'action' => 'deleted',
            'target' => 'Old Archive Page',
            'time' => '3 hours ago',
            'type' => 'Delete',
            'typeColor' => 'text-red-500 bg-red-500/15',
        ],
    ];

    $inquiries = [
        ['name' => 'James Wilson', 'message' => 'Interested in partnership...', 'time' => '12:30 PM'],
        ['name' => 'Sarah Connor', 'message' => 'Quote for website redesign...', 'time' => 'Yesterday'],
    ];
@endphp

<div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
    <div class="lg:col-span-3 space-y-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Visitors -->
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 shadow-sm flex flex-col justify-between border border-gray-200 dark:border-[#272B30]">
                <div class="flex items-center justify-between mb-4">
                    <div class="h-12 w-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <span class="text-xs font-bold text-[#83BF6E] bg-[#83BF6E]/15 px-2.5 py-1 rounded-lg">{{ $stats['visitors_change'] }}</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-[#6F767E] uppercase tracking-wider mb-1">Total Visitors</p>
                    <p class="text-3xl font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $stats['total_visitors'] }}</p>
                </div>
            </div>

            <!-- Active Pages -->
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 shadow-sm flex flex-col justify-between border border-gray-200 dark:border-[#272B30]">
                <div class="flex items-center justify-between mb-4">
                    <div class="h-12 w-12 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                        <span class="material-symbols-outlined">description</span>
                    </div>
                    <span class="text-xs font-bold text-[#83BF6E] bg-[#83BF6E]/15 px-2.5 py-1 rounded-lg">{{ $stats['pages_change'] }}</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-[#6F767E] uppercase tracking-wider mb-1">Active Pages</p>
                    <p class="text-3xl font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $stats['active_pages'] }}</p>
                </div>
            </div>

            <!-- Bounce Rate -->
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 shadow-sm flex flex-col justify-between border border-gray-200 dark:border-[#272B30]">
                <div class="flex items-center justify-between mb-4">
                    <div class="h-12 w-12 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center">
                        <span class="material-symbols-outlined">bolt</span>
                    </div>
                    <span class="text-xs font-bold text-[#FF6A55] bg-[#FF6A55]/15 px-2.5 py-1 rounded-lg">{{ $stats['bounce_change'] }}</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-[#6F767E] uppercase tracking-wider mb-1">Bounce Rate</p>
                    <p class="text-3xl font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $stats['bounce_rate'] }}</p>
                </div>
            </div>
        </div>

        <!-- Website Performance Chart -->
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-8 shadow-sm border border-gray-200 dark:border-[#272B30]">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Website Performance</h2>
                    <p class="text-sm text-[#6F767E] mt-1">Daily traffic trends for the last 7 days</p>
                </div>
                <select class="bg-white border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] rounded-xl px-4 py-2 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-0 focus:outline-none">
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                </select>
            </div>
            <div class="h-64 w-full flex items-end justify-between gap-4 pt-4">
                @foreach($trafficData as $data)
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-[#2563EB]/20 rounded-t-lg relative group" style="height: {{ $data['height'] }};">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-white dark:bg-[#1A1D1F] text-[#111827] dark:text-white text-[10px] px-2 py-1 rounded shadow-md opacity-0 group-hover:opacity-100 transition-opacity">
                            {{ $data['value'] }}k
                        </div>
                        <div class="w-full h-1 bg-[#2563EB] absolute top-0 rounded-full"></div>
                    </div>
                    <span class="text-xs font-medium text-[#6F767E]">{{ $data['day'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Content Activity -->
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-0 shadow-sm overflow-hidden border border-gray-200 dark:border-[#272B30]">
            <div class="p-8 border-b border-gray-200 dark:border-[#272B30] flex items-center justify-between">
                <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Recent Content Activity</h2>
                <button class="text-sm font-bold text-[#2563EB] hover:underline transition-all">View History</button>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-[#272B30]">
                @foreach($recentActivities as $activity)
                <div class="p-6 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-[#272B30]/30 transition-colors">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm">
                        {{ substr($activity['name'], 0, 2) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">
                            {{ $activity['name'] }} 
                            <span class="font-normal text-[#6F767E]">{{ $activity['action'] }}</span> 
                            {{ $activity['target'] }}
                        </p>
                        <p class="text-xs text-[#6F767E] mt-0.5">{{ $activity['time'] }}</p>
                    </div>
                    <span class="text-xs font-bold {{ $activity['typeColor'] }} px-2 py-1 rounded">{{ $activity['type'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <aside class="space-y-8">
        <!-- Site Status -->
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 shadow-sm border border-gray-200 dark:border-[#272B30]">
            <h2 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-6">Site Status</h2>
            <div class="flex items-center justify-between p-4 rounded-2xl bg-[#F4F5F6] dark:bg-[#111315]">
                <div class="flex items-center gap-3">
                    <div class="h-3 w-3 rounded-full bg-[#83BF6E] animate-pulse"></div>
                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Online</span>
                </div>
                <div class="relative inline-flex h-6 w-11 items-center rounded-full bg-[#83BF6E] cursor-pointer">
                    <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                </div>
            </div>
            <p class="text-[11px] text-[#6F767E] mt-4 text-center">Maintenance mode is currently disabled.</p>
        </div>

        <!-- Quick Inquiries -->
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 shadow-sm border border-gray-200 dark:border-[#272B30]">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Quick Inquiries</h2>
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#FF6A55] text-[10px] font-bold text-white">{{ count($inquiries) }}</span>
            </div>
            <div class="space-y-4">
                @foreach($inquiries as $inquiry)
                <div class="p-4 rounded-2xl border border-gray-100 dark:border-[#272B30] hover:bg-gray-50 dark:hover:bg-[#272B30]/30 transition-all cursor-pointer">
                    <p class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $inquiry['name'] }}</p>
                    <p class="text-xs text-[#6F767E] mt-1 truncate">{{ $inquiry['message'] }}</p>
                    <p class="text-[10px] text-[#6F767E] mt-2">{{ $inquiry['time'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Content Health -->
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 shadow-sm border border-gray-200 dark:border-[#272B30]">
            <h2 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-6">Content Health</h2>
            <div class="space-y-6">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-[#6F767E]">SEO Score</span>
                        <span class="text-xs font-bold text-[#83BF6E]">92%</span>
                    </div>
                    <div class="h-2 w-full bg-[#F4F5F6] dark:bg-[#272B30] rounded-full overflow-hidden">
                        <div class="h-full bg-[#83BF6E] rounded-full" style="width: 92%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-[#6F767E]">Media Alt Tags</span>
                        <span class="text-xs font-bold text-orange-500">64%</span>
                    </div>
                    <div class="h-2 w-full bg-[#F4F5F6] dark:bg-[#272B30] rounded-full overflow-hidden">
                        <div class="h-full bg-orange-500 rounded-full" style="width: 64%"></div>
                    </div>
                </div>
                <button class="w-full mt-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] text-xs font-bold text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] hover:border-gray-400 dark:hover:border-white/50 transition-all">
                    Run Health Check
                </button>
            </div>
        </div>
    </aside>
</div>
@endsection
