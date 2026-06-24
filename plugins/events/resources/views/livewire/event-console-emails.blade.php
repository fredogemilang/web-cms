<div class="space-y-8" x-on:console-save.window="$wire.save()">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined text-lg">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Sender Credentials & Status Templates -->
        <div class="lg:col-span-2 space-y-6">
            {{-- Email Sender Credentials Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#2563EB] text-lg">outgoing_mail</span>
                        Email Sender Credentials
                    </h3>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="sending_email" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 dark:bg-zinc-600 peer-focus:outline-none rounded-full peer peer-checked:bg-[#2563EB] transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Sender Name</label>
                        <input type="text" wire:model="sender_name" {{ !$sending_email ? 'disabled' : '' }}
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 disabled:opacity-50"
                            placeholder="e.g. Committee Name"/>
                        @error('sender_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Sender Email Address</label>
                        <input type="email" wire:model="sender_email" {{ !$sending_email ? 'disabled' : '' }}
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 disabled:opacity-50"
                            placeholder="e.g. events@domain.com"/>
                        @error('sender_email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1 col-span-1 md:col-span-2">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">CC Notifications to Email</label>
                        <input type="text" wire:model="cc_to_email" {{ !$sending_email ? 'disabled' : '' }}
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 disabled:opacity-50"
                            placeholder="e.g. info@domain.com (comma-separated)"/>
                        @error('cc_to_email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Status Response Templates Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-5">
                <h3 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-lg">drafts</span>
                    Status Response Templates
                </h3>

                <!-- Inner tabs for pending / approved / rejected templates -->
                <div class="flex border-b border-dark-border gap-2">
                    <button wire:click="$set('activeEmailTab', 'pending')"
                        class="pb-2 border-b-2 text-xs font-bold uppercase tracking-wider px-3 transition-colors
                            {{ $activeEmailTab === 'pending'
                                ? 'border-[#2563EB] text-text-primary'
                                : 'border-transparent text-text-secondary hover:text-text-primary' }}">
                        Pending
                    </button>
                    <button wire:click="$set('activeEmailTab', 'approved')"
                        class="pb-2 border-b-2 text-xs font-bold uppercase tracking-wider px-3 transition-colors
                            {{ $activeEmailTab === 'approved'
                                ? 'border-[#2563EB] text-text-primary'
                                : 'border-transparent text-text-secondary hover:text-text-primary' }}">
                        Approved
                    </button>
                    <button wire:click="$set('activeEmailTab', 'rejected')"
                        class="pb-2 border-b-2 text-xs font-bold uppercase tracking-wider px-3 transition-colors
                            {{ $activeEmailTab === 'rejected'
                                ? 'border-[#2563EB] text-text-primary'
                                : 'border-transparent text-text-secondary hover:text-text-primary' }}">
                        Rejected
                    </button>
                </div>

                <!-- Pending Template panel -->
                @if($activeEmailTab === 'pending')
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Subject Line</label>
                        <input type="text" wire:model="pending_subject"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"/>
                        @error('pending_subject') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Email Body Content</label>
                        <textarea wire:model="pending_body" rows="6"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-4 font-mono resize-none"></textarea>
                        @error('pending_body') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif

                <!-- Approved Template panel -->
                @if($activeEmailTab === 'approved')
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Subject Line</label>
                        <input type="text" wire:model="approved_subject"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"/>
                        @error('approved_subject') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Email Body Content</label>
                        <textarea wire:model="approved_body" rows="6"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-4 font-mono resize-none"></textarea>
                        @error('approved_body') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif

                <!-- Rejected Template panel -->
                @if($activeEmailTab === 'rejected')
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Subject Line</label>
                        <input type="text" wire:model="rejected_subject"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"/>
                        @error('rejected_subject') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Email Body Content</label>
                        <textarea wire:model="rejected_body" rows="6"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-4 font-mono resize-none"></textarea>
                        @error('rejected_body') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif
            </div>

            {{-- Custom Approval & Rejection Types Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#2563EB] text-lg">fact_check</span>
                        Custom Approval & Rejection Types
                    </h3>
                    <button type="button" wire:click="openTypeModal()" 
                            class="px-3.5 py-1.5 bg-[#2563EB] hover:bg-blue-600 rounded-xl text-xs font-bold text-white transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Add Custom Type
                    </button>
                </div>

                <p class="text-xs text-text-secondary">
                    Create custom templates to use when manually approving or rejecting attendees from the Directory.
                </p>

                {{-- Inline Form for Add/Edit --}}
                @if($showTypeModal)
                <div class="bg-dark-surface-lighter border border-dark-border rounded-xl p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-dark-border pb-3">
                        <h4 class="text-xs font-bold text-text-primary uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[#2563EB] text-base">fact_check</span>
                            <span>{{ $typeId ? 'Edit Custom Type' : 'Add Custom Type' }}</span>
                        </h4>
                        <button type="button" wire:click="$set('showTypeModal', false)" class="text-text-secondary hover:text-text-primary">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        {{-- Category --}}
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Category <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" wire:click="$set('typeCat', 'approved')" 
                                        class="py-2 border rounded-xl text-xs font-bold transition-all text-center {{ $typeCat === 'approved' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500' : 'bg-dark-surface text-zinc-400 border-dark-border hover:text-white' }}">
                                    Approval Type
                                </button>
                                <button type="button" wire:click="$set('typeCat', 'rejected')" 
                                        class="py-2 border rounded-xl text-xs font-bold transition-all text-center {{ $typeCat === 'rejected' ? 'bg-red-500/20 text-red-400 border-red-500' : 'bg-dark-surface text-zinc-400 border-dark-border hover:text-white' }}">
                                    Rejection Type
                                </button>
                            </div>
                        </div>

                        {{-- Name --}}
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Type Name / Reason Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="typeName" placeholder="e.g. VIP Pass Approval, Quota Rejection..." 
                                   class="w-full bg-dark-surface border border-dark-border text-sm rounded-xl p-2.5 text-text-primary focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] block">
                            @error('typeName') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Subject --}}
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Email Subject <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="typeSubject" placeholder="e.g. Registration Approved: @{{event_title}}" 
                                   class="w-full bg-dark-surface border border-dark-border text-sm rounded-xl p-2.5 text-text-primary focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] block">
                            @error('typeSubject') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Body --}}
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-text-secondary uppercase tracking-wider block">Email Body <span class="text-red-500">*</span></label>
                            <textarea wire:model="typeBody" rows="6" placeholder="Dear @{{name}}, your registration..." 
                                      class="w-full bg-dark-surface border border-dark-border text-sm rounded-xl p-3 text-text-primary focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] block resize-none font-mono"></textarea>
                            @error('typeBody') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showTypeModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-text-secondary hover:text-white transition-colors">Cancel</button>
                        <button type="button" wire:click="saveCustomType()" class="px-5 py-2 bg-[#2563EB] hover:bg-blue-600 rounded-xl text-xs font-bold text-white transition-colors">Save Type</button>
                    </div>
                </div>
                @endif

                <div class="overflow-x-auto border border-dark-border rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-dark-border text-text-secondary uppercase font-semibold bg-dark-surface/50">
                                <th class="py-3 px-4">Name</th>
                                <th class="py-3 px-4">Category</th>
                                <th class="py-3 px-4">Subject</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-border">
                            @forelse($customTemplates as $ct)
                                <tr class="hover:bg-dark-surface/30 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-text-primary">{{ $ct->type_name }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border 
                                            {{ $ct->cat === 'approved' 
                                                ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
                                                : 'bg-red-500/10 text-red-400 border-red-500/20' }}">
                                            {{ ucfirst($ct->cat) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-text-secondary truncate max-w-xs">{{ $ct->email_subject }}</td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2 text-text-secondary">
                                            <button type="button" wire:click="openTypeModal({{ $ct->id }})" class="p-1 hover:text-primary transition-colors">
                                                <span class="material-symbols-outlined text-base">edit</span>
                                            </button>
                                            <button type="button" wire:click="deleteCustomType({{ $ct->id }})" wire:confirm="Are you sure you want to delete this custom type?" class="p-1 hover:text-red-400 transition-colors">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-text-secondary">No custom approval or rejection types configured yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Variables Guide & Email Banner Image -->
        <div class="space-y-6">
            {{-- Template Dynamic Tags Guide Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h4 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-lg">info</span>
                    Template Dynamic Tags
                </h4>
                <p class="text-xs text-text-secondary">Use these keys inside your template body/subject to dynamically insert registration data:</p>

                <ul class="space-y-2 text-xs">
                    <li class="p-2 bg-dark-surface rounded-xl border border-dark-border flex items-center justify-between">
                        <code class="text-[#2563EB] font-bold font-mono">@{{name}}</code>
                        <span class="text-[10px] text-text-secondary font-medium">Attendee Full Name</span>
                    </li>
                    <li class="p-2 bg-dark-surface rounded-xl border border-dark-border flex items-center justify-between">
                        <code class="text-[#2563EB] font-bold font-mono">@{{email}}</code>
                        <span class="text-[10px] text-text-secondary font-medium">Attendee Email</span>
                    </li>
                    <li class="p-2 bg-dark-surface rounded-xl border border-dark-border flex items-center justify-between">
                        <code class="text-[#2563EB] font-bold font-mono">@{{event_title}}</code>
                        <span class="text-[10px] text-text-secondary font-medium">Event Name</span>
                    </li>
                    <li class="p-2 bg-dark-surface rounded-xl border border-dark-border flex items-center justify-between">
                        <code class="text-[#2563EB] font-bold font-mono">@{{event_date}}</code>
                        <span class="text-[10px] text-text-secondary font-medium">Event Date</span>
                    </li>
                    <li class="p-2 bg-dark-surface rounded-xl border border-dark-border flex items-center justify-between">
                        <code class="text-[#2563EB] font-bold font-mono">@{{event_location}}</code>
                        <span class="text-[10px] text-text-secondary font-medium">Event Location</span>
                    </li>
                </ul>
            </div>

            {{-- Email Banner Image Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h4 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-lg">image</span>
                    Email Banner Image
                </h4>
                <p class="text-xs text-text-secondary">This banner image will be displayed at the top of all outgoing event status emails.</p>

                <div class="space-y-2">
                    <livewire:admin.media-picker 
                        field="email_banner" 
                        :value="$current_banner"
                        label="Select Email Banner"
                    />
                </div>
            </div>
        </div>
    </div>

</div>
