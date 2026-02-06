<div class="flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <header class="sticky top-0 z-30 flex items-center justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm">
        <h1 class="text-3xl font-extrabold text-[#111827] dark:text-[#FCFCFC]">Community Members</h1>
    </header>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-[#6F767E] uppercase tracking-widest">Total Members</p>
                            <h3 class="text-3xl font-extrabold text-[#111827] dark:text-[#FCFCFC] mt-2">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="h-14 w-14 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-3xl text-blue-600 dark:text-blue-400">people</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-[#6F767E] uppercase tracking-widest">Active</p>
                            <h3 class="text-3xl font-extrabold text-green-600 dark:text-green-400 mt-2">{{ $stats['active'] }}</h3>
                        </div>
                        <div class="h-14 w-14 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-3xl text-green-600 dark:text-green-400">check_circle</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-[#6F767E] uppercase tracking-widest">Pending</p>
                            <h3 class="text-3xl font-extrabold text-yellow-600 dark:text-yellow-400 mt-2">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="h-14 w-14 rounded-2xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-3xl text-yellow-600 dark:text-yellow-400">pending</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-[#6F767E] uppercase tracking-widest">Rejected</p>
                            <h3 class="text-3xl font-extrabold text-red-600 dark:text-red-400 mt-2">{{ $stats['rejected'] }}</h3>
                        </div>
                        <div class="h-14 w-14 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-3xl text-red-600 dark:text-red-400">cancel</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & Actions Bar (Single Row like Events) -->
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <!-- Search -->
                    <div class="flex-1 w-full md:w-auto">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#6F767E]">search</span>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="w-full h-12 pl-12 pr-4 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all"
                                placeholder="Search members...">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="w-full md:w-48">
                        <select wire:model.live="statusFilter"
                            class="w-full h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="rejected">Rejected</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <!-- Display Rows -->
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <span class="text-xs font-medium text-[#6F767E] whitespace-nowrap">Display:</span>
                        <select wire:model.live="perPage"
                            class="h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all">
                            <option value="10">10 Rows</option>
                            <option value="25">25 Rows</option>
                            <option value="50">50 Rows</option>
                            <option value="100">100 Rows</option>
                        </select>
                    </div>

                    <!-- Export Button -->
                    <a href="{{ route('admin.membership.export') }}"
                        class="w-full md:w-auto px-6 py-3 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                        <span class="material-symbols-outlined text-lg">download</span>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            @if(count($selectedMembers) > 0)
            <div class="rounded-3xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-blue-900 dark:text-blue-100">{{ count($selectedMembers) }} selected</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="bulkApprove" wire:confirm="Approve selected members?"
                        class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition-all">
                        Approve
                    </button>
                    <button wire:click="bulkDelete" wire:confirm="Delete selected members?"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition-all">
                        Delete
                    </button>
                </div>
            </div>
            @endif

            <!-- Members Table -->
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                                <th class="px-6 py-5 w-12">
                                    <input type="checkbox" wire:model.live="selectAll"
                                        class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </th>
                                <th class="px-4 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest cursor-pointer" wire:click="sortBy('user_id')">
                                    Member
                                </th>
                                <th class="px-4 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest cursor-pointer" wire:click="sortBy('joined_at')">
                                    Joined Date
                                </th>
                                <th class="px-4 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Status</th>
                                <th class="px-4 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest cursor-pointer" wire:click="sortBy('created_at')">
                                    Registered
                                </th>
                                <th class="px-6 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30">
                            @forelse($memberships as $membership)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" wire:model.live="selectedMembers" value="{{ $membership->id }}"
                                        class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                            {{ substr($membership->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $membership->user->name }}</p>
                                            <p class="text-xs text-[#6F767E]">{{ $membership->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-sm text-[#111827] dark:text-[#FCFCFC] font-medium">
                                        {{ $membership->joined_at ? $membership->joined_at->format('M d, Y') : '-' }}
                                    </p>
                                </td>
                                <td class="px-4 py-4">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
                                            'active' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
                                            'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                                            'suspended' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold {{ $statusColors[$membership->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($membership->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-xs text-[#6F767E]">{{ $membership->created_at->format('M d, Y') }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-1 items-center justify-end">
                                        @if($membership->status !== 'active')
                                        <button wire:click="approveMember({{ $membership->id }})" wire:confirm="Approve this member?"
                                            class="w-9 h-9 p-2 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/30 text-green-600 dark:text-green-400 transition-colors"
                                            title="Approve">
                                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                        </button>
                                        @endif

                                        @if($membership->status !== 'rejected')
                                        <button wire:click="rejectMember({{ $membership->id }})" wire:confirm="Reject this member?"
                                            class="w-9 h-9 p-2 rounded-xl hover:bg-orange-100 dark:hover:bg-orange-900/30 text-orange-600 dark:text-orange-400 transition-colors"
                                            title="Reject">
                                            <span class="material-symbols-outlined text-[20px]">block</span>
                                        </button>
                                        @endif

                                        <a href="{{ route('admin.membership.show', $membership->id) }}"
                                            class="w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors"
                                            title="View Details">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </a>

                                        <button wire:click="deleteMember({{ $membership->id }})" wire:confirm="Are you sure you want to delete this member? This action cannot be undone."
                                            class="w-9 h-9 p-2 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                                            title="Delete">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
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
                                        <p class="text-[#6F767E] font-medium">No members found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($memberships->hasPages())
                <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30]">
                    {{ $memberships->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
