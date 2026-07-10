<div>
        {{-- Sub-tab Navigation --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex gap-2">
                @foreach(['sessions' => 'Sessions & Prizes', 'eligible' => 'Eligible Users', 'winners' => 'All Winners'] as $key => $label)
                    <button wire:click="$set('activeSubTab', '{{ $key }}')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all border
                            {{ $activeSubTab === $key
                                ? 'border-[#2563EB] text-text-primary bg-[#2563EB]/10'
                                : 'border-dark-border text-text-secondary hover:text-text-primary bg-dark-surface' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            @if($activeSubTab === 'sessions')
                <button wire:click="openAddSession" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">add</span> Add Session
                </button>
            @endif
        </div>

        {{-- ═══ SESSIONS TAB ═══ --}}
        @if($activeSubTab === 'sessions')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                {{-- Left/Main Column: Sessions & Prizes --}}
                <div class="lg:col-span-2">
                    @if($this->sessions->isEmpty())
                        <div class="glass-panel rounded-2xl p-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-text-secondary mb-2">redeem</span>
                            <p class="text-sm font-semibold text-text-primary">No raffle sessions yet</p>
                            <p class="text-xs text-text-secondary mt-1">Create a session to start managing doorprize raffles</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($this->sessions as $session)
                    <div class="glass-panel rounded-2xl overflow-hidden">
                        {{-- Session Header --}}
                        <div class="p-5 flex items-center justify-between border-b border-dark-border">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[#2563EB]">casino</span>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-text-primary">{{ $session->name }}</h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        @if($session->require_checkin)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-500">CHECK-IN REQ</span>
                                        @endif
                                        @if($session->require_feedback)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-500">FEEDBACK REQ</span>
                                        @endif
                                        <span class="text-[10px] text-text-secondary">{{ $session->prizes->count() }} prize(s)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click="openBanManager({{ $session->id }})" class="p-2 rounded-lg text-text-secondary hover:text-text-primary hover:bg-dark-surface-lighter transition-all" title="Manage exclusions">
                                    <span class="material-symbols-outlined text-sm">person_off</span>
                                </button>
                                <button wire:click="openEditSession({{ $session->id }})" class="p-2 rounded-lg text-text-secondary hover:text-text-primary hover:bg-dark-surface-lighter transition-all">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button wire:click="confirmDelete('session', {{ $session->id }})" class="p-2 rounded-lg text-text-secondary hover:text-red-500 hover:bg-red-500/10 transition-all">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>

                        {{-- Prizes --}}
                        <div class="p-5">
                            @if($session->prizes->isEmpty())
                                <div class="text-center py-8">
                                    <p class="text-xs text-text-secondary mb-3">No prizes in this session yet.</p>
                                    <button wire:click="openAddPrize({{ $session->id }})" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-all inline-flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm">add</span> Add Prize
                                    </button>
                                </div>
                            @else
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @foreach($session->prizes as $prize)
                                        <div class="flex flex-col justify-between p-5 rounded-2xl bg-dark-surface border border-dark-border group hover:border-[#2563EB]/30 transition-all duration-300">
                                            <div>
                                                {{-- Card Header --}}
                                                <div class="flex items-start justify-between gap-3 mb-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="h-10 w-10 rounded-xl bg-amber-500/10 flex items-center justify-center shrink-0">
                                                            <span class="material-symbols-outlined text-amber-500">emoji_events</span>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <h4 class="text-sm font-bold text-text-primary truncate">{{ $prize->name }}</h4>
                                                            @if($prize->gift_description)
                                                                <p class="text-xs text-text-secondary truncate mt-0.5">{{ $prize->gift_description }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                        <button wire:click="openEditPrize({{ $prize->id }})" class="p-1.5 rounded-lg text-text-secondary hover:text-text-primary hover:bg-dark-surface-lighter transition-all" title="Edit Prize">
                                                            <span class="material-symbols-outlined text-sm">edit</span>
                                                        </button>
                                                        <button wire:click="confirmDelete('prize', {{ $prize->id }})" class="p-1.5 rounded-lg text-text-secondary hover:text-red-500 hover:bg-red-500/10 transition-all" title="Delete Prize">
                                                            <span class="material-symbols-outlined text-sm">delete</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Status & Info Badges --}}
                                                <div class="flex flex-wrap items-center gap-2 mb-4">
                                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-dark-surface-lighter text-text-secondary border border-dark-border">
                                                        Winners: {{ $prize->winners->count() }} / {{ $prize->max_winners }}
                                                    </span>
                                                    @if($prize->has_available_slots)
                                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/10">SLOTS AVAILABLE</span>
                                                    @else
                                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-red-500/10 text-red-500 border border-red-500/10">FULL</span>
                                                    @endif
                                                </div>

                                                {{-- Winners List --}}
                                                @if($prize->winners->isNotEmpty())
                                                    <div class="mt-4 pt-3 border-t border-dark-border/50">
                                                        <p class="text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-2">Winners</p>
                                                        <div class="space-y-1.5 max-h-36 overflow-y-auto no-scrollbar">
                                                            @foreach($prize->winners as $winner)
                                                                <div class="flex items-center justify-between text-xs bg-dark-surface-lighter px-2.5 py-1.5 rounded-lg transition-colors border border-dark-border">
                                                                    <span class="text-emerald-500 flex items-center gap-1.5 min-w-0">
                                                                        <span class="material-symbols-outlined text-xs shrink-0">trophy</span>
                                                                        <span class="truncate font-semibold">{{ $winner->registration->name ?? $winner->registration->full_name ?? 'Unknown' }}</span>
                                                                        <span class="text-text-secondary truncate text-[10px]">({{ $winner->registration->email ?? '' }})</span>
                                                                    </span>
                                                                    <button wire:click="removeWinner({{ $winner->id }})" wire:confirm="Remove this winner?" class="text-text-secondary hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity ml-1.5 shrink-0" title="Remove Winner">
                                                                        <span class="material-symbols-outlined text-xs">close</span>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Footer Action --}}
                                            @if($prize->has_available_slots)
                                                <div class="mt-5 pt-4 border-t border-dark-border/50">
                                                    <button wire:click="openRaffle({{ $session->id }}, {{ $prize->id }})" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all flex items-center justify-center gap-1.5 shadow-sm">
                                                        <span class="material-symbols-outlined text-sm">casino</span> Draw Winner
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                    {{-- Add Prize Card inside Grid --}}
                                    <button wire:click="openAddPrize({{ $session->id }})" class="flex flex-col items-center justify-center p-5 rounded-2xl border border-dashed border-dark-border hover:border-[#2563EB]/40 text-text-secondary hover:text-[#2563EB] bg-transparent hover:bg-[#2563EB]/5 transition-all duration-300 min-h-[180px]">
                                        <span class="material-symbols-outlined text-2xl mb-1.5">add_circle</span>
                                        <span class="text-xs font-bold">Add Prize</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

                {{-- Right Column: Sidebar Settings --}}
                <div class="space-y-6">
                    {{-- Live Display Card --}}
                    <div class="glass-panel rounded-2xl p-5 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-emerald-500">cast</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-text-primary">Live Display</h4>
                                <p class="text-[10px] text-text-secondary">Open on projector/big screen</p>
                            </div>
                        </div>
                        
                        <a href="{{ $this->displayUrl }}" target="_blank" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all flex items-center justify-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-sm">open_in_new</span> Open Display
                        </a>

                        <div class="pt-3 border-t border-dark-border/50">
                            <label class="block text-[10px] font-bold text-text-secondary uppercase tracking-wider mb-2">Display Link</label>
                            <div class="flex items-center gap-2">
                                <input type="text" value="{{ $this->displayUrl }}" readonly class="flex-1 h-9 rounded-xl border border-dark-border bg-console-input px-3 text-[10px] text-text-secondary font-mono"/>
                                <button onclick="navigator.clipboard.writeText('{{ $this->displayUrl }}')" class="p-2 rounded-xl text-text-secondary hover:text-text-primary border border-dark-border hover:border-[#2563EB]/30 transition-all flex items-center justify-center shrink-0" title="Copy Link">
                                    <span class="material-symbols-outlined text-sm">content_copy</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Display Background Settings --}}
                    <div class="glass-panel rounded-2xl p-5 space-y-4">
                        <div>
                            <h4 class="text-sm font-bold text-text-primary">Live Display Background</h4>
                            <p class="text-[10px] text-text-secondary">Customize the background image of projector display</p>
                        </div>

                        <div class="pt-3 border-t border-dark-border/50 space-y-3">
                            @if($event->doorprize_background)
                                <div class="relative rounded-xl overflow-hidden border border-dark-border">
                                    <img src="{{ asset('storage/' . $event->doorprize_background) }}" class="w-full h-24 object-cover"/>
                                    <button wire:click="removeBackground" wire:confirm="Remove background image?" class="absolute top-2 right-2 p-1.5 rounded-lg bg-red-600/90 text-white hover:bg-red-700 transition-all">
                                        <span class="material-symbols-outlined text-xs">delete</span>
                                    </button>
                                </div>
                            @endif

                            <div class="flex flex-col gap-2">
                                <input type="file" wire:model="backgroundUpload" accept="image/*" class="text-xs text-text-secondary file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-[#2563EB]/10 file:text-[#2563EB] hover:file:bg-[#2563EB]/20 file:cursor-pointer"/>
                                @if($backgroundUpload)
                                    <button wire:click="uploadBackground" class="w-full py-1.5 rounded-lg text-xs font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-all">Upload Background</button>
                                @endif
                            </div>
                            @error('backgroundUpload') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endif

    {{-- ═══ ELIGIBLE USERS TAB ═══ --}}
    @if($activeSubTab === 'eligible')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- Left/Main Column: Eligible Users Table & Exclusions --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Table Card --}}
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6 space-y-4">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <h3 class="text-base font-bold text-[#111827] dark:text-[#FCFCFC]">Eligible Users ({{ $this->eligibleUsers->total() }})</h3>
                            <p class="text-xs text-[#6F767E]">Currently eligible to win doorprizes based on global default requirements.</p>
                        </div>
                        <div class="relative w-full sm:w-64">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#6F767E] text-lg">search</span>
                            <input type="text" wire:model.live="eligibleSearch" placeholder="Search name or email..." class="h-11 w-full rounded-xl border-none bg-gray-50/50 dark:bg-[#0B0B0B]/20 pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"/>
                        </div>
                    </div>

                    @if($this->eligibleUsers->isEmpty())
                        <div class="text-center py-12 rounded-2xl bg-gray-50/10 dark:bg-[#0B0B0B]/10 border border-dashed border-gray-200 dark:border-[#272B30]">
                            <span class="material-symbols-outlined text-4xl text-[#6F767E] mb-2">group</span>
                            <p class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC]">No eligible participants found</p>
                            <p class="text-xs text-[#6F767E] mt-1">Check settings or search query</p>
                        </div>
                    @else
                        <div class="rounded-2xl border border-gray-200 dark:border-[#272B30] overflow-hidden bg-white dark:bg-[#1A1A1A]">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Participant</th>
                                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Check-In</th>
                                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Feedback</th>
                                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30">
                                    @foreach($this->eligibleUsers as $user)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                                            <td class="px-6 py-4 min-w-0">
                                                <div class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC] truncate">{{ $user->name ?? $user->full_name }}</div>
                                                <div class="text-xs text-[#6F767E] truncate mt-0.5">{{ $user->email }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($user->check_in)
                                                    <span class="inline-flex items-center rounded-lg bg-[#3F8C5826] text-[#83BF6E] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">Checked In</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-lg bg-gray-100 dark:bg-[#272B30] text-[#6F767E] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">No</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($user->feedback_submitted)
                                                    <span class="inline-flex items-center rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">Submitted</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-lg bg-gray-100 dark:bg-[#272B30] text-[#6F767E] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider">No</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <button wire:click="banRegistrationGlobally({{ $user->id }})" class="px-3 py-1.5 rounded-lg text-xs font-bold text-[#FF6A55] hover:bg-[#FF6A55]/10 transition-colors inline-flex items-center gap-1" title="Exclude from all sessions">
                                                    <span class="material-symbols-outlined text-sm">person_off</span> Exclude
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="pt-4 flex items-center justify-between">
                            <p class="text-sm font-medium text-[#6F767E]">
                                Showing {{ $this->eligibleUsers->firstItem() }} to {{ $this->eligibleUsers->lastItem() }} of {{ $this->eligibleUsers->total() }} participants
                            </p>
                            <div class="flex items-center gap-2">
                                @if($this->eligibleUsers->onFirstPage())
                                <button disabled class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] opacity-50 cursor-not-allowed">
                                    <span class="material-symbols-outlined text-xl">chevron_left</span>
                                </button>
                                @else
                                <button wire:click="previousPage('eligiblePage')" class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                                    <span class="material-symbols-outlined text-xl">chevron_left</span>
                                </button>
                                @endif

                                @foreach($this->eligibleUsers->getUrlRange(max(1, $this->eligibleUsers->currentPage() - 2), min($this->eligibleUsers->lastPage(), $this->eligibleUsers->currentPage() + 2)) as $page => $url)
                                    @if($page == $this->eligibleUsers->currentPage())
                                    <button class="h-10 w-10 rounded-xl bg-[#2563EB] text-white flex items-center justify-center text-sm font-bold shadow-lg shadow-blue-500/20">{{ $page }}</button>
                                    @else
                                    <button wire:click="gotoPage({{ $page }}, 'eligiblePage')" class="h-10 w-10 rounded-xl bg-white dark:bg-[#1A1A1A] flex items-center justify-center text-sm font-bold text-[#6F767E] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">{{ $page }}</button>
                                    @endif
                                @endforeach

                                @if($this->eligibleUsers->hasMorePages())
                                <button wire:click="nextPage('eligiblePage')" class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                                    <span class="material-symbols-outlined text-xl">chevron_right</span>
                                </button>
                                @else
                                <button disabled class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] opacity-50 cursor-not-allowed">
                                    <span class="material-symbols-outlined text-xl">chevron_right</span>
                                </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Excluded List Card (Separate, below the table) --}}
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6 space-y-4">
                    <div>
                        <h3 class="text-base font-bold text-[#111827] dark:text-[#FCFCFC]">Globally Excluded Participants ({{ $this->globalBans->count() }})</h3>
                        <p class="text-xs text-[#6F767E]">These users will be skipped in all drawing sessions.</p>
                    </div>

                    @if($this->globalBans->isEmpty())
                        <div class="text-center py-8 rounded-2xl bg-gray-50/10 dark:bg-[#0B0B0B]/10 border border-dashed border-gray-200 dark:border-[#272B30]">
                            <p class="text-xs text-[#6F767E]">No participants are currently excluded.</p>
                        </div>
                    @else
                        <div class="rounded-2xl border border-gray-200 dark:border-[#272B30] overflow-hidden bg-white dark:bg-[#1A1A1A]">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Participant</th>
                                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Email</th>
                                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30">
                                    @foreach($this->globalBans as $ban)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                                            <td class="px-6 py-4 font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $ban->name ?? $ban->full_name }}</td>
                                            <td class="px-6 py-4 text-sm text-[#6F767E]">{{ $ban->email }}</td>
                                            <td class="px-6 py-4 text-right">
                                                <button wire:click="unbanGlobally({{ $ban->id }})" class="px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-500 hover:bg-emerald-500/10 transition-colors inline-flex items-center gap-1" title="Include back in drawings">
                                                    <span class="material-symbols-outlined text-sm">check</span> Include
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Global Session Defaults Settings --}}
            <div class="space-y-6">
                {{-- Session Defaults Card --}}
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6 space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Global Session Defaults</h4>
                        <p class="text-xs text-[#6F767E]">Default settings for new raffle sessions</p>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-[#272B30]">
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.live="defaultRequireCheckin" class="custom-checkbox shrink-0 mt-0.5" />
                            <div>
                                <span class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] group-hover:text-[#2563EB] transition-colors">Require Check-In</span>
                                <p class="text-[10px] text-[#6F767E]">Only checked-in attendees are eligible by default.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" wire:model.live="defaultRequireFeedback" class="custom-checkbox shrink-0 mt-0.5" />
                            <div>
                                <span class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] group-hover:text-[#2563EB] transition-colors">Require Feedback</span>
                                <p class="text-[10px] text-[#6F767E]">Only attendees who submitted feedback are eligible by default.</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Status Summary Card --}}
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6 space-y-3">
                    <h4 class="text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Doorprize Pool Stats</h4>
                    <div class="space-y-2.5 pt-3 border-t border-gray-100 dark:border-[#272B30]">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[#6F767E]">Approved Attendees</span>
                            <span class="font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $event->approved_count }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[#6F767E]">Checked In Guests</span>
                            <span class="font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $event->checkedin_count }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs pt-2 border-t border-dashed border-gray-100 dark:border-[#272B30]">
                            <span class="text-[#6F767E] font-semibold">Eligible Pool</span>
                            <span class="font-bold text-emerald-500">{{ $this->eligibleUsers->total() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ ALL WINNERS TAB ═══ --}}
    @if($activeSubTab === 'winners')
        <div class="space-y-4">
            <div>
                <h3 class="text-base font-bold text-[#111827] dark:text-[#FCFCFC]">All Winners ({{ $this->allWinners->count() }})</h3>
                <p class="text-xs text-[#6F767E]">List of all participants who have won doorprizes.</p>
            </div>

            @if($this->allWinners->isEmpty())
                <div class="text-center py-12 rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30]">
                    <span class="material-symbols-outlined text-4xl text-[#6F767E] mb-2">trophy</span>
                    <p class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC]">No winners drawn yet</p>
                </div>
            @else
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden relative">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                                    <th class="px-8 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Winner</th>
                                    <th class="px-6 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Email</th>
                                    <th class="px-6 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Prize</th>
                                    <th class="px-6 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Session</th>
                                    <th class="px-8 py-5 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Won At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30">
                                @foreach($this->allWinners as $w)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                                        <td class="px-8 py-4 text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $w->registration->name ?? $w->registration->full_name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-[#6F767E]">{{ $w->registration->email ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-[#111827] dark:text-[#FCFCFC]">{{ $w->prize->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-[#6F767E]">{{ $w->prize->session->name ?? '-' }}</td>
                                        <td class="px-8 py-4 text-sm text-[#6F767E] text-right">{{ $w->won_at?->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ═══ SESSION MODAL ═══ --}}
    @if($showSessionModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4" wire:click.self="$set('showSessionModal', false)">
            <div class="bg-dark-surface border border-dark-border rounded-2xl max-w-lg w-full p-6 shadow-xl text-text-primary" @click.stop>
                <h3 class="text-lg font-bold text-text-primary mb-4">{{ $editingSessionId ? 'Edit' : 'Add' }} Session</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Session Name *</label>
                        <input type="text" wire:model="sessionName" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary focus:ring-1 focus:ring-[#2563EB]" placeholder="e.g. Main Raffle"/>
                        @error('sessionName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="sessionRequireCheckin" id="reqCheckinDp" class="rounded border-dark-border"/>
                        <label for="reqCheckinDp" class="text-sm text-text-primary">Require check-in to be eligible</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="sessionRequireFeedback" id="reqFeedbackDp" class="rounded border-dark-border"/>
                        <label for="reqFeedbackDp" class="text-sm text-text-primary">Require feedback submission to be eligible</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showSessionModal', false)" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary">Cancel</button>
                    <button wire:click="saveSession" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-all">{{ $editingSessionId ? 'Update' : 'Create' }} Session</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ PRIZE MODAL ═══ --}}
    @if($showPrizeModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4" wire:click.self="$set('showPrizeModal', false)">
            <div class="bg-dark-surface border border-dark-border rounded-2xl max-w-lg w-full p-6 shadow-xl text-text-primary" @click.stop>
                <h3 class="text-lg font-bold text-text-primary mb-4">{{ $editingPrizeId ? 'Edit' : 'Add' }} Prize</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Prize Name *</label>
                        <input type="text" wire:model="prizeName" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary focus:ring-1 focus:ring-[#2563EB]" placeholder="e.g. iPad Air"/>
                        @error('prizeName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Description</label>
                        <textarea wire:model="prizeDescription" rows="2" class="w-full rounded-xl border border-dark-border bg-console-input px-4 py-3 text-sm text-text-primary resize-none focus:ring-1 focus:ring-[#2563EB]" placeholder="Prize description..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-text-primary mb-1.5">Max Winners *</label>
                        <input type="number" wire:model="prizeMaxWinners" min="1" class="w-full h-12 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary focus:ring-1 focus:ring-[#2563EB]"/>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showPrizeModal', false)" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary">Cancel</button>
                    <button wire:click="savePrize" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-all">{{ $editingPrizeId ? 'Update' : 'Add' }} Prize</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ RAFFLE MODAL ═══ --}}
    @if($showRaffleModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl max-w-md w-full p-6 shadow-xl text-text-primary text-center">
                @if($raffleResult && isset($raffleResult['error']))
                    <div class="h-16 w-16 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-red-500">error</span>
                    </div>
                    <h3 class="text-lg font-bold text-text-primary mb-2">Cannot Draw</h3>
                    <p class="text-sm text-text-secondary">{{ $raffleResult['error'] }}</p>
                @elseif($raffleResult && isset($raffleResult['success']))
                    <div class="h-16 w-16 rounded-full bg-amber-500/10 flex items-center justify-center mx-auto mb-4 animate-bounce">
                        <span class="material-symbols-outlined text-3xl text-amber-500">trophy</span>
                    </div>
                    <h3 class="text-lg font-bold text-text-primary mb-1">🎉 Winner!</h3>
                    <p class="text-2xl font-extrabold text-[#2563EB] my-3">{{ $raffleResult['name'] }}</p>
                    <p class="text-sm text-text-secondary">{{ $raffleResult['email'] }}</p>
                    @if($raffleResult['organization'])
                        <p class="text-xs text-text-secondary mt-0.5">{{ $raffleResult['organization'] }}</p>
                    @endif
                    <div class="mt-4 p-3 rounded-xl bg-dark-surface-lighter text-xs text-text-secondary">
                        Prize: <span class="text-text-primary font-bold">{{ $raffleResult['prize'] }}</span>
                        · Remaining slots: {{ $raffleResult['remaining'] }}
                        · Pool size was: {{ $raffleResult['poolSize'] }}
                    </div>
                @else
                    <div class="h-16 w-16 rounded-full bg-[#2563EB]/10 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-[#2563EB]">casino</span>
                    </div>
                    <h3 class="text-lg font-bold text-text-primary mb-2">Ready to Draw</h3>
                    <p class="text-sm text-text-secondary mb-6">Click the button below to randomly select a winner from the eligible pool.</p>
                @endif

                <div class="flex justify-center gap-3 mt-6">
                    <button wire:click="$set('showRaffleModal', false)" class="px-5 py-2 rounded-xl text-sm font-semibold text-text-secondary hover:text-text-primary transition-all">Close</button>
                    @if(!$raffleResult || (isset($raffleResult['success']) && ($raffleResult['remaining'] ?? 0) > 0))
                        <button wire:click="drawWinner" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">casino</span>
                            {{ $raffleResult ? 'Draw Again' : 'Draw Winner' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ BAN MODAL ═══ --}}
    @if($showBanModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4" wire:click.self="$set('showBanModal', false)">
            <div class="bg-dark-surface border border-dark-border rounded-2xl max-w-lg w-full max-h-[80vh] flex flex-col shadow-xl text-text-primary" @click.stop>
                <div class="p-6 border-b border-dark-border">
                    <h3 class="text-lg font-bold text-text-primary">Manage Exclusions</h3>
                    <p class="text-xs text-text-secondary mt-1">Exclude specific participants from this raffle session</p>
                </div>
                <div class="p-6 overflow-y-auto space-y-4">
                    {{-- Search to add --}}
                    <div>
                        <input type="text" wire:model.live.debounce.300ms="banSearch" class="w-full h-10 rounded-xl border border-dark-border bg-console-input px-4 text-sm text-text-primary" placeholder="Search participant to exclude..."/>
                        @if($this->banCandidates->isNotEmpty())
                            <div class="mt-2 border border-dark-border rounded-xl divide-y divide-dark-border overflow-hidden">
                                @foreach($this->banCandidates as $candidate)
                                    <div class="flex items-center justify-between p-3 hover:bg-dark-surface-lighter">
                                        <div>
                                            <span class="text-sm text-text-primary">{{ $candidate->name ?? $candidate->full_name }}</span>
                                            <span class="text-xs text-text-secondary ml-2">{{ $candidate->email }}</span>
                                        </div>
                                        <button wire:click="banRegistration({{ $candidate->id }})" class="px-3 py-1 rounded-lg text-xs font-bold text-red-500 bg-red-500/10 hover:bg-red-500/20 transition-all">Exclude</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Current bans --}}
                    @php $session = $this->sessions->firstWhere('id', $banSessionId); @endphp
                    @if($session && $session->bans->isNotEmpty())
                        <div>
                            <h4 class="text-xs font-bold text-text-secondary uppercase mb-2">Currently Excluded ({{ $session->bans->count() }})</h4>
                            <div class="space-y-1">
                                @foreach($session->bans as $ban)
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-dark-surface-lighter">
                                        <span class="text-sm text-text-primary">{{ $ban->registration->name ?? $ban->registration->full_name ?? 'Unknown' }} <span class="text-text-secondary text-xs">{{ $ban->registration->email ?? '' }}</span></span>
                                        <button wire:click="unban({{ $ban->id }})" class="text-xs text-emerald-500 hover:underline">Re-include</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="p-4 border-t border-dark-border flex justify-end">
                    <button wire:click="$set('showBanModal', false)" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary hover:text-text-primary">Done</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ DELETE MODAL ═══ --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4">
            <div class="bg-dark-surface border border-dark-border rounded-2xl max-w-sm w-full p-6 shadow-xl text-text-primary">
                <h3 class="text-lg font-bold text-text-primary mb-2">Delete {{ ucfirst($deleteType) }}?</h3>
                <p class="text-sm text-text-secondary mb-6">This will permanently remove this {{ $deleteType }} and all associated data.</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl text-sm font-semibold text-text-secondary">Cancel</button>
                    <button wire:click="deleteItem" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
