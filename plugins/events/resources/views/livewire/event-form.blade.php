<div class="flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <a class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all"
                href="{{ route('admin.events.index') }}" wire:navigate>
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $eventId ? 'Edit Event' : 'Add New Event' }}</h1>
                <div class="flex items-center gap-2 text-xs text-[#6F767E] mt-0.5">
                    <span class="w-2 h-2 rounded-full {{ $status === 'published' ? 'bg-green-500' : 'bg-gray-400' }} inline-block"></span>
                    <span>{{ ucfirst($status) }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <!-- Theme Toggle -->
                <button 
                    x-data="{ 
                        darkMode: document.documentElement.classList.contains('dark'),
                        toggle() {
                            this.darkMode = !this.darkMode;
                            document.documentElement.classList.toggle('dark');
                            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                        }
                    }"
                    @click="toggle()"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-[#6F767E] shadow-sm hover:bg-gray-50 hover:text-[#111827] dark:bg-[#272B30] dark:text-[#FCFCFC] transition-colors focus:outline-none ml-2">
                    <span class="material-symbols-outlined text-[24px]" x-show="!darkMode" x-cloak>dark_mode</span>
                    <span class="material-symbols-outlined text-[24px]" x-show="darkMode" x-cloak>light_mode</span>
                </button>
            </div>
            
            <div class="h-8 w-px bg-gray-200 dark:bg-[#272B30]"></div>

            <div class="flex items-center gap-3">
                <button wire:click="save" wire:loading.attr="disabled"
                    class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                    <span wire:loading wire:target="save" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                    <span>{{ $status === 'published' ? 'Update' : 'Save' }}</span>
                </button>
            </div>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
            <div class="max-w-4xl mx-auto space-y-8">
                <!-- Title -->
                <div class="space-y-4">
                    <input wire:model.live="title"
                        class="w-full bg-transparent border-none text-4xl md:text-5xl font-extrabold text-[#111827] dark:text-[#FCFCFC] placeholder-gray-400 dark:placeholder-[#272B30] focus:ring-0 focus:outline-none shadow-none focus:shadow-none px-0 @error('title') text-red-500 placeholder-red-300 @enderror"
                        placeholder="Enter Event Title..." type="text" />
                    
                    @error('title')
                        <p class="text-sm text-red-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                    
                    @if($slug)
                    <div class="flex items-center gap-2 text-xs font-bold text-[#6F767E] uppercase tracking-wider pl-1">
                        <span>PERMALINK:</span>
                        <span class="text-[#6F767E] lowercase font-normal">{{ url('/event') }}/</span>
                        <span class="bg-[#1A1A1A] px-2 py-0.5 rounded text-[#FCFCFC] lowercase font-normal border border-[#272B30]">{{ $slug }}</span>
                    </div>
                    @endif
                </div>

                <!-- Tabs -->
                <div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 dark:border-[#272B30]">
                        <nav class="flex gap-2 overflow-x-auto no-scrollbar">
                            <button @click="activeTab = 'basic'" :class="{ 'border-[#2563EB] text-[#2563EB]': activeTab === 'basic', 'border-transparent text-[#6F767E] hover:text-[#111827] dark:hover:text-white': activeTab !== 'basic' }"
                                class="px-4 py-3 border-b-2 text-sm font-bold transition-all whitespace-nowrap">
                                Basic Info
                            </button>
                            <button @click="activeTab = 'datetime'" :class="{ 'border-[#2563EB] text-[#2563EB]': activeTab === 'datetime', 'border-transparent text-[#6F767E] hover:text-[#111827] dark:hover:text-white': activeTab !== 'datetime' }"
                                class="px-4 py-3 border-b-2 text-sm font-bold transition-all whitespace-nowrap">
                                Date & Time
                            </button>
                            <button @click="activeTab = 'location'" :class="{ 'border-[#2563EB] text-[#2563EB]': activeTab === 'location', 'border-transparent text-[#6F767E] hover:text-[#111827] dark:hover:text-white': activeTab !== 'location' }"
                                class="px-4 py-3 border-b-2 text-sm font-bold transition-all whitespace-nowrap">
                                Location
                            </button>
                            <button @click="activeTab = 'registration'" :class="{ 'border-[#2563EB] text-[#2563EB]': activeTab === 'registration', 'border-transparent text-[#6F767E] hover:text-[#111827] dark:hover:text-white': activeTab !== 'registration' }"
                                class="px-4 py-3 border-b-2 text-sm font-bold transition-all whitespace-nowrap">
                                Registration
                            </button>
                            <button @click="activeTab = 'seo'" :class="{ 'border-[#2563EB] text-[#2563EB]': activeTab === 'seo', 'border-transparent text-[#6F767E] hover:text-[#111827] dark:hover:text-white': activeTab !== 'seo' }"
                                class="px-4 py-3 border-b-2 text-sm font-bold transition-all whitespace-nowrap">
                                SEO
                            </button>
                            <button @click="activeTab = 'documentation'" :class="{ 'border-[#2563EB] text-[#2563EB]': activeTab === 'documentation', 'border-transparent text-[#6F767E] hover:text-[#111827] dark:hover:text-white': activeTab !== 'documentation' }"
                                class="px-4 py-3 border-b-2 text-sm font-bold transition-all whitespace-nowrap">
                                Documentation
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="min-h-[400px]">
                        <!-- Basic Info Tab -->
                        <div x-show="activeTab === 'basic'" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Description</label>
                                <textarea wire:model="description" rows="3"
                                    class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] p-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent resize-none"
                                    placeholder="Short description of the event..."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Content</label>
                                <div wire:ignore x-data="tiptapEditor('content')" 
                                     @tiptap-undo.window="undo()" 
                                     @tiptap-redo.window="redo()"
                                     id="event-content-editor" class="min-h-[500px] rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] flex flex-col overflow-hidden">
            
                                    <!-- Toolbar -->
                                    <div class="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-[#272B30] overflow-x-auto flex-wrap">
                                        <!-- Text Formatting -->
                                        <div class="flex items-center gap-0.5">
                                            <button type="button" @click="toggleBold()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('bold') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Bold">
                                                <span class="material-symbols-outlined text-[20px]">format_bold</span>
                                            </button>
                                            <button type="button" @click="toggleItalic()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('italic') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Italic">
                                                <span class="material-symbols-outlined text-[20px]">format_italic</span>
                                            </button>
                                            <button type="button" @click="toggleStrike()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('strike') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Strike">
                                                <span class="material-symbols-outlined text-[20px]">strikethrough_s</span>
                                            </button>
                                            <button type="button" @click="toggleCodeBlock()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('codeBlock') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Code Block">
                                                <span class="material-symbols-outlined text-[20px]">code</span>
                                            </button>
                                        </div>
                                        
                                        <div class="w-px h-5 bg-gray-200 dark:bg-[#272B30] mx-1"></div>
            
                                        <!-- Headings -->
                                        <div class="flex items-center gap-0.5">
                                            <button type="button" @click="toggleHeading(1)" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('heading', { level: 1 }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Heading 1">
                                                <span class="material-symbols-outlined text-[20px]">format_h1</span>
                                            </button>
                                            <button type="button" @click="toggleHeading(2)" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('heading', { level: 2 }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Heading 2">
                                                <span class="material-symbols-outlined text-[20px]">format_h2</span>
                                            </button>
                                            <button type="button" @click="toggleHeading(3)" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('heading', { level: 3 }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Heading 3">
                                                <span class="material-symbols-outlined text-[20px]">format_h3</span>
                                            </button>
                                        </div>
            
                                        <div class="w-px h-5 bg-gray-200 dark:bg-[#272B30] mx-1"></div>
            
                                        <!-- Alignment -->
                                        <div class="flex items-center gap-0.5">
                                            <button type="button" @click="setTextAlign('left')" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive({ textAlign: 'left' }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Align Left">
                                                <span class="material-symbols-outlined text-[20px]">format_align_left</span>
                                            </button>
                                            <button type="button" @click="setTextAlign('center')" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive({ textAlign: 'center' }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Align Center">
                                                <span class="material-symbols-outlined text-[20px]">format_align_center</span>
                                            </button>
                                            <button type="button" @click="setTextAlign('right')" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive({ textAlign: 'right' }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Align Right">
                                                <span class="material-symbols-outlined text-[20px]">format_align_right</span>
                                            </button>
                                            <button type="button" @click="setTextAlign('justify')" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive({ textAlign: 'justify' }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Justify">
                                                <span class="material-symbols-outlined text-[20px]">format_align_justify</span>
                                            </button>
                                        </div>
            
                                        <div class="w-px h-5 bg-gray-200 dark:bg-[#272B30] mx-1"></div>
            
                                        <!-- Lists & Indent -->
                                        <div class="flex items-center gap-0.5">
                                            <button type="button" @click="toggleBulletList()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('bulletList') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Bullet List">
                                                <span class="material-symbols-outlined text-[20px]">format_list_bulleted</span>
                                            </button>
                                            <button type="button" @click="toggleOrderedList()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('orderedList') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Ordered List">
                                                <span class="material-symbols-outlined text-[20px]">format_list_numbered</span>
                                            </button>
                                        </div>
            
                                        <div class="w-px h-5 bg-gray-200 dark:bg-[#272B30] mx-1"></div>
            
                                        <!-- Insert -->
                                        <div class="flex items-center gap-0.5">
                                            <button type="button" @click="setLink()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('link') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Link">
                                                <span class="material-symbols-outlined text-[20px]">link</span>
                                            </button>
                                            <button type="button" @click="openMediaPicker()" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Image from Media Library">
                                                <span class="material-symbols-outlined text-[20px]">image</span>
                                            </button>
                                            <button type="button" @click="toggleBlockquote()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('blockquote') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Blockquote">
                                                <span class="material-symbols-outlined text-[20px]">format_quote</span>
                                            </button>
                                            <button type="button" @click="setHorizontalRule()" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Horizontal Rule">
                                                <span class="material-symbols-outlined text-[20px]">horizontal_rule</span>
                                            </button>
                                        </div>
            
                                        <div class="w-px h-5 bg-gray-200 dark:bg-[#272B30] mx-1"></div>
            
                                        <!-- Clear & History -->
                                        <div class="flex items-center gap-0.5">
                                            <button type="button" @click="clearFormatting()" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Clear Formatting">
                                                <span class="material-symbols-outlined text-[20px]">format_clear</span>
                                            </button>
                                            <button type="button" @click="undo()" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Undo">
                                                <span class="material-symbols-outlined text-[20px]">undo</span>
                                            </button>
                                            <button type="button" @click="redo()" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Redo">
                                                <span class="material-symbols-outlined text-[20px]">redo</span>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Editor Area -->
                                    <div x-ref="editor" class="flex-1 overflow-y-auto cursor-text relative"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Date & Time Tab -->
                        <div x-show="activeTab === 'datetime'" class="space-y-6" x-cloak>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Start Date *</label>
                                    <input wire:model="start_date" type="date"
                                        class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent">
                                    @error('start_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Start Time *</label>
                                    <input wire:model="start_time" type="time"
                                        class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent">
                                    @error('start_time') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">End Date</label>
                                    <input wire:model="end_date" type="date"
                                        class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">End Time</label>
                                    <input wire:model="end_time" type="time"
                                        class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent">
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <input wire:model="is_all_day" type="checkbox" id="is_all_day"
                                    class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                <label for="is_all_day" class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">All-day event</label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Timezone</label>
                                <select wire:model="timezone"
                                    class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent">
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}">{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div x-show="activeTab === 'location'" class="space-y-6" x-cloak>
                            <div>
                                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Event Type *</label>
                                <div class="grid grid-cols-3 gap-4">
                                    <label class="relative flex items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all {{ $event_type === 'online' ? 'border-[#2563EB] bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-[#272B30] hover:border-gray-300' }}">
                                        <input wire:model.live="event_type" type="radio" value="online" class="sr-only">
                                        <div class="text-center">
                                            <span class="material-symbols-outlined text-3xl {{ $event_type === 'online' ? 'text-[#2563EB]' : 'text-[#6F767E]' }}">videocam</span>
                                            <p class="text-sm font-bold mt-2 {{ $event_type === 'online' ? 'text-[#2563EB]' : 'text-[#111827] dark:text-[#FCFCFC]' }}">Online</p>
                                        </div>
                                    </label>

                                    <label class="relative flex items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all {{ $event_type === 'offline' ? 'border-[#2563EB] bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-[#272B30] hover:border-gray-300' }}">
                                        <input wire:model.live="event_type" type="radio" value="offline" class="sr-only">
                                        <div class="text-center">
                                            <span class="material-symbols-outlined text-3xl {{ $event_type === 'offline' ? 'text-[#2563EB]' : 'text-[#6F767E]' }}">location_on</span>
                                            <p class="text-sm font-bold mt-2 {{ $event_type === 'offline' ? 'text-[#2563EB]' : 'text-[#111827] dark:text-[#FCFCFC]' }}">Offline</p>
                                        </div>
                                    </label>

                                    <label class="relative flex items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all {{ $event_type === 'hybrid' ? 'border-[#2563EB] bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-[#272B30] hover:border-gray-300' }}">
                                        <input wire:model.live="event_type" type="radio" value="hybrid" class="sr-only">
                                        <div class="text-center">
                                            <span class="material-symbols-outlined text-3xl {{ $event_type === 'hybrid' ? 'text-[#2563EB]' : 'text-[#6F767E]' }}">hub</span>
                                            <p class="text-sm font-bold mt-2 {{ $event_type === 'hybrid' ? 'text-[#2563EB]' : 'text-[#111827] dark:text-[#FCFCFC]' }}">Hybrid</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            @if($event_type === 'online' || $event_type === 'hybrid')
                            <div>
                                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Meeting URL</label>
                                <input wire:model="online_meeting_url" type="url"
                                    class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent"
                                    placeholder="https://zoom.us/j/...">
                            </div>
                            @endif

                            @if($event_type === 'offline' || $event_type === 'hybrid')
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Location Name</label>
                                    <input wire:model="location" type="text"
                                        class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent"
                                        placeholder="e.g., Grand Ballroom">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Address</label>
                                    <textarea wire:model="location_address" rows="3"
                                        class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] p-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent resize-none"
                                        placeholder="Full address..."></textarea>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Registration Tab -->
                        <div x-show="activeTab === 'registration'" class="space-y-6" x-cloak>
                            <div class="flex items-center gap-3">
                                <input wire:model.live="requires_registration" type="checkbox" id="requires_registration"
                                    class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                <label for="requires_registration" class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Require registration for this event</label>
                            </div>

                            @if($requires_registration)
                            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
                                <div class="flex items-start gap-3">
                                    <input wire:model="registration_requires_approval" type="checkbox" id="registration_requires_approval"
                                        class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB] mt-0.5">
                                    <div class="flex-1">
                                        <label for="registration_requires_approval" class="text-sm font-bold text-blue-900 dark:text-blue-100 cursor-pointer">Require admin approval for registrations</label>
                                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">When enabled, registrations will be in "pending" status until approved by admin. When disabled, registrations are automatically confirmed.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Max Participants</label>
                                    <input wire:model="max_participants" type="number" min="1"
                                        class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent"
                                        placeholder="Leave empty for unlimited">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Registration Deadline</label>
                                    <input wire:model="registration_deadline" type="date"
                                        class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent">
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- SEO Tab -->
                        <div x-show="activeTab === 'seo'" class="space-y-6" x-cloak>
                            <div>
                                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Meta Title</label>
                                <input wire:model="meta_title" type="text"
                                    class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent"
                                    placeholder="SEO Title (defaults to event title)">
                                <p class="mt-2 text-xs text-[#6F767E]">Recommended length: 50-60 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Meta Description</label>
                                <textarea wire:model="meta_description" rows="3"
                                    class="w-full rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] p-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent resize-none"
                                    placeholder="SEO Description (defaults to description)"></textarea>
                                <p class="mt-2 text-xs text-[#6F767E]">Recommended length: 150-160 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Meta Keywords</label>
                                <input wire:model="meta_keywords" type="text"
                                    class="w-full h-12 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent"
                                    placeholder="keyword1, keyword2, keyword3">
                            </div>
                        </div>

                        <!-- Documentation Tab -->
                        <div x-show="activeTab === 'documentation'" x-cloak class="space-y-6">
                            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-200 dark:border-[#272B30] p-6">
                                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-4">Event Gallery</h3>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    {{-- Existing Images --}}
                                    @if(!empty($gallery_images))
                                        @foreach($gallery_images as $index => $image)
                                            <div class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-[#272B30]">
                                                <img src="/storage/{{ $image }}" class="w-full h-full object-cover">
                                                <button wire:click="removeGalleryImage({{ $index }})" type="button" 
                                                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif

                                    {{-- Add Photo Button (Media Picker) --}}
                                    <div class="aspect-square">
                                        <livewire:admin.media-picker 
                                            field="gallery_images" 
                                            label="Add Photo" 
                                            :multiple="false"
                                            :shouldClearAfterSelection="true" 
                                            :compact="false"
                                        />
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500">Supported formats: JPG, PNG. Max size: 10MB.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="w-[320px] bg-[#F4F5F6] dark:bg-[#0B0B0B] border-l border-gray-200 dark:border-[#272B30] overflow-y-auto no-scrollbar hidden lg:block">
            <div class="p-6 space-y-6">
                <!-- Publishing Info Card -->
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-6 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">tune</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Publishing</span>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Status</label>
                            <select wire:model="status"
                                class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Category</label>
                            <select wire:model="category_id"
                                class="w-full h-10 rounded-xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-3 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                                <option value="">No Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Speakers Card -->
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-4 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">mic</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Speakers</span>
                    </div>

                    <div class="space-y-3">
                         @if($availableSpeakers->isEmpty())
                            <div class="text-xs text-gray-500 italic p-3 bg-gray-50 rounded-lg text-center">
                                No speakers found. 
                                <a href="{{ route('admin.events.migration.wordpress.speakers') }}" class="text-indigo-600 hover:underline">Import</a> or add manually.
                            </div>
                        @else
                            <div class="max-h-60 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                                @foreach($availableSpeakers as $speaker)
                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] cursor-pointer transition-colors group">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" wire:model="speakers" value="{{ $speaker->id }}"
                                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition-all">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC] truncate group-hover:text-indigo-600 transition-colors">{{ $speaker->name }}</p>
                                            <p class="text-[10px] text-[#6F767E] truncate">{{ $speaker->title ?? 'Speaker' }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="text-xs text-[#6F767E] text-center pt-2 border-t border-gray-100 dark:border-[#272B30]">
                                {{ count($speakers) }} selected
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Featured Image Card -->
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-4 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">image</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Featured Image</span>
                    </div>
                    
                    @if($existingFeaturedImage)
                        <div class="relative group">
                            <img src="{{ $existingFeaturedImage->url }}" class="w-full h-40 object-cover rounded-xl">
                            <button wire:click="removeFeaturedImage" type="button"
                                class="absolute top-2 right-2 h-8 w-8 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                    @elseif($featured_image)
                        <div class="relative group">
                            <img src="{{ $featured_image->temporaryUrl() }}" class="w-full h-40 object-cover rounded-xl">
                            <button wire:click="removeFeaturedImage" type="button"
                                class="absolute top-2 right-2 h-8 w-8 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                    @else
                        <input wire:model="featured_image" type="file" accept="image/*" id="featured-image" class="hidden">
                        <label for="featured-image"
                            class="flex flex-col items-center justify-center h-40 rounded-xl border-2 border-dashed border-gray-300 dark:border-[#272B30] cursor-pointer hover:border-[#2563EB] transition-all">
                            <span class="material-symbols-outlined text-4xl text-[#6F767E]">add_photo_alternate</span>
                            <span class="text-sm font-medium text-[#6F767E] mt-2">Upload Image</span>
                        </label>
                    @endif
                </div>
            </div>
        </aside>
    </div>
    {{-- TipTap Media Picker Modal --}}
    <livewire:admin.tiptap-media-picker />
</div>
