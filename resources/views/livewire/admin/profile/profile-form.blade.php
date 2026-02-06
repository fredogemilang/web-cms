<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <div class="lg:col-span-8 space-y-8">
        <section
            class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
            <div class="mb-10">
                <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Personal Information
                </h2>
                <p class="text-sm text-[#6F767E] mt-1">Manage your basic account details and identity.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div class="space-y-1">
                    <label class="form-label">Full Name</label>
                    <input wire:model.blur="name" class="form-input-field @error('name') border-red-500 @enderror" type="text" />
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="form-label">Username</label>
                    <input wire:model.blur="username" class="form-input-field @error('username') border-red-500 @enderror" type="text" />
                    @error('username') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2 space-y-1">
                    <label class="form-label">Email Address</label>
                    <input wire:model.blur="email" class="form-input-field @error('email') border-red-500 @enderror" type="email" />
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mt-8 space-y-1">
                <label class="form-label">Bio</label>
                <textarea wire:model.blur="bio" class="form-input-field resize-none @error('bio') border-red-500 @enderror"
                    placeholder="Write a short bio about yourself..."
                    rows="5"></textarea>
                @error('bio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                <p class="text-xs text-[#6F767E] mt-2">Brief description for your profile. Maximum 200
                    characters.</p>
            </div>

        </section>

        <section
            class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
            <div class="mb-10">
                <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Security</h2>
                <p class="text-sm text-[#6F767E] mt-1">Enhance your account safety and access control.
                </p>
            </div>
            <div class="space-y-6">
                <div
                    id="password-change-row"
                    class="flex items-center justify-between p-6 rounded-2xl bg-gray-50 dark:bg-[#0B0B0B] border border-gray-100 dark:border-[#272B30]">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-primary">
                            <span class="material-symbols-outlined">lock</span>
                        </div>
                        <div>
                            <p class="text-[15px] font-bold text-[#111827] dark:text-[#FCFCFC]">Change
                                Password</p>
                            <p class="text-xs text-[#6F767E]">
                                Last changed {{ auth()->user()->password_changed_at ? auth()->user()->password_changed_at->diffForHumans() : 'Never' }}
                            </p>
                        </div>
                    </div>
                    <button
                        x-data=""
                        @click="$dispatch('open-password-modal'); $nextTick(() => document.getElementById('password-change-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' }))"
                        class="px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-primary hover:bg-primary/10 rounded-xl transition-colors border border-primary/20">
                        Update
                    </button>
                </div>
                
                <!-- Password Change Modal/Form (Inline for now as per request "without input old password") -->
                <div 
                    id="password-change-form"
                    x-data="{ open: false }" 
                    @open-password-modal.window="open = true" 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-4"
                    x-cloak 
                    class="space-y-4 p-6 rounded-2xl bg-gray-50 dark:bg-[#0B0B0B] border border-gray-100 dark:border-[#272B30]"
                >
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Set New Password</h3>
                        <button 
                            @click="document.getElementById('password-change-row').scrollIntoView({ behavior: 'smooth', block: 'center' }); setTimeout(() => open = false, 300)" 
                            class="text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]"
                        >
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                    <div class="space-y-1">
                        <label class="form-label">New Password</label>
                        <input wire:model="password" class="form-input-field @error('password') border-red-500 @enderror" type="password" placeholder="Min. 8 characters" />
                        @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="form-label">Confirm Password</label>
                        <input wire:model="password_confirmation" class="form-input-field" type="password" placeholder="Confirm new password" />
                    </div>
                    <div class="flex justify-end pt-2">
                        <button wire:click="updatePassword" class="px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white bg-primary hover:bg-blue-600 rounded-xl transition-colors shadow-lg shadow-blue-500/20">
                            Save Password
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="lg:col-span-4 space-y-8">
        <section
            class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30] text-center">
            <h2 class="form-label mb-8">Profile Picture</h2>
            <div class="relative group inline-block">
                <div
                    class="h-40 w-40 rounded-3xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center border-2 border-dashed border-gray-200 dark:border-[#272B30] overflow-hidden relative">
                    
                    <div wire:loading wire:target="avatar" class="absolute inset-0 bg-black/50 flex items-center justify-center z-10">
                        <span class="material-symbols-outlined text-white animate-spin">refresh</span>
                    </div>

                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" alt="Profile Preview" class="h-full w-full object-cover">
                    @elseif(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Profile" class="h-full w-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-5xl text-[#6F767E]">person</span>
                    @endif
                </div>
                
                <input type="file" wire:model="avatar" id="avatar-upload" class="hidden" accept="image/*">
                
                <label for="avatar-upload"
                    class="absolute -bottom-3 -right-3 h-12 w-12 bg-primary text-white rounded-2xl flex items-center justify-center shadow-lg hover:scale-110 transition-transform border-4 border-white dark:border-[#1A1A1A] cursor-pointer">
                    <span class="material-symbols-outlined text-[24px]">edit</span>
                </label>
            </div>
            @error('avatar') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
            <p class="text-xs text-[#6F767E] mt-8 font-medium leading-relaxed uppercase tracking-wider">
                JPG, GIF or PNG. <br /> Max size of 1MB</p>
        </section>

        <section
            class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-10 shadow-sm border border-gray-200 dark:border-[#272B30]">
            <h2 class="form-label mb-8">Account Metadata</h2>
            <div class="space-y-4">
                <div
                    class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-[#272B30]">
                    <span class="text-sm font-medium text-[#6F767E]">Joined Date</span>
                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ auth()->user()->created_at->format('M d, Y') ?? 'Oct 12, 2023' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-[#6F767E]">Last Login</span>
                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">2 hours
                        ago</span>
                </div>
            </div>
        </section>
    </div>
</div>
