<div class="flex flex-col h-full overflow-hidden">
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <a class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all"
                href="{{ route('admin.posts.index') }}" wire:navigate>
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $postId ? 'Edit Post' : 'Add New Post' }}</h1>
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
                <button wire:click="save('draft')" wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-[#6F767E] hover:text-[#111827] dark:hover:white transition-all flex items-center gap-2">
                    <span wire:loading wire:target="save('draft')" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                    <span>Save Draft</span>
                </button>
                @if($slug)
                <a href="{{ route('posts.show', $slug) }}" target="_blank"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-[#111827] dark:text-white bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] hover:border-[#6F767E] transition-all flex items-center justify-center">
                    Preview
                </a>
                @else
                <button disabled
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed">
                    Preview
                </button>
                @endif
                <button wire:click="save('published')" wire:loading.attr="disabled"
                    class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                    <span wire:loading wire:target="save('published')" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                    <span>{{ $status === 'published' ? 'Update' : 'Publish' }}</span>
                </button>
            </div>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
            <div class="max-w-4xl mx-auto space-y-10">
                <!-- Title -->
                <div class="space-y-4">
                    <input wire:model.live="title"
                        class="w-full bg-transparent border-none text-4xl md:text-5xl font-extrabold text-[#111827] dark:text-[#FCFCFC] placeholder-gray-400 dark:placeholder-[#272B30] focus:ring-0 focus:outline-none shadow-none focus:shadow-none px-0 @error('title') text-red-500 placeholder-red-300 @enderror"
                        placeholder="Enter Post Title..." type="text" />
                    
                    @error('title')
                        <p class="text-sm text-red-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                    
                    @if($slug)
                    <div class="flex items-center gap-2 text-xs font-bold text-[#6F767E] uppercase tracking-wider pl-1">
                        <span>PERMALINK:</span>
                        <span class="text-[#6F767E] lowercase font-normal">{{ url('/') }}/</span>
                        <div x-data="{ editing: false }" class="relative flex items-center gap-2">
                            <span x-show="!editing" class="bg-[#1A1A1A] px-2 py-0.5 rounded text-[#FCFCFC] lowercase font-normal border border-[#272B30]">{{ $slug }}</span>
                            <input x-show="editing" wire:model.blur="slug" @blur="editing = false" @keydown.enter="editing = false" type="text" class="bg-[#1A1A1A] px-2 py-0.5 rounded text-[#FCFCFC] lowercase font-normal border border-[#2563EB] focus:outline-none w-auto min-w-[100px]" x-cloak>
                            <button @click="editing = !editing; $nextTick(() => $el.previousElementSibling.focus())" class="text-[#6F767E] hover:text-[#FCFCFC] transition-colors">
                                <span class="material-symbols-outlined text-[14px]">edit</span>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Content Editor (Simplified to Textarea for now) -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">Content</h3>
                    </div>
                    <div wire:ignore x-data="tiptapEditor('content')" 
                         @tiptap-undo.window="undo()" 
                         @tiptap-redo.window="redo()"
                         id="post-content-editor" class="min-h-[500px] rounded-3xl border border-gray-200 dark:border-[#272B30]/30 bg-white dark:bg-[#1A1A1A] flex flex-col overflow-hidden">

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

                        <!-- Excerpt -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">Excerpt</h3>
                            </div>
                            <textarea wire:model="excerpt" rows="3" 
                                class="w-full rounded-2xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] p-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent resize-none"
                                placeholder="Write a short excerpt..."></textarea>
                        </div>

                        <!-- SEO Section -->
                        <div x-data="{ activeTab: 'meta' }" class="mt-6 bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-200 dark:border-[#272B30] overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-[#272B30] flex items-center justify-between">
                                <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">SEO Settings</h3>
                                
                                <!-- Tabs -->
                                <div class="flex bg-gray-100 dark:bg-[#272B30] rounded-lg p-1">
                                    <button type="button" 
                                            @click="activeTab = 'meta'" 
                                            :class="{ 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm': activeTab === 'meta', 'text-[#6F767E] hover:text-[#111827] dark:hover:text-white': activeTab !== 'meta' }" 
                                            class="px-4 py-1.5 rounded-md text-xs font-bold transition-all">
                                        Meta Data
                                    </button>
                                    <button type="button" 
                                            @click="activeTab = 'og'" 
                                            :class="{ 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm': activeTab === 'og', 'text-[#6F767E] hover:text-[#111827] dark:hover:text-white': activeTab !== 'og' }" 
                                            class="px-4 py-1.5 rounded-md text-xs font-bold transition-all">
                                        Open Graph
                                    </button>
                                </div>
                            </div>
    
                            <div class="p-6">
                                <!-- Meta Data Tab -->
                                <div x-show="activeTab === 'meta'" class="space-y-6">
                                    <div>
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" wire:model="meta_title" class="form-input-field" placeholder="SEO Title (defaults to post title)">
                                        <p class="mt-2 text-xs text-[#6F767E]">Recommended length: 50-60 characters</p>
                                    </div>
                                    <div>
                                        <label class="form-label">Meta Description</label>
                                        <textarea wire:model="meta_description" rows="3" class="form-input-field" placeholder="SEO Description (defaults to excerpt)"></textarea>
                                        <p class="mt-2 text-xs text-[#6F767E]">Recommended length: 150-160 characters</p>
                                    </div>
                                </div>
    
                                <!-- Open Graph Tab -->
                                <div x-show="activeTab === 'og'" class="space-y-6" x-cloak>
                                    <div>
                                        <label class="form-label">OG Title</label>
                                        <input type="text" wire:model="og_title" class="form-input-field" placeholder="Social Media Title">
                                    </div>
                                    <div>
                                        <label class="form-label">OG Description</label>
                                        <textarea wire:model="og_description" rows="3" class="form-input-field" placeholder="Social Media Description"></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label">OG Image URL</label>
                                        <input type="text" wire:model="og_image" class="form-input-field" placeholder="https://example.com/image.jpg">
                                        <p class="mt-2 text-xs text-[#6F767E]">Leave empty to use featured image</p>
                                    </div>
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
                    <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none" x-data="{ editingStatus: false, editingVisibility: false }">
                        <div class="flex items-center gap-2 mb-6 text-[#6F767E]">
                            <span class="material-symbols-outlined text-lg">tune</span>
                            <span class="text-xs font-bold uppercase tracking-widest">Publishing Info</span>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Featured Toggle -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-[#6F767E]">Featured Post</span>
                                <button type="button" 
                                    wire:click="$toggle('is_featured')"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2"
                                    :class="{ 'bg-[#2563EB]': @js($is_featured), 'bg-gray-200 dark:bg-[#272B30]': !@js($is_featured) }"
                                    role="switch" 
                                    aria-checked="false">
                                    <span aria-hidden="true" 
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="{ 'translate-x-5': @js($is_featured), 'translate-x-0': !@js($is_featured) }">
                                    </span>
                                </button>
                            </div>

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

                        <!-- Visibility -->
                        <div class="group">
                            <div class="flex items-center justify-between" x-show="!editingVisibility">
                                <span class="text-sm text-[#6F767E]">Visibility:</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ ucfirst($visibility) }}</span>
                                    <button @click="editingVisibility = true" class="text-[10px] font-bold text-[#2563EB] hover:underline uppercase">Edit</button>
                                </div>
                            </div>
                            <div x-show="editingVisibility" class="bg-gray-50 dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-3 rounded-lg space-y-2" x-cloak>
                                <select wire:model.live="visibility" class="w-full h-8 rounded-md bg-white dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] text-xs font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-1 focus:ring-[#2563EB]">
                                    <option value="public">Public</option>
                                    <option value="private">Private</option>
                                    <option value="password">Password Protected</option>
                                </select>
                                
                                <div x-show="$wire.visibility === 'password'" x-transition>
                                    <input type="password" wire:model="password" placeholder="Enter password" class="w-full h-8 rounded-md bg-white dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] text-xs font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-1 focus:ring-[#2563EB]">
                                </div>

                                <div class="flex justify-end">
                                    <button @click="editingVisibility = false" class="text-xs text-[#2563EB] font-bold hover:underline">Done</button>
                                </div>
                            </div>
                        </div>

                        <!-- Author -->
                        <div class="group" x-data="{ editingAuthor: false }">
                            <div class="flex items-center justify-between" x-show="!editingAuthor">
                                <span class="text-sm text-[#6F767E]">Author:</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $users->find($author_id)->name ?? 'Unknown' }}</span>
                                    <button @click="editingAuthor = true" class="text-[10px] font-bold text-[#2563EB] hover:underline uppercase">Edit</button>
                                </div>
                            </div>
                            <div x-show="editingAuthor" class="bg-gray-50 dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-3 rounded-lg space-y-2 relative z-10 shadow-lg" x-cloak @click.away="editingAuthor = false">
                                <select wire:model="author_id" class="w-full h-8 rounded-md bg-white dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] text-xs font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-1 focus:ring-[#2563EB]">
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex justify-end">
                                    <button @click="editingAuthor = false" class="text-xs text-[#2563EB] font-bold hover:underline">Done</button>
                                </div>
                            </div>
                        </div>

                        <!-- Publish Date -->
                        <div class="group" x-data="{ editingPublish: false }">
                            <div class="flex items-center justify-between" x-show="!editingPublish">
                                <span class="text-sm text-[#6F767E]">Publish:</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">
                                        {{ $published_at ? \Carbon\Carbon::parse($published_at)->format('M d, Y H:i') : 'Immediately' }}
                                    </span>
                                    <button @click="editingPublish = true" class="text-[10px] font-bold text-[#2563EB] hover:underline uppercase">Edit</button>
                                </div>
                            </div>
                            <div x-show="editingPublish" class="bg-gray-50 dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-3 rounded-lg space-y-3 mt-2" x-cloak>
                                <div class="flex items-center gap-2">
                                    <input type="radio" id="publish_immediately" name="publish_type" 
                                        @click="$wire.set('published_at', null)" 
                                        :checked="{{ $published_at ? 'false' : 'true' }}"
                                        class="text-[#2563EB] focus:ring-[#2563EB] bg-white dark:bg-[#0B0B0B] border-gray-300 dark:border-[#272B30]">
                                    <label for="publish_immediately" class="text-xs font-medium text-[#111827] dark:text-[#FCFCFC]">Immediately</label>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" id="publish_schedule" name="publish_type" 
                                            :checked="{{ $published_at ? 'true' : 'false' }}"
                                            class="text-[#2563EB] focus:ring-[#2563EB] bg-white dark:bg-[#0B0B0B] border-gray-300 dark:border-[#272B30]">
                                        <label for="publish_schedule" class="text-xs font-medium text-[#111827] dark:text-[#FCFCFC]">Schedule</label>
                                    </div>
                                    <input wire:model="published_at" type="datetime-local" 
                                        class="w-full h-8 rounded-md bg-white dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] text-xs font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-1 focus:ring-[#2563EB]">
                                </div>
                                <div class="flex justify-end">
                                    <button @click="editingPublish = false" class="text-xs text-[#2563EB] font-bold hover:underline">Done</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-4 border-t border-gray-100 dark:border-[#272B30] flex items-center justify-end text-end">
                        <button wire:click="delete" wire:confirm="Are you sure you want to move this post to trash?" class="text-xs font-bold text-[#FF6A55] hover:text-[#ff4f38] transition-colors">
                            Move to Trash
                        </button>
                    </div>
                </div>

                <!-- Featured Image Card -->
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-6 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">image</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Featured Image</span>
                    </div>
                    
                    <livewire:admin.media-picker 
                        field="featured_image" 
                        :value="$featured_image"
                        label="Select Featured Image"
                    />
                </div>

                <!-- Organization Card -->
                <div class="rounded-2xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center gap-2 mb-6 text-[#6F767E]">
                        <span class="material-symbols-outlined text-lg">folder_open</span>
                        <span class="text-xs font-bold uppercase tracking-widest">Organization</span>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Categories -->
                        <div class="space-y-2" x-data="{ addingCategory: false, newCategoryName: '' }">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Categories</label>
                                <button @click="addingCategory = !addingCategory" class="text-[10px] font-bold text-[#2563EB] hover:underline uppercase">Add New</button>
                            </div>
                            
                            <div x-show="addingCategory" class="flex gap-2 mb-2" x-cloak>
                                <input x-model="newCategoryName" 
                                    @keydown.enter.prevent="$wire.addCategory(newCategoryName); newCategoryName = ''; addingCategory = false"
                                    type="text" 
                                    class="flex-1 h-8 rounded-md bg-white dark:bg-[#0B0B0B] border-gray-200 dark:border-[#272B30] text-xs font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-1 focus:ring-[#2563EB]" 
                                    placeholder="Category name">
                                <button @click="$wire.addCategory(newCategoryName); newCategoryName = ''; addingCategory = false" class="px-2 h-8 rounded-md bg-[#2563EB] text-white text-xs font-bold">Add</button>
                            </div>

                            <div class="max-h-40 overflow-y-auto space-y-1 p-2 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] border border-transparent focus-within:border-[#2563EB] transition-colors">
                                @foreach($categories as $category)
                                <label class="flex items-center gap-2 cursor-pointer group py-1">
                                    <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="w-4 h-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB] bg-white dark:bg-[#1A1A1A] dark:border-[#272B30]">
                                    <span class="text-sm text-[#111827] dark:text-[#FCFCFC] group-hover:text-[#2563EB] transition-colors">{{ $category->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="space-y-2" x-data="{ newTag: '' }">
                            <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Tags</label>
                            <div class="relative">
                                <input x-model="newTag" 
                                    @keydown.enter.prevent="$wire.addTag(newTag); newTag = ''"
                                    type="text" 
                                    class="w-full h-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] pl-4 pr-10"
                                    placeholder="Add tags...">
                                <button @click="$wire.addTag(newTag); newTag = ''" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-[#6F767E] hover:text-[#2563EB] transition-colors">
                                    <span class="material-symbols-outlined text-xl">add</span>
                                </button>
                            </div>
                            <!-- Visual Chips for Tags -->
                            @if($tags)
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach(array_filter(array_map('trim', explode(',', $tags))) as $tag)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-100 dark:bg-[#272B30] border border-gray-200 dark:border-[#33383f]">
                                    <span class="text-[10px] font-bold text-[#111827] dark:text-[#FCFCFC] uppercase">{{ $tag }}</span>
                                    <button wire:click="removeTag('{{ $tag }}')" class="text-[#6F767E] hover:text-[#FF6A55] transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                    </button>
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- TipTap Media Picker Modal --}}
    <livewire:admin.tiptap-media-picker />
</div>

