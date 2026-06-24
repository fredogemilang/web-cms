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
                                <p class="text-xs text-text-secondary text-center py-4">No prizes in this session</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($session->prizes as $prize)
                                        <div class="flex items-center gap-4 p-4 rounded-xl bg-dark-surface border border-dark-border group hover:border-[#2563EB]/30 transition-all">
                                            <div class="h-10 w-10 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-amber-500">emoji_events</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-bold text-text-primary">{{ $prize->name }}</h4>
                                                @if($prize->gift_description)
                                                    <p class="text-xs text-text-secondary truncate">{{ $prize->gift_description }}</p>
                                                @endif
                                                <div class="flex items-center gap-3 mt-1">
                                                    <span class="text-[10px] text-text-secondary">Winners: {{ $prize->winners->count() }} / {{ $prize->max_winners }}</span>
                                                    @if($prize->has_available_slots)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-500">SLOTS AVAILABLE</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-500">FULL</span>
                                                    @endif
                                                </div>
                                                {{-- Winners list --}}
                                                @if($prize->winners->isNotEmpty())
                                                    <div class="mt-2 space-y-1">
                                                        @foreach($prize->winners as $winner)
                                                            <div class="flex items-center justify-between text-xs">
                                                                <span class="text-emerald-500 flex items-center gap-1">
                                                                    <span class="material-symbols-outlined text-xs">trophy</span>
                                                                    {{ $winner->registration->name ?? $winner->registration->full_name ?? 'Unknown' }}
                                                                    <span class="text-text-secondary">({{ $winner->registration->email ?? '' }})</span>
                                                                </span>
                                                                <button wire:click="removeWinner({{ $winner->id }})" wire:confirm="Remove this winner?" class="text-text-secondary hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                                                    <span class="material-symbols-outlined text-xs">close</span>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                @if($prize->has_available_slots)
                                                    <button wire:click="openRaffle({{ $session->id }}, {{ $prize->id }})" class="px-3 py-2 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-sm">casino</span> Draw
                                                    </button>
                                                @endif
                                                <button wire:click="openEditPrize({{ $prize->id }})" class="p-2 rounded-lg text-text-secondary hover:text-text-primary hover:bg-dark-surface-lighter transition-all opacity-0 group-hover:opacity-100">
                                                    <span class="material-symbols-outlined text-sm">edit</span>
                                                </button>
                                                <button wire:click="confirmDelete('prize', {{ $prize->id }})" class="p-2 rounded-lg text-text-secondary hover:text-red-500 hover:bg-red-500/10 transition-all opacity-0 group-hover:opacity-100">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <button wire:click="openAddPrize({{ $session->id }})" class="mt-3 w-full py-2 rounded-xl text-xs font-semibold text-text-secondary hover:text-text-primary border border-dashed border-dark-border hover:border-[#2563EB]/30 transition-all flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-sm">add</span> Add Prize
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    {{-- ═══ ALL WINNERS TAB ═══ --}}
    @elseif($activeSubTab === 'winners')
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
