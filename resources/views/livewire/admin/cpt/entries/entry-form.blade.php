<div class="flex flex-col h-full overflow-hidden">
    <!-- Sticky Header -->
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <a class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all"
                href="{{ route('admin.cpt.entries.index', $postType->slug) }}">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">
                    {{ $isEdit ? 'Edit' : 'Add New' }} {{ $postType->singular_label }}
                </h1>
                <div class="flex items-center gap-2 text-xs text-[#6F767E] mt-0.5">
                    <span class="w-2 h-2 rounded-full {{ $status === 'published' ? 'bg-green-500' : 'bg-gray-400' }} inline-block"></span>
                    <span>{{ ucfirst($status) }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
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
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-[#6F767E] shadow-sm hover:bg-gray-50 hover:text-[#111827] dark:bg-[#272B30] dark:text-[#FCFCFC] transition-colors focus:outline-none ml-2"
                    title="Toggle Theme">
                    <span class="material-symbols-outlined text-[24px]" x-show="!darkMode" x-cloak>dark_mode</span>
                    <span class="material-symbols-outlined text-[24px]" x-show="darkMode" x-cloak>light_mode</span>
                </button>
            </div>
            <div class="h-8 w-px bg-gray-200 dark:bg-[#272B30]"></div>
            <div class="flex items-center gap-3">
                <button wire:click="saveAsDraft" wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-[#6F767E] hover:text-[#111827] dark:hover:white transition-all flex items-center gap-2">
                    <span wire:loading wire:target="saveAsDraft" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                    <span>Save Draft</span>
                </button>
                
                <button wire:click="publish" wire:loading.attr="disabled"
                    class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                    <span wire:loading wire:target="publish" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                    <span>{{ $status === 'published' ? 'Update' : 'Publish' }}</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Center Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
            <div class="max-w-4xl mx-auto space-y-10">
                <!-- Title & Permalink -->
                <div class="space-y-4">
                    <input wire:model.blur="title"
                        class="w-full bg-transparent border-none text-4xl md:text-5xl font-extrabold text-[#111827] dark:text-[#FCFCFC] placeholder-gray-400 dark:placeholder-[#272B30] focus:ring-0 focus:outline-none shadow-none focus:shadow-none px-0 @error('title') text-red-500 placeholder-red-300 @enderror"
                        placeholder="Enter Title..." type="text" />
                    
                    @error('title')
                        <p class="text-sm text-red-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                    
                    @if($slug)
                    <div class="flex items-center gap-2 text-xs font-bold text-[#6F767E] uppercase tracking-wider pl-1">
                        <span>PERMALINK:</span>
                        <span class="text-[#6F767E] lowercase font-normal">/{{ $postType->slug }}/</span>
                        <div x-data="{ editing: false }" class="relative flex items-center gap-2">
                            <span x-show="!editing" class="bg-[#1A1A1A] px-2 py-0.5 rounded text-[#FCFCFC] lowercase font-normal border border-[#272B30]">{{ $slug }}</span>
                            <input x-show="editing" wire:model.blur="slug" @blur="editing = false" @keydown.enter="editing = false" type="text" class="bg-[#1A1A1A] px-2 py-0.5 rounded text-[#FCFCFC] lowercase font-normal border border-[#2563EB] focus:outline-none w-auto min-w-[100px]" x-cloak>
                            <button @click="editing = !editing; $nextTick(() => $el.previousElementSibling.focus())" class="text-[#6F767E] hover:text-[#FCFCFC] transition-colors">
                                <span class="material-symbols-outlined text-[14px]">edit</span>
                            </button>
                            <button type="button" wire:click="generateSlug" class="text-[#6F767E] hover:text-[#FCFCFC] transition-colors" title="Regenerate slug">
                                <span class="material-symbols-outlined text-[14px]">refresh</span>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Content Editor -->
                @if(in_array('editor', $postType->supports ?? []))
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">Content</h3>
                    </div>
                    <div wire:ignore x-data="tiptapEditor('content')" 
                         @tiptap-undo.window="undo()" 
                         @tiptap-redo.window="redo()"
                         id="cpt-content-editor" class="min-h-[500px] rounded-3xl border border-gray-200 dark:border-[#272B30]/30 bg-white dark:bg-[#1A1A1A] flex flex-col overflow-hidden">

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
                @endif

                <!-- Excerpt -->
                @if(in_array('excerpt', $postType->supports ?? []))
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">Excerpt</h3>
                    </div>
                    <textarea wire:model="excerpt" rows="3" 
                        class="w-full rounded-2xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] p-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent resize-none"
                        placeholder="Write a short description..."></textarea>
                </div>
                @endif

                <!-- Meta Boxes (Normal & Advanced) -->
                <!-- Meta Boxes (Normal & Advanced) -->
                <div class="space-y-6">
                    @php
                        $normalBoxes = collect($metaBoxes)->filter(fn($box) => $box['context'] === 'normal' && isset($groupedFields[$box['id']]));
                        $advancedBoxes = collect($metaBoxes)->filter(fn($box) => $box['context'] === 'advanced' && isset($groupedFields[$box['id']]));
                    @endphp

                    {{-- Normal Context - Tabbed Layout --}}
                    @if($normalBoxes->isNotEmpty())
                        <div x-data="{ activeTab: '{{ $normalBoxes->first()['id'] }}' }" class="bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-200 dark:border-[#272B30] overflow-hidden">
                            {{-- Tabs Header --}}
                            <div class="flex overflow-x-auto no-scrollbar border-b border-gray-200 dark:border-[#272B30] bg-gray-50/50 dark:bg-[#0B0B0B]/20">
                                @foreach($normalBoxes as $box)
                                    <button 
                                        type="button"
                                        @click="activeTab = '{{ $box['id'] }}'"
                                        :class="activeTab === '{{ $box['id'] }}' ? 'text-blue-600 border-b-2 border-blue-600 bg-white dark:bg-[#1A1A1A]' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-white border-b-2 border-transparent'"
                                        class="px-6 py-4 text-sm font-bold uppercase tracking-widest whitespace-nowrap transition-all"
                                    >
                                        {{ $box['title'] }}
                                    </button>
                                @endforeach
                            </div>

                            {{-- Tabs Content --}}
                            <div>
                                @foreach($normalBoxes as $box)
                                    <div x-show="activeTab === '{{ $box['id'] }}'" style="display: none;" class="p-6 space-y-4">
                                        {{-- Add x-cloak handling via style or class if needed, but style display:none works with x-show for initial load if js not ready --}}
                                        @foreach($groupedFields[$box['id']] as $field)
                                            @include('livewire.admin.cpt.entries.partials.field-render', ['field' => $field])
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Advanced Context - Stacked Layout --}}
                    @foreach($advancedBoxes as $box)
                        <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-200 dark:border-[#272B30] overflow-hidden">
                            <div class="border-b border-gray-200 dark:border-[#272B30] px-6 py-4 bg-gray-50/50 dark:bg-[#0B0B0B]/20">
                                <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">{{ $box['title'] }}</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                @foreach($groupedFields[$box['id']] as $field)
                                    @include('livewire.admin.cpt.entries.partials.field-render', ['field' => $field])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Default Custom Fields -->
                @if(isset($groupedFields['default']))
                    <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-200 dark:border-[#272B30] overflow-hidden">
                        <div class="border-b border-gray-200 dark:border-[#272B30] px-6 py-4 bg-gray-50/50 dark:bg-[#0B0B0B]/20">
                            <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">Custom Fields</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach($groupedFields['default'] as $field)
                                @include('livewire.admin.cpt.entries.partials.field-render', ['field' => $field])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Sidebar -->
        <aside class="w-[320px] bg-[#F4F5F6] dark:bg-[#0B0B0B] border-l border-gray-200 dark:border-[#272B30] overflow-y-auto no-scrollbar hidden lg:block">
            <div class="p-6 space-y-6">
                <!-- Publishing Info Card -->
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none" x-data="{ editingStatus: false, editingPublish: false }">
                    <div class="flex items-center gap-2 mb-6 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">tune</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Publishing Info</span>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Status -->
                        <div class="group">
                            <div class="flex items-center justify-between" x-show="!editingStatus">
                                <span class="text-sm text-[#6F767E]">Status:</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ ucfirst($status) }}</span>
                                    <button @click="editingStatus = true" class="text-[10px] font-bold text-[#2563EB] hover:underline uppercase">Edit</button>
                                </div>
                            </div>
                            <div x-show="editingStatus" class="bg-gray-50 dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-3 rounded-lg space-y-2" x-cloak>
                                <select wire:model="status" class="w-full h-8 rounded-md bg-white dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] text-xs font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-1 focus:ring-[#2563EB]">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <div class="flex justify-end">
                                    <button @click="editingStatus = false" class="text-xs text-[#2563EB] font-bold hover:underline">Done</button>
                                </div>
                            </div>
                        </div>

                        <!-- Publish Date -->
                        <div class="group">
                            <div class="flex items-center justify-between" x-show="!editingPublish">
                                <span class="text-sm text-[#6F767E]">Publish:</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">
                                        {{ $publishedAt ? \Carbon\Carbon::parse($publishedAt)->format('M d, Y H:i') : 'Immediately' }}
                                    </span>
                                    <button @click="editingPublish = true" class="text-[10px] font-bold text-[#2563EB] hover:underline uppercase">Edit</button>
                                </div>
                            </div>
                            <div x-show="editingPublish" class="bg-gray-50 dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-3 rounded-lg space-y-3 mt-2" x-cloak>
                                <input wire:model="publishedAt" type="datetime-local" 
                                    class="w-full h-8 rounded-md bg-white dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] text-xs font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-1 focus:ring-[#2563EB]">
                                <div class="flex justify-end">
                                    <button @click="editingPublish = false" class="text-xs text-[#2563EB] font-bold hover:underline">Done</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Image Card -->
                @if(in_array('thumbnail', $postType->supports ?? []))
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-6 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">image</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Featured Image</span>
                    </div>
                    
                    <livewire:admin.media-picker 
                        field="featured_image" 
                        :value="$featuredImage"
                        label="Select Featured Image"
                    />
                </div>
                @endif

                <!-- Taxonomies -->
                @foreach($taxonomies as $taxonomy)
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-4 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">folder_open</span>
                        <span class="text-xs font-bold uppercase tracking-widest">{{ $taxonomy->plural_label }}</span>
                    </div>
                    
                    <div class="max-h-40 overflow-y-auto space-y-1 p-2 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] border border-transparent focus-within:border-[#2563EB] transition-colors">
                        @if(isset($taxonomyTerms[$taxonomy->id]) && $taxonomyTerms[$taxonomy->id]->count() > 0)
                            @foreach($taxonomyTerms[$taxonomy->id] as $term)
                                <label class="flex items-center gap-2 cursor-pointer group py-1" style="margin-left: {{ ($term->depth ?? 0) * 1.25 }}rem">
                                    <input 
                                        type="checkbox"
                                        wire:click="toggleTerm({{ $taxonomy->id }}, {{ $term->id }})"
                                        @checked(in_array($term->id, $selectedTerms[$taxonomy->id] ?? []))
                                        class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB] bg-white dark:bg-[#1A1A1A] dark:border-[#272B30]"
                                    >
                                    <span class="text-sm text-[#111827] dark:text-[#FCFCFC] group-hover:text-[#2563EB] transition-colors">{{ $term->name }}</span>
                                </label>
                            @endforeach
                        @else
                            <p class="text-xs text-[#6F767E] p-2">No {{ strtolower($taxonomy->plural_label) }} found.</p>
                        @endif
                    </div>
                    
                    <!-- Quick Add Term -->
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-white/5" x-data="{ show: false }">
                        <button type="button" @click="show = !show" class="text-xs font-semibold text-[#2563EB] hover:text-blue-600 flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-sm" x-show="!show">add</span>
                            <span class="material-symbols-outlined text-sm" x-show="show">remove</span>
                            <span x-text="show ? 'Cancel' : 'Add New {{ $taxonomy->singular_label }}'"></span>
                        </button>

                        <div x-show="show" class="mt-2" x-transition>
                            <input 
                                type="text" 
                                wire:model="newTermInput.{{ $taxonomy->id }}" 
                                wire:keydown.enter.prevent="createTerm({{ $taxonomy->id }})"
                                placeholder="Term Name"
                                class="w-full mb-2 px-3 py-2 bg-gray-50 dark:bg-[#0B0B0B] border border-gray-200 dark:border-[#272B30] rounded-lg text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-1 focus:ring-[#2563EB]"
                            >
                            <button 
                                type="button" 
                                wire:click="createTerm({{ $taxonomy->id }})"
                                class="w-full px-3 py-2 bg-[#2563EB] text-white text-xs font-bold rounded-lg hover:bg-blue-600 transition-colors"
                                wire:loading.attr="disabled"
                            >
                                Add New Term
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Parent (for hierarchical) -->
                @if($postType->is_hierarchical && $possibleParents->count() > 0)
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-4 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">account_tree</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Parent</span>
                    </div>
                    <select 
                        wire:model="parentId"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-[#0B0B0B] border border-gray-200 dark:border-[#272B30] rounded-xl text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition-all text-sm"
                    >
                        <option value="">(No Parent)</option>
                        @foreach($possibleParents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <!-- Order (for hierarchical) -->
                @if($postType->is_hierarchical)
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-4 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">sort</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Order</span>
                    </div>
                    <input 
                        type="number"
                        wire:model="menuOrder"
                        min="0"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-[#0B0B0B] border border-gray-200 dark:border-[#272B30] rounded-xl text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition-all text-sm"
                    >
                    <p class="mt-2 text-xs text-[#6F767E]">Lower number = higher priority</p>
                </div>
                @endif

                <!-- Meta Boxes (Side) -->
                @foreach($metaBoxes as $box)
                    @if($box['context'] === 'side' && isset($groupedFields[$box['id']]))
                        <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                            <div class="flex items-center gap-2 mb-4 text-[#6F767E]">
                                <span class="material-symbols-outlined text-lg">extension</span>
                                <span class="text-xs font-bold uppercase tracking-widest">{{ $box['title'] }}</span>
                            </div>
                            <div class="space-y-4">
                                @foreach($groupedFields[$box['id']] as $field)
                                    @include('livewire.admin.cpt.entries.partials.field-render', ['field' => $field])
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </aside>
    </div>
    
    {{-- TipTap Media Picker Modal (If used by CPT logic, otherwise this might just exist but be unused if not wired up) --}}
    @if(in_array('thumbnail', $postType->supports ?? []))
    <livewire:admin.tiptap-media-picker />
    @endif
</div>
