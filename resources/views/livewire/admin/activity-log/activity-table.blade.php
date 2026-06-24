<div>
    {{-- Filters --}}
    <div class="flex flex-col lg:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#6F767E]">search</span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search description or action..."
                class="h-12 w-full rounded-xl bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] focus:outline-none transition"
            />
        </div>

        <select wire:model.live="userFilter"
            class="h-12 rounded-xl bg-white dark:bg-[#1A1A1A] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
            <option value="">All users</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="actionFilter"
            class="h-12 rounded-xl bg-white dark:bg-[#1A1A1A] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
            <option value="">All actions</option>
            @foreach($actionGroups as $prefix => $label)
                <option value="{{ $prefix }}">{{ $label }}</option>
            @endforeach
        </select>

        <input type="date" wire:model.live="dateFrom"
            class="h-12 rounded-xl bg-white dark:bg-[#1A1A1A] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] focus:outline-none"
            title="From date" />
        <input type="date" wire:model.live="dateTo"
            class="h-12 rounded-xl bg-white dark:bg-[#1A1A1A] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] focus:outline-none"
            title="To date" />

        @if($search || $userFilter || $actionFilter || $dateFrom || $dateTo)
            <button wire:click="clearFilters" class="h-12 px-4 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] hover:bg-gray-200 dark:hover:bg-[#333] text-sm font-medium flex items-center gap-2 transition">
                <span class="material-symbols-outlined text-lg">close</span>
                Clear
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-[#0F1113]">
                    <tr class="text-left">
                        <th class="px-4 py-3 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider">Action</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider">When</th>
                        <th class="px-4 py-3 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#272B30]">
                    @forelse($activities as $a)
                        @php
                            $actionParts = explode('.', $a->action);
                            $module      = $actionParts[0] ?? 'system';
                            $verb        = $actionParts[1] ?? '';
                            $color = match (true) {
                                str_contains($a->action, 'created')  => 'bg-emerald-500/15 text-emerald-500',
                                str_contains($a->action, 'updated')  => 'bg-blue-500/15 text-blue-500',
                                str_contains($a->action, 'deleted')  => 'bg-red-500/15 text-red-500',
                                str_contains($a->action, 'login')    => 'bg-purple-500/15 text-purple-500',
                                str_contains($a->action, 'logout')   => 'bg-gray-500/15 text-gray-500',
                                str_contains($a->action, 'failed')   => 'bg-orange-500/15 text-orange-500',
                                default                              => 'bg-gray-500/15 text-gray-500',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30]/30 transition-colors">
                            <td class="px-4 py-3">
                                @if($a->user)
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                            @if($a->user->avatar)
                                                <img src="{{ asset('storage/' . $a->user->avatar) }}" alt="{{ $a->user->name }}" class="h-full w-full rounded-full object-cover">
                                            @else
                                                {{ strtoupper(substr($a->user->name, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC] truncate">{{ $a->user->name }}</p>
                                            <p class="text-[10px] text-[#6F767E] truncate">{{ $a->user->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-[#6F767E] italic">System</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded {{ $color }}">{{ $a->action }}</span>
                            </td>
                            <td class="px-4 py-3 text-[#111827] dark:text-[#FCFCFC] max-w-md">
                                <p class="truncate">{{ $a->description ?: '—' }}</p>
                                @if($a->subject_type && $a->subject_id)
                                    <p class="text-[10px] text-[#6F767E] mt-0.5 font-mono">
                                        {{ class_basename($a->subject_type) }}#{{ $a->subject_id }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-[#6F767E] whitespace-nowrap">
                                <span title="{{ $a->created_at }}">{{ $a->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($a->properties)
                                    <button wire:click="toggleExpand({{ $a->id }})" class="p-2 rounded-lg text-[#6F767E] hover:text-[#2563EB] hover:bg-blue-500/10 transition" title="View details">
                                        <span class="material-symbols-outlined text-[18px]">{{ $expandedId === $a->id ? 'expand_less' : 'expand_more' }}</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @if($expandedId === $a->id && $a->properties)
                            <tr class="bg-gray-50 dark:bg-[#0F1113]">
                                <td colspan="5" class="px-4 py-3">
                                    <div class="text-xs text-[#6F767E] mb-2">Properties</div>
                                    <pre class="text-[11px] font-mono text-[#111827] dark:text-[#FCFCFC] bg-white dark:bg-[#1A1A1A] p-3 rounded-lg overflow-x-auto border border-gray-200 dark:border-[#272B30]">{{ json_encode($a->properties, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
                                    @if($a->ip_address || $a->user_agent)
                                        <div class="mt-2 flex gap-4 text-[11px] text-[#6F767E]">
                                            @if($a->ip_address)<span><strong>IP:</strong> {{ $a->ip_address }}</span>@endif
                                            @if($a->user_agent)<span class="truncate" title="{{ $a->user_agent }}"><strong>UA:</strong> {{ \Illuminate\Support\Str::limit($a->user_agent, 100) }}</span>@endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <span class="material-symbols-outlined text-[40px] text-[#6F767E]">history</span>
                                <p class="text-sm font-medium text-[#6F767E] mt-2">No activities match your filters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
        <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30] flex items-center justify-between">
            <p class="text-sm font-medium text-[#6F767E]">
                Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} activities
            </p>
            <div class="flex items-center gap-2">
                @if($activities->onFirstPage())
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

                @foreach($activities->getUrlRange(max(1, $activities->currentPage() - 2), min($activities->lastPage(), $activities->currentPage() + 2)) as $page => $url)
                    @if($page == $activities->currentPage())
                    <button class="h-10 w-10 rounded-xl bg-[#2563EB] text-white flex items-center justify-center text-sm font-bold shadow-lg shadow-blue-500/20">{{ $page }}</button>
                    @else
                    <button wire:click="gotoPage({{ $page }})" class="h-10 w-10 rounded-xl bg-white dark:bg-[#1A1A1A] flex items-center justify-center text-sm font-bold text-[#6F767E] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if($activities->hasMorePages())
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
</div>
