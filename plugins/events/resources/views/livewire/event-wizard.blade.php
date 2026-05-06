<div class="flex flex-col h-full overflow-hidden" x-data="{ showBannerPicker: false }">
    <!-- Header -->
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <a class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all"
                href="{{ route('admin.events.index') }}" wire:navigate>
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">
                    {{ $eventId ? 'Edit Event' : 'Create New Event' }}
                </h1>
                <div class="flex items-center gap-2 text-xs text-[#6F767E] mt-0.5">
                    <span class="w-2 h-2 rounded-full {{ $status === 'published' ? 'bg-green-500' : 'bg-gray-400' }} inline-block"></span>
                    <span>Step {{ $currentStep }} of {{ $totalSteps }} — {{ ['Event Details', 'Date & Schedule', 'Properties', 'Success Page'][$currentStep - 1] }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            @if($currentStep > 1)
                <button wire:click="previousStep"
                    class="px-4 py-2 rounded-lg text-sm font-bold text-[#6F767E] bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Back
                </button>
            @endif
            <button wire:click="saveDraft" wire:loading.attr="disabled"
                class="px-4 py-2 rounded-lg text-sm font-bold text-[#6F767E] bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">
                Save Draft
            </button>
            @if($currentStep < $totalSteps)
                <button wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                    Next
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </button>
            @else
                <button wire:click="submit" wire:loading.attr="disabled"
                    class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-green-600 hover:bg-green-700 shadow-lg shadow-green-500/20 transition-all flex items-center gap-2">
                    <span wire:loading wire:target="submit" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                    {{ $eventId ? 'Update Event' : 'Publish Event' }}
                </button>
            @endif
        </div>
    </header>

    <!-- Progress Steps -->
    <div class="px-6 md:px-10 bg-white dark:bg-[#0B0B0B] border-b border-gray-200 dark:border-[#272B30]">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-4">
            @foreach ([1, 2, 3, 4] as $step)
                <button wire:click="goToStep({{ $step }})"
                    @if($step > $currentStep) disabled @endif
                    class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap
                        {{ $step === $currentStep ? 'bg-[#2563EB] text-white' : ($step < $currentStep ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 cursor-pointer' : 'bg-gray-100 dark:bg-[#1A1A1A] text-[#6F767E]') }}">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]
                        {{ $step === $currentStep ? 'bg-white/20' : ($step < $currentStep ? 'bg-green-500 text-white' : 'bg-gray-200 dark:bg-[#272B30]') }}">
                        @if($step < $currentStep)
                            <span class="material-symbols-outlined text-xs">check</span>
                        @else
                            {{ $step }}
                        @endif
                    </span>
                    {{ ['Details', 'Schedule', 'Properties', 'Success'][($step - 1)] }}
                </button>
                @if(!$loop->last)
                    <div class="w-6 h-px bg-gray-200 dark:bg-[#272B30] flex-shrink-0"></div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Email Warning Banner -->
    @if(isset($sending_email) && !$sending_email)
        <div class="mx-6 md:mx-10 mt-4 flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
            <span class="material-symbols-outlined text-amber-500 mt-0.5">warning</span>
            <div>
                <p class="text-sm font-bold text-amber-800 dark:text-amber-200">Email sending is disabled for this event</p>
                <p class="text-xs text-amber-600 dark:text-amber-300 mt-0.5">No confirmation emails will be sent to registrants.</p>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
        <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
            <div class="max-w-4xl mx-auto space-y-8">

                <!-- ══════════════ STEP 1: Event Details ══════════════ -->
                @if($currentStep === 1)
                <div class="space-y-6" x-show="$wire.currentStep === 1">
                    <div>
                        <input wire:model.live="title"
                            class="w-full bg-transparent border-none text-4xl md:text-5xl font-extrabold text-[#111827] dark:text-[#FCFCFC] placeholder-gray-400 dark:placeholder-[#272B30] focus:ring-0 focus:outline-none px-0"
                            placeholder="Enter Event Title..." type="text" />
                        @error('title') <p class="text-sm text-red-500 font-medium mt-1">{{ $message }}</p> @enderror

                        @if($slug)
                        <div class="flex items-center gap-2 text-xs font-bold text-[#6F767E] uppercase tracking-wider pl-1 mt-2">
                            <span>PERMALINK:</span>
                            <span class="bg-[#1A1A1A] px-2 py-0.5 rounded text-[#FCFCFC] lowercase font-normal border border-[#272B30]">{{ url('/event') }}/{{ $slug }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Category -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Category</label>
                            <select wire:model="category_id"
                                class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                                <option value="">No Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Event Type -->
                        <div>
                            <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Event Type *</label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(['online' => 'videocam', 'offline' => 'location_on', 'hybrid' => 'hub'] as $type => $icon)
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all text-center
                                        {{ $event_type === $type ? 'border-[#2563EB] bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-[#272B30] hover:border-gray-300' }}">
                                        <input wire:model.live="event_type" type="radio" value="{{ $type }}" class="sr-only">
                                        <div>
                                            <span class="material-symbols-outlined text-2xl {{ $event_type === $type ? 'text-[#2563EB]' : 'text-[#6F767E]' }}">{{ $icon }}</span>
                                            <p class="text-xs font-bold mt-1 {{ $event_type === $type ? 'text-[#2563EB]' : 'text-[#111827] dark:text-[#FCFCFC]' }}">{{ ucfirst($type) }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Short Description</label>
                        <textarea wire:model="description" rows="3"
                            class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] p-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] resize-none"
                            placeholder="Brief description shown in event listings..."></textarea>
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Full Content</label>
                        <div wire:ignore x-data="tiptapEditor('content')"
                             @tiptap-undo.window="undo()"
                             @tiptap-redo.window="redo()"
                             id="event-content-editor" class="min-h-[400px] rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] flex flex-col overflow-hidden">
                            <div class="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-[#272B30] overflow-x-auto flex-wrap">
                                <button type="button" @click="toggleBold()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('bold') }" class="p-1.5 rounded-lg hover:bg-gray-50 text-[#6F767E]" title="Bold"><span class="material-symbols-outlined text-[20px]">format_bold</span></button>
                                <button type="button" @click="toggleItalic()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('italic') }" class="p-1.5 rounded-lg hover:bg-gray-50 text-[#6F767E]" title="Italic"><span class="material-symbols-outlined text-[20px]">format_italic</span></button>
                                <button type="button" @click="toggleHeading(1)" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('heading', { level: 1 }) }" class="p-1.5 rounded-lg hover:bg-gray-50 text-[#6F767E]" title="H1"><span class="material-symbols-outlined text-[20px]">format_h1</span></button>
                                <button type="button" @click="toggleHeading(2)" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('heading', { level: 2 }) }" class="p-1.5 rounded-lg hover:bg-gray-50 text-[#6F767E]" title="H2"><span class="material-symbols-outlined text-[20px]">format_h2</span></button>
                                <button type="button" @click="toggleBulletList()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('bulletList') }" class="p-1.5 rounded-lg hover:bg-gray-50 text-[#6F767E]" title="List"><span class="material-symbols-outlined text-[20px]">format_list_bulleted</span></button>
                                <button type="button" @click="setLink()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('link') }" class="p-1.5 rounded-lg hover:bg-gray-50 text-[#6F767E]" title="Link"><span class="material-symbols-outlined text-[20px]">link</span></button>
                                <button type="button" @click="openMediaPicker()" class="p-1.5 rounded-lg hover:bg-gray-50 text-[#6F767E]" title="Image"><span class="material-symbols-outlined text-[20px]">image</span></button>
                            </div>
                            <div x-ref="editor" class="flex-1 overflow-y-auto cursor-text relative"></div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- ══════════════ STEP 2: Date & Schedule ══════════════ -->
                @if($currentStep === 2)
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Start Date *</label>
                            <input wire:model="start_date" type="date"
                                class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                            @error('start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Start Time *</label>
                            <input wire:model="start_time" type="time"
                                class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">End Date</label>
                            <input wire:model="end_date" type="date"
                                class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">End Time</label>
                            <input wire:model="end_time" type="time"
                                class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input wire:model="is_all_day" type="checkbox" id="is_all_day"
                            class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                        <label for="is_all_day" class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">All-day event</label>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Timezone</label>
                        <select wire:model="timezone"
                            class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}">{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Registration Period -->
                    <div class="rounded-2xl bg-[#F4F5F6] dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 space-y-4">
                        <h3 class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC] uppercase tracking-wider">Registration Period</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-[#6F767E] mb-1">Opens On</label>
                                <input wire:model="registration_start_date" type="date"
                                    class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#6F767E] mb-1">Closes On</label>
                                <input wire:model="registration_end_date" type="date"
                                    class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                                @error('registration_end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Location Fields (shown based on event type) -->
                    @if($event_type === 'online' || $event_type === 'hybrid')
                    <div>
                        <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Meeting URL</label>
                        <input wire:model="online_meeting_url" type="url"
                            class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                            placeholder="https://zoom.us/j/...">
                    </div>
                    @endif

                    @if($event_type === 'offline' || $event_type === 'hybrid')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Location Name</label>
                            <input wire:model="location" type="text"
                                class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                                placeholder="e.g., Grand Ballroom">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Address</label>
                            <textarea wire:model="location_address" rows="2"
                                class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] p-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] resize-none"></textarea>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- ══════════════ STEP 3: Properties ══════════════ -->
                @if($currentStep === 3)
                <div class="space-y-6">
                    <!-- Registration Options -->
                    <div class="rounded-2xl bg-[#F4F5F6] dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 space-y-4">
                        <h3 class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC] uppercase tracking-wider">Registration Settings</h3>

                        <div class="flex items-center gap-3">
                            <input wire:model.live="requires_registration" type="checkbox" id="requires_registration"
                                class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                            <label for="requires_registration" class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Require registration</label>
                        </div>

                        @if($requires_registration)
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <input wire:model="registration_requires_approval" type="checkbox" id="approval"
                                class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB] mt-0.5">
                            <div>
                                <label for="approval" class="text-sm font-bold text-blue-900 dark:text-blue-100 cursor-pointer">Require admin approval</label>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5">Registrations will be "pending" until you approve them.</p>
                            </div>
                        </div>
                        @endif

                        <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                            <input wire:model="requires_corporate_email" type="checkbox" id="corpo_email"
                                class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB] mt-0.5">
                            <div>
                                <label for="corpo_email" class="text-sm font-bold text-amber-900 dark:text-amber-100 cursor-pointer">Require corporate email</label>
                                <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">Attendees must register with a corporate email address.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-[#6F767E] mb-1">Max Participants</label>
                                <input wire:model="max_participants" type="number" min="1"
                                    class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                                    placeholder="Unlimited">
                            </div>
                        </div>
                    </div>

                    <!-- Email Sender Config -->
                    <div class="rounded-2xl bg-[#F4F5F6] dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC] uppercase tracking-wider">Email Sender</h3>
                            <label class="relative flex items-center gap-2 cursor-pointer">
                                <input wire:model.live="sending_email" type="checkbox"
                                    class="sr-only peer">
                                <div class="w-9 h-5 rounded-full bg-gray-200 dark:bg-[#272B30] peer-checked:bg-[#2563EB] transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-4"></div>
                                <span class="text-xs font-bold text-[#6F767E]">{{ $sending_email ? 'ON' : 'OFF' }}</span>
                            </label>
                        </div>

                        @if(!$sending_email)
                        <div class="flex items-center gap-2 p-3 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs">
                            <span class="material-symbols-outlined text-sm">info</span>
                            Email sending is disabled. No confirmation emails will be sent.
                        </div>
                        @endif

                        @if($sending_email)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-[#6F767E] mb-1">Sender Email</label>
                                <input wire:model="sender_email" type="email"
                                    class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                                    placeholder="events@company.com">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-[#6F767E] mb-1">Sender Name</label>
                                <input wire:model="sender_name" type="text"
                                    class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                                    placeholder="Event Team">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-[#6F767E] mb-1">CC Email</label>
                                <input wire:model="cc_to_email" type="email"
                                    class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                                    placeholder="admin@company.com">
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Banner Upload -->
                    <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5">
                        <h3 class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC] uppercase tracking-wider mb-4">Event Banner</h3>

                        @if($banner_image && is_object($banner_image))
                            <div class="relative group mb-4">
                                <img src="{{ $banner_image->temporaryUrl() }}" class="w-full h-48 object-cover rounded-xl">
                                <button wire:click="$set('banner_image', null)" type="button"
                                    class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        @elseif($banner_image && is_string($banner_image))
                            <div class="relative group mb-4">
                                <img src="/storage/{{ $banner_image }}" class="w-full h-48 object-cover rounded-xl">
                            </div>
                        @endif

                        <input wire:model="banner_image" type="file" accept="image/jpg,image/jpeg,image/png,image/webp" id="banner-upload" class="hidden">
                        <label for="banner-upload"
                            class="flex flex-col items-center justify-center h-32 rounded-xl border-2 border-dashed border-gray-300 dark:border-[#272B30] cursor-pointer hover:border-[#2563EB] transition-all">
                            <span class="material-symbols-outlined text-3xl text-[#6F767E]">add_photo_alternate</span>
                            <span class="text-sm font-medium text-[#6F767E] mt-2">Upload Banner</span>
                            <span class="text-xs text-[#6F767E] mt-1">JPG, PNG, WebP — Max 2MB</span>
                        </label>
                        @error('banner_image') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif

                <!-- ══════════════ STEP 4: Success Page ══════════════ -->
                @if($currentStep === 4)
                <div class="space-y-6">
                    <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 space-y-4">
                        <h3 class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC] uppercase tracking-wider">Success Page Content</h3>
                        <p class="text-xs text-[#6F767E]">Shown to users after they successfully register for the event.</p>

                        <div>
                            <label class="block text-xs font-medium text-[#6F767E] mb-1">Success Title</label>
                            <input wire:model="success_title" type="text"
                                class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                                placeholder="You're All Set!">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#6F767E] mb-1">Success Message</label>
                            <textarea wire:model="success_desc" rows="3"
                                class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 py-2 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] resize-none"
                                placeholder="Thank you for registering. We'll send you a confirmation email."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#6F767E] mb-1">Button Label</label>
                            <input wire:model="success_button" type="text"
                                class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                                placeholder="Back to Event">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-[#6F767E] mb-2">Redirect After Success</label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input wire:model.live="success_link_type" type="radio" value="event" class="sr-only">
                                    <div class="px-4 py-2 rounded-lg text-sm font-bold border-2 transition-all
                                        {{ $success_link_type === 'event' ? 'border-[#2563EB] bg-blue-50 text-[#2563EB]' : 'border-gray-200 dark:border-[#272B30] text-[#6F767E]' }}">
                                        Back to Event
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input wire:model.live="success_link_type" type="radio" value="custom" class="sr-only">
                                    <div class="px-4 py-2 rounded-lg text-sm font-bold border-2 transition-all
                                        {{ $success_link_type === 'custom' ? 'border-[#2563EB] bg-blue-50 text-[#2563EB]' : 'border-gray-200 dark:border-[#272B30] text-[#6F767E]' }}">
                                        Custom URL
                                    </div>
                                </label>
                            </div>
                        </div>

                        @if($success_link_type === 'custom')
                        <div>
                            <label class="block text-xs font-medium text-[#6F767E] mb-1">Custom Redirect URL</label>
                            <input wire:model="success_link" type="url"
                                class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                                placeholder="https://...">
                            @error('success_link') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </div>

                    <!-- Display Flags -->
                    <div class="rounded-2xl bg-[#F4F5F6] dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 space-y-3">
                        <h3 class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC] uppercase tracking-wider">Display Options</h3>
                        <div class="flex items-center gap-3">
                            <input wire:model="show_registered_count" type="checkbox" id="show_count"
                                class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                            <label for="show_count" class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Show registered count on event page</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input wire:model="enable_track_session" type="checkbox" id="track_session"
                                class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                            <label for="track_session" class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Enable session tracking</label>
                        </div>
                    </div>

                    <!-- Publishing Status -->
                    <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5">
                        <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-3">Event Status</label>
                        <select wire:model="status"
                            class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                            <option value="draft">Draft — Not visible to public</option>
                            <option value="published">Published — Visible to public</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session()->has('message'))
        <div class="fixed bottom-6 right-6 flex items-center gap-3 px-5 py-3 rounded-xl bg-[#1A1A1A] dark:bg-white text-white dark:text-[#111827] shadow-xl z-50">
            <span class="material-symbols-outlined text-green-400">check_circle</span>
            <span class="text-sm font-medium">{{ session('message') }}</span>
        </div>
    @endif
    @if(session()->has('success'))
        <div class="fixed bottom-6 right-6 flex items-center gap-3 px-5 py-3 rounded-xl bg-green-600 text-white shadow-xl z-50">
            <span class="material-symbols-outlined text-white">check_circle</span>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
</div>
