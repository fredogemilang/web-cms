<div>
        {{-- Sub-tab Navigation --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex gap-2">
                @foreach(['sessions' => 'Sessions & Prizes', 'winners' => 'All Winners'] as $key => $label)
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

                    {{-- Global Defaults --}}
                    <div class="glass-panel rounded-2xl p-5 space-y-4">
                        <div>
                            <h4 class="text-sm font-bold text-text-primary">Global Session Defaults</h4>
                            <p class="text-[10px] text-text-secondary">Default settings for new raffle sessions</p>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-dark-border/50">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model.live="defaultRequireCheckin" class="custom-checkbox shrink-0 mt-0.5" />
                                <div>
                                    <span class="text-xs font-bold text-text-primary group-hover:text-white transition-colors">Require Check-In</span>
                                    <p class="text-[10px] text-text-secondary">Only checked-in attendees are eligible by default.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 cursor-pointer group mt-3">
                                <input type="checkbox" wire:model.live="defaultRequireFeedback" class="custom-checkbox shrink-0 mt-0.5" />
                                <div>
                                    <span class="text-xs font-bold text-text-primary group-hover:text-white transition-colors">Require Feedback</span>
                                    <p class="text-[10px] text-text-secondary">Only attendees who submitted feedback are eligible by default.</p>
                                </div>
                            </label>
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

                    {{-- Global Exclusions --}}
                    <div class="glass-panel rounded-2xl p-5 space-y-4">
                        <div>
                            <h4 class="text-sm font-bold text-text-primary">Global Exclusions</h4>
                            <p class="text-[10px] text-text-secondary">Exclude specific participants from all drawings</p>
                        </div>

                        <div class="pt-3 border-t border-dark-border/50 space-y-3">
                            {{-- Search Input --}}
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-sm">search</span>
                                <input type="text" wire:model.live="globalBanSearch" placeholder="Search name or email..." class="w-full h-9 rounded-xl border border-dark-border bg-console-input pl-9 pr-4 text-xs text-text-primary outline-none focus:border-[#2563EB]/50 transition-all"/>
                            </div>

                            {{-- Search Results --}}
                            @if($globalBanSearch)
                                @if($this->globalBanCandidates->isEmpty())
                                    <p class="text-[10px] text-text-secondary text-center py-2">No matching participants found</p>
                                @else
                                    <div class="rounded-xl border border-dark-border bg-dark-surface p-2 space-y-1.5 max-h-40 overflow-y-auto no-scrollbar">
                                        @foreach($this->globalBanCandidates as $reg)
                                            <div class="flex items-center justify-between gap-2 p-1.5 rounded-lg hover:bg-dark-surface-lighter transition-colors">
                                                <div class="min-w-0">
                                                    <p class="text-[10px] font-bold text-text-primary truncate">{{ $reg->name ?? $reg->full_name }}</p>
                                                    <p class="text-[9px] text-text-secondary truncate">{{ $reg->email }}</p>
                                                </div>
                                                <button wire:click="banRegistrationGlobally({{ $reg->id }})" class="p-1 rounded-lg text-emerald-500 hover:bg-emerald-500/10 transition-colors shrink-0" title="Exclude">
                                                    <span class="material-symbols-outlined text-xs">add</span>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            {{-- Current Banned List --}}
                            @if($this->globalBans->isNotEmpty())
                                <div class="pt-2">
                                    <label class="block text-[9px] font-bold text-text-secondary uppercase tracking-wider mb-2">Excluded Participants ({{ $this->globalBans->count() }})</label>
                                    <div class="space-y-1.5 max-h-48 overflow-y-auto no-scrollbar">
                                        @foreach($this->globalBans as $ban)
                                            <div class="flex items-center justify-between gap-2 bg-dark-surface-lighter px-2.5 py-1.5 rounded-lg border border-dark-border">
                                                <div class="min-w-0">
                                                    <p class="text-[10px] font-bold text-text-primary truncate">{{ $ban->name ?? $ban->full_name }}</p>
                                                    <p class="text-[9px] text-text-secondary truncate">{{ $ban->email }}</p>
                                                </div>
                                                <button wire:click="unbanGlobally({{ $ban->id }})" class="p-1 rounded-lg text-text-secondary hover:text-red-500 hover:bg-red-500/10 transition-all shrink-0" title="Include back">
                                                    <span class="material-symbols-outlined text-xs">close</span>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-[10px] text-text-secondary text-center py-4 bg-dark-surface/30 rounded-xl border border-dashed border-dark-border/50">No global exclusions set</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

    {{-- ═══ ALL WINNERS TAB ═══ --}}
    @if($activeSubTab === 'winners')
        <div class="glass-panel rounded-2xl p-6">
            <h3 class="text-base font-bold text-text-primary mb-4">All Winners ({{ $this->allWinners->count() }})</h3>
            @if($this->allWinners->isEmpty())
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-4xl text-text-secondary mb-2">trophy</span>
                    <p class="text-sm font-semibold text-text-primary">No winners drawn yet</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-dark-border">
                    <table class="w-full text-sm text-text-primary border-collapse">
                        <thead>
                            <tr class="border-b border-dark-border bg-dark-surface-lighter">
                                <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Winner</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Prize</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Session</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-text-secondary uppercase">Won At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border">
                            @foreach($this->allWinners as $w)
                                <tr class="hover:bg-dark-surface-lighter transition-colors">
                                    <td class="px-4 py-3 font-medium">{{ $w->registration->name ?? $w->registration->full_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-text-secondary">{{ $w->registration->email ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $w->prize->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-text-secondary">{{ $w->prize->session->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-text-secondary text-xs">{{ $w->won_at?->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
