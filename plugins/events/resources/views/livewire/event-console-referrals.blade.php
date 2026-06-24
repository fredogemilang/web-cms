<div class="space-y-8 text-text-primary" x-data="{ showCreateModal: @entangle('showCreateModal'), showDeleteModal: @entangle('showDeleteModal') }">
    
    {{-- ─── SUMMARY CARDS ROW ─── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Referral Signups --}}
        <div class="glass-panel rounded-2xl p-5 flex flex-col justify-between hover:border-blue-500/40 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-extrabold text-text-secondary uppercase tracking-wider">Referral Signups</span>
                <div class="h-8 w-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-base">link</span>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-extrabold text-text-primary">
                    {{ $this->summaryStats['total'] }}
                </h3>
                <p class="text-[10px] text-text-secondary mt-1">Registrations via referral links</p>
            </div>
        </div>

        {{-- Approved Referral Signups --}}
        <div class="glass-panel rounded-2xl p-5 flex flex-col justify-between hover:border-emerald-500/40 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-extrabold text-text-secondary uppercase tracking-wider">Approved Referrals</span>
                <div class="h-8 w-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-extrabold text-text-primary">
                    {{ $this->summaryStats['approved'] }}
                </h3>
                <p class="text-[10px] text-text-secondary mt-1">
                    Approval Rate: {{ $this->summaryStats['total'] > 0 ? round(($this->summaryStats['approved'] / $this->summaryStats['total']) * 100) : 0 }}%
                </p>
            </div>
        </div>

        {{-- Checked-in Referral Signups --}}
        <div class="glass-panel rounded-2xl p-5 flex flex-col justify-between hover:border-indigo-500/40 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-extrabold text-text-secondary uppercase tracking-wider">Checked-in Referrals</span>
                <div class="h-8 w-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-extrabold text-text-primary">
                    {{ $this->summaryStats['checked_in'] }}
                </h3>
                <p class="text-[10px] text-text-secondary mt-1">
                    Attendance Rate: {{ $this->summaryStats['approved'] > 0 ? round(($this->summaryStats['checked_in'] / $this->summaryStats['approved']) * 100) : 0 }}%
                </p>
            </div>
        </div>

        {{-- Top Performing Campaign --}}
        <div class="glass-panel rounded-2xl p-5 flex flex-col justify-between hover:border-amber-500/40 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-extrabold text-text-secondary uppercase tracking-wider">Top Campaign</span>
                <div class="h-8 w-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-base">military_tech</span>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-text-primary truncate max-w-[200px]" title="{{ $this->summaryStats['top_campaign'] }}">
                    {{ $this->summaryStats['top_campaign'] }}
                </h3>
                <p class="text-[10px] text-text-secondary mt-1">
                    @if($this->summaryStats['top_campaign_count'] > 0)
                        Attributed signups: <span class="font-bold text-amber-500">{{ $this->summaryStats['top_campaign_count'] }}</span>
                    @else
                        No campaigns active
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ─── MAIN TABLES GRID ─── --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        
        {{-- Campaign Links Management (8 Columns) --}}
        <div class="xl:col-span-8 space-y-6">
            <div class="glass-panel rounded-2xl p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-text-primary">Campaign Tracking Links</h2>
                        <p class="text-xs text-text-secondary mt-0.5">Create custom registration tracking URLs for newsletter campaigns, sponsors, or partners.</p>
                    </div>
                    <button wire:click="openCreateModal()" type="button"
                        class="px-4 py-2 bg-primary hover:bg-blue-600 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-md">
                        <span class="material-symbols-outlined text-sm">add_link</span>
                        <span>Create Link</span>
                    </button>
                </div>

                <div class="overflow-hidden border border-dark-border rounded-xl">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-dark-border text-text-secondary text-xs uppercase font-semibold bg-dark-surface/50">
                                <th class="py-3 px-4">Code</th>
                                <th class="py-3 px-4">Campaign Name</th>
                                <th class="py-3 px-4">Referral Parameters</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border">
                            @forelse($this->trackingCodes as $code)
                            <tr class="hover:bg-dark-surface/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-1 rounded bg-[#2563EB]/10 text-[#2563EB] text-xs font-mono font-bold">{{ $code->tracking_code }}</span>
                                    @if($code->description)
                                        <div class="text-[10px] text-text-secondary mt-1 max-w-[150px] truncate" title="{{ $code->description }}">{{ $code->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-xs text-text-primary">{{ $code->source }}</td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="flex items-center gap-2" x-data="{ copied: false }">
                                        <span class="text-xs font-mono text-text-secondary font-medium select-all">?ref={{ $code->tracking_code }}</span>
                                        <button type="button" 
                                            @click="navigator.clipboard.writeText('{{ route('events.show', $event->slug) }}?ref={{ $code->tracking_code }}'); copied = true; setTimeout(() => copied = false, 2000); $dispatch('notify', { message: 'Campaign link copied!' })"
                                            class="px-2.5 py-1 rounded-lg border border-dark-border bg-dark-surface-lighter hover:bg-[#2563EB]/10 hover:border-[#2563EB] hover:text-[#2563EB] text-[10px] font-bold flex items-center gap-1 transition-all"
                                            :title="copied ? 'Copied!' : 'Copy Campaign Link'">
                                            <span class="material-symbols-outlined text-xs" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
                                            <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <button wire:click="toggleActive({{ $code->id }})" type="button"
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold border transition-all hover:scale-105
                                            {{ $code->is_active 
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 hover:border-emerald-500/40' 
                                                : 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20 hover:border-zinc-500/40' }}">
                                        {{ $code->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2 text-text-secondary">
                                        <button wire:click="openCreateModal({{ $code->id }})" type="button" title="Edit" class="p-1 hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <button wire:click="confirmDelete({{ $code->id }})" type="button" title="Delete" class="p-1 hover:text-danger transition-colors">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-text-secondary text-xs">
                                    <span class="material-symbols-outlined text-3xl text-text-secondary/40 block mb-2">link_off</span>
                                    No custom tracking codes created yet. Click "Create Link" to get started.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Traffic Attribution Analytics (4 Columns) --}}
        <div class="xl:col-span-4 space-y-6">
            <div class="glass-panel rounded-2xl p-6 space-y-6">
                <div>
                    <h2 class="text-base font-bold text-text-primary">Traffic Attribution</h2>
                    <p class="text-xs text-text-secondary mt-0.5">Real-time statistics of registrants categorized by their referral code or UTM parameters.</p>
                </div>

                <div class="border border-dark-border rounded-xl overflow-hidden">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-dark-border text-text-secondary text-xs uppercase font-semibold bg-dark-surface/50">
                                <th class="py-3 px-4">Source / Code</th>
                                <th class="py-3 px-4 text-center">Registrants</th>
                                <th class="py-3 px-4 text-center">Approved</th>
                                <th class="py-3 px-4 text-center">Checked In</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border text-xs">
                            @forelse($this->referralStats as $stat)
                            <tr class="hover:bg-dark-surface/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-text-primary">{{ $stat->referral_source ?? 'Direct' }}</div>
                                    @if($stat->referral_code && $stat->referral_code !== $stat->referral_source)
                                        <div class="text-[10px] text-text-secondary mt-0.5 font-mono">Code: {{ $stat->referral_code }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-text-primary">{{ $stat->total_count }}</td>
                                <td class="py-3.5 px-4 text-center text-emerald-600 dark:text-emerald-400 font-semibold">{{ $stat->approved_count }}</td>
                                <td class="py-3.5 px-4 text-center text-indigo-600 dark:text-indigo-400 font-semibold">{{ $stat->checked_in_count }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-text-secondary">
                                    <span class="material-symbols-outlined text-3xl text-text-secondary/40 block mb-2">bar_chart</span>
                                    No registrations recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── CREATE/EDIT MODAL ─── --}}
    <div x-show="showCreateModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak>
        
        <div class="bg-dark-surface border border-dark-border rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col"
            @click.away="showCreateModal = false">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-dark-border flex items-center justify-between">
                <h3 class="text-sm font-bold text-text-primary flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">link</span>
                    <span>{{ $editingId ? 'Edit Campaign Link' : 'Create Campaign Link' }}</span>
                </h3>
                <button @click="showCreateModal = false" type="button" class="text-text-secondary hover:text-text-primary">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit.prevent="saveTrackingCode" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-text-secondary uppercase mb-2">Tracking Code <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="trackingCode" 
                        class="w-full bg-console-input border border-dark-border text-text-primary text-xs rounded-xl p-3 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary block font-mono" 
                        placeholder="e.g. FB-ADS-2026">
                    @error('trackingCode')
                        <p class="text-red-500 text-[10px] mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-text-secondary uppercase mb-2">Campaign Name / Source <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="source" 
                        class="w-full bg-console-input border border-dark-border text-text-primary text-xs rounded-xl p-3 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary block" 
                        placeholder="e.g. Facebook Ads, Sponsor Banner, Partner Name">
                    @error('source')
                        <p class="text-red-500 text-[10px] mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-text-secondary uppercase mb-2">Description</label>
                    <textarea wire:model="description" rows="3"
                        class="w-full bg-console-input border border-dark-border text-text-primary text-xs rounded-xl p-3 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary block" 
                        placeholder="Optional details or context..."></textarea>
                    @error('description')
                        <p class="text-red-500 text-[10px] mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2.5 pt-2">
                    <input type="checkbox" id="isActive" wire:model="isActive" 
                        class="rounded border-dark-border bg-dark-surface text-primary focus:ring-primary h-4 w-4">
                    <label for="isActive" class="text-xs font-bold text-text-primary cursor-pointer select-none">Active (Registrations using this code will be tracked)</label>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-dark-border mt-6">
                    <button @click="showCreateModal = false" type="button"
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold text-text-secondary hover:text-text-primary bg-dark-surface-lighter border border-dark-border transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-primary hover:bg-blue-600 shadow-lg shadow-blue-900/20 transition-all">
                        Save Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── DELETE MODAL ─── --}}
    <div x-show="showDeleteModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-cloak>
        <div class="bg-dark-surface border border-dark-border rounded-2xl w-full max-w-md shadow-2xl p-6 space-y-6"
            @click.away="showDeleteModal = false">
            <div class="flex items-center gap-3 text-red-500">
                <span class="material-symbols-outlined text-3xl">warning</span>
                <div>
                    <h3 class="text-sm font-bold text-text-primary">Delete Campaign Link</h3>
                    <p class="text-xs text-text-secondary mt-0.5">Are you sure you want to delete this campaign link? Any future registrations with this code will not be matched.</p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button @click="showDeleteModal = false" type="button"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-text-secondary bg-dark-surface-lighter border border-dark-border">
                    Cancel
                </button>
                <button wire:click="deleteTrackingCode" type="button"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
