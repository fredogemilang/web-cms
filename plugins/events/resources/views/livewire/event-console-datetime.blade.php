<div class="space-y-8" x-on:console-save.window="$wire.save()">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined text-lg">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Main Settings (Date & Time + Success Page) -->
        <div class="lg:col-span-2 space-y-6">
            {{-- Date & Schedule Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-5">
                <h3 class="text-sm font-bold text-text-primary mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-[#2563EB]">event</span>
                    Date & Schedule
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Start Date *</label>
                        <input wire:model="start_date" type="date"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 dark:[color-scheme:dark]">
                        @error('start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Start Time *</label>
                        <input wire:model="start_time" type="time"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 dark:[color-scheme:dark]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">End Date</label>
                        <input wire:model="end_date" type="date"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 dark:[color-scheme:dark]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">End Time</label>
                        <input wire:model="end_time" type="time"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 dark:[color-scheme:dark]">
                    </div>
                </div>

                <div class="flex items-center gap-3 py-2">
                    <input wire:model="is_all_day" type="checkbox" id="is_all_day_revamp" class="w-4 h-4 rounded border-dark-border bg-dark-surface text-[#2563EB] focus:ring-[#2563EB]">
                    <label for="is_all_day_revamp" class="text-xs font-bold text-text-primary uppercase tracking-wide cursor-pointer">All-day event</label>
                </div>

                <div class="space-y-1 pt-2">
                    <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Timezone</label>
                    <select wire:model="timezone"
                        class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3">
                        @foreach($timezones as $tz)
                            <option value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Registration Period --}}
                @if($requires_registration)
                <div class="border-t border-dark-border pt-4 mt-2 space-y-4">
                    <h4 class="text-xs font-bold text-text-primary uppercase tracking-wider">Registration Period</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs text-text-secondary mb-1">Registration Open Date</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input wire:model="registration_start_date" type="date"
                                    class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 dark:[color-scheme:dark]">
                                <input wire:model="registration_start_time" type="time"
                                    class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 dark:[color-scheme:dark]">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-text-secondary mb-1">Registration End Date (Deadline)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input wire:model="registration_end_date" type="date"
                                    class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 dark:[color-scheme:dark]">
                                <input wire:model="registration_end_time" type="time"
                                    class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 dark:[color-scheme:dark]">
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Success Page Configuration Card --}}
            @if($requires_registration)
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h3 class="text-sm font-bold text-text-primary mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-[#2563EB]">celebration</span>
                    Success Page Configuration
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Success Title</label>
                        <input wire:model="success_title" type="text"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"
                            placeholder="e.g. Registration Successful!">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Success Description</label>
                        <textarea wire:model="success_desc" rows="3"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-4 resize-none"
                            placeholder="Thank you for registering..."></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Button Text</label>
                            <input wire:model="success_button" type="text"
                                class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"
                                placeholder="e.g. Back to Event">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Link Type</label>
                            <select wire:model.live="success_link_type"
                                class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3">
                                <option value="event">Event Page (default)</option>
                                <option value="custom">Custom URL</option>
                            </select>
                        </div>
                    </div>
                    @if($success_link_type === 'custom')
                    <div>
                        <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Custom URL</label>
                        <input wire:model="success_link" type="url"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"
                            placeholder="https://...">
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Registration & Access Settings -->
        <div class="space-y-6">
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h3 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-lg">rule</span>
                    Registration & Access
                </h3>

                <div class="space-y-3">
                    <div class="divide-y divide-dark-border">
                        <!-- Requires Registration Toggle -->
                        <div class="flex items-center justify-between py-2.5">
                            <div>
                                <span class="text-xs font-bold text-text-primary block">Requires Registration</span>
                                <span class="text-[10px] text-text-secondary">Open to public if disabled</span>
                            </div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="requires_registration" id="requires_registration" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-gray-200 dark:bg-zinc-600 peer-focus:outline-none rounded-full peer peer-checked:bg-[#2563EB] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        <!-- Limit by Quota Toggle -->
                        @if($requires_registration)
                        <div class="flex items-center justify-between py-2.5">
                            <div>
                                <span class="text-xs font-bold text-text-primary block">Limit by Quota</span>
                                <span class="text-[10px] text-text-secondary">Cap total number of registrants</span>
                            </div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="limit_by_quota" id="limit_by_quota" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-gray-200 dark:bg-zinc-600 peer-focus:outline-none rounded-full peer peer-checked:bg-[#2563EB] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>
                        @endif
                    </div>

                    <!-- Max Capacity Quota Input -->
                    @if($requires_registration && $limit_by_quota)
                    <div class="space-y-1 pb-1.5 mt-2">
                        <label class="block text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Max Capacity Quota</label>
                        <input wire:model="max_participants" type="number" min="1"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl p-3 focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block"
                            placeholder="Leave empty for unlimited">
                    </div>
                    @endif

                    <div class="divide-y divide-dark-border">
                        <!-- Manual Admin Approval Toggle -->
                        <div class="flex items-center justify-between py-2.5">
                            <div>
                                <span class="text-xs font-bold text-text-primary block">Manual Admin Approval</span>
                                <span class="text-[10px] text-text-secondary">Review queue before confirm</span>
                            </div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="registration_requires_approval" id="registration_requires_approval" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-gray-200 dark:bg-zinc-600 peer-focus:outline-none rounded-full peer peer-checked:bg-[#2563EB] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        <!-- Corporate Email Only Toggle -->
                        <div class="flex items-center justify-between py-2.5">
                            <div>
                                <span class="text-xs font-bold text-text-primary block">Corporate Email Only</span>
                                <span class="text-[10px] text-text-secondary">Reject personal domains</span>
                            </div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="requires_corporate_email" id="requires_corporate_email" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-gray-200 dark:bg-zinc-600 peer-focus:outline-none rounded-full peer peer-checked:bg-[#2563EB] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
