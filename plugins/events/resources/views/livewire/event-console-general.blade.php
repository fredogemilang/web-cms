<div class="space-y-8" x-on:console-save.window="$wire.save()">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined text-lg">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- ═══════════════════════════════════════════════════════
             LEFT COLUMN: Event Information
             ═══════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Event Information Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-5">
                <h3 class="text-base font-bold text-text-primary">Event Information</h3>

                <div class="space-y-4">
                    {{-- Event Name --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider">Event Name</label>
                        <input wire:model.live="title" type="text"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"
                            placeholder="Enter event name..." />
                        @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Permalink Slug --}}
                    <div class="space-y-1">
                        @if($slug)
                        <div class="flex items-center gap-2 text-xs font-bold text-text-secondary uppercase tracking-wider">
                            <span>PERMALINK:</span>
                            <span class="text-text-secondary lowercase font-normal">{{ url('/event') }}/</span>
                            <div x-data="{ editing: false }" class="relative flex items-center gap-2">
                                <span x-show="!editing" class="bg-dark-surface px-2 py-0.5 rounded text-text-primary lowercase font-normal border border-dark-border">{{ $slug }}</span>
                                <input x-show="editing" wire:model.blur="slug" @blur="editing = false" @keydown.enter="editing = false" type="text" class="bg-dark-surface px-2 py-0.5 rounded text-text-primary lowercase font-normal border border-[#2563EB] focus:outline-none w-auto min-w-[100px]" x-cloak>
                                <button @click="editing = !editing; $nextTick(() => $el.previousElementSibling.focus())" type="button" class="text-text-secondary hover:text-text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">edit</span>
                                </button>
                            </div>
                        </div>
                        @endif
                        @error('slug') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Short Description --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider">Short Description</label>
                        <textarea wire:model="description" rows="3"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 resize-none"
                            placeholder="Provide short teaser description..."></textarea>
                    </div>

                    {{-- Event Content (TipTap Rich Text Editor) --}}
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider block mb-2">Event Content (Description / Agenda Detail)</label>
                        <div wire:ignore x-data="tiptapEditor('content')"
                             @tiptap-undo.window="undo()"
                             @tiptap-redo.window="redo()"
                             id="console-content-editor" class="rounded-xl border border-dark-border bg-dark-surface overflow-hidden">

                            {{-- Toolbar --}}
                            <div class="flex items-center gap-1 p-2 bg-dark-surface-lighter border-b border-dark-border flex-wrap">
                                {{-- Text Formatting --}}
                                <div class="flex items-center gap-0.5">
                                    <button type="button" @click="toggleBold()" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('bold') }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Bold">
                                        <span class="material-symbols-outlined text-[18px]">format_bold</span>
                                    </button>
                                    <button type="button" @click="toggleItalic()" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('italic') }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Italic">
                                        <span class="material-symbols-outlined text-[18px]">format_italic</span>
                                    </button>
                                    <button type="button" @click="toggleStrike()" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('strike') }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Strike">
                                        <span class="material-symbols-outlined text-[18px]">strikethrough_s</span>
                                    </button>
                                    <button type="button" @click="toggleCodeBlock()" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('codeBlock') }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Code Block">
                                        <span class="material-symbols-outlined text-[18px]">code</span>
                                    </button>
                                </div>

                                <div class="w-px h-5 bg-dark-border mx-1"></div>

                                {{-- Headings --}}
                                <div class="flex items-center gap-0.5">
                                    <button type="button" @click="toggleHeading(1)" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('heading', { level: 1 }) }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="H1">
                                        <span class="material-symbols-outlined text-[18px]">format_h1</span>
                                    </button>
                                    <button type="button" @click="toggleHeading(2)" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('heading', { level: 2 }) }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="H2">
                                        <span class="material-symbols-outlined text-[18px]">format_h2</span>
                                    </button>
                                    <button type="button" @click="toggleHeading(3)" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('heading', { level: 3 }) }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="H3">
                                        <span class="material-symbols-outlined text-[18px]">format_h3</span>
                                    </button>
                                </div>

                                <div class="w-px h-5 bg-dark-border mx-1"></div>

                                {{-- Lists --}}
                                <div class="flex items-center gap-0.5">
                                    <button type="button" @click="toggleBulletList()" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('bulletList') }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Bullet List">
                                        <span class="material-symbols-outlined text-[18px]">format_list_bulleted</span>
                                    </button>
                                    <button type="button" @click="toggleOrderedList()" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('orderedList') }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Ordered List">
                                        <span class="material-symbols-outlined text-[18px]">format_list_numbered</span>
                                    </button>
                                </div>

                                <div class="w-px h-5 bg-dark-border mx-1"></div>

                                {{-- Insert --}}
                                <div class="flex items-center gap-0.5">
                                    <button type="button" @click="setLink()" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('link') }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Link">
                                        <span class="material-symbols-outlined text-[18px]">link</span>
                                    </button>
                                    <button type="button" @click="openMediaPicker()" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Image">
                                        <span class="material-symbols-outlined text-[18px]">image</span>
                                    </button>
                                    <button type="button" @click="toggleBlockquote()" :class="{ 'bg-dark-surface text-[#2563EB]': isActive('blockquote') }" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Blockquote">
                                        <span class="material-symbols-outlined text-[18px]">format_quote</span>
                                    </button>
                                </div>

                                <div class="w-px h-5 bg-dark-border mx-1"></div>

                                {{-- Undo/Redo --}}
                                <div class="flex items-center gap-0.5">
                                    <button type="button" @click="undo()" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Undo">
                                        <span class="material-symbols-outlined text-[18px]">undo</span>
                                    </button>
                                    <button type="button" @click="redo()" class="p-1.5 rounded hover:bg-dark-surface text-text-secondary hover:text-text-primary transition-colors" title="Redo">
                                        <span class="material-symbols-outlined text-[18px]">redo</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Editor Area --}}
                            <div x-ref="editor" class="flex-1 overflow-y-auto cursor-text relative min-h-[250px]"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gallery Images Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h3 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-[#2563EB]">photo_library</span>
                    Gallery Images
                </h3>
                <div class="space-y-2">
                    <div class="grid grid-cols-3 gap-2">
                        @if(!empty($gallery_images))
                            @foreach($gallery_images as $index => $image)
                                <div class="relative group aspect-square rounded-lg overflow-hidden border border-dark-border">
                                    <img src="/storage/{{ $image }}" class="w-full h-full object-cover" alt="Gallery {{ $index + 1 }}">
                                    <button wire:click="removeGalleryImage({{ $index }})" type="button"
                                        class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="material-symbols-outlined text-[10px]">close</span>
                                    </button>
                                </div>
                            @endforeach
                        @endif

                        {{-- Add Image Button --}}
                        <div class="aspect-square">
                            <livewire:admin.media-picker
                                field="gallery_images"
                                label="Add Image"
                                :multiple="false"
                                :shouldClearAfterSelection="true"
                                :compact="false"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO Metadata Card --}}
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h3 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-[#2563EB]">search</span>
                    SEO Metadata
                </h3>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider">Meta Title</label>
                        <input wire:model="meta_title" type="text"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"
                            placeholder="SEO Title (defaults to event title)" />
                        <p class="text-[10px] text-text-secondary mt-1">Recommended: 50-60 characters</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider">Meta Description</label>
                        <textarea wire:model="meta_description" rows="2"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3 resize-none"
                            placeholder="SEO Description (defaults to description)"></textarea>
                        <p class="text-[10px] text-text-secondary mt-1">Recommended: 150-160 characters</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-text-secondary uppercase tracking-wider">Meta Keywords</label>
                        <input wire:model="meta_keywords" type="text"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-xl focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none block p-3"
                            placeholder="keyword1, keyword2, keyword3" />
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             RIGHT COLUMN: Publishing, Registration, Speakers, Media
             ═══════════════════════════════════════════════════════ --}}
        <div class="space-y-6">
            {{-- Publishing Config --}}
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h3 class="text-sm font-bold text-text-primary">Publishing Config</h3>
                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="text-xs text-text-secondary font-semibold">Publish Status</label>
                        <select wire:model="status"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-lg p-2.5 focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs text-text-secondary font-semibold">Event Category</label>
                        <select wire:model="category_id"
                            class="w-full bg-dark-surface border border-dark-border text-text-primary text-sm rounded-lg p-2.5 focus:ring-1 focus:ring-[#2563EB] focus:border-[#2563EB] focus:outline-none">
                            <option value="">No Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Event Speakers --}}
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h3 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-base">mic</span>
                    Event Speakers
                </h3>
                @if($availableSpeakers->isEmpty())
                    <div class="text-xs text-text-secondary italic p-3 bg-dark-surface-lighter rounded-lg text-center">
                        No speakers found.
                        <a href="{{ route('admin.events.speakers.index') }}" class="text-[#2563EB] hover:underline">Add speakers</a>
                    </div>
                @else
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        @foreach($availableSpeakers as $speaker)
                            <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-dark-surface-lighter cursor-pointer transition-colors group">
                                <input type="checkbox" wire:model="speakers" value="{{ $speaker->id }}"
                                    class="w-4 h-4 rounded border-dark-border bg-dark-surface text-[#2563EB] focus:ring-[#2563EB] mt-0.5">
                                <div>
                                    <p class="text-xs font-bold text-text-primary group-hover:text-[#2563EB] transition-colors">{{ $speaker->name }}</p>
                                    <p class="text-[10px] text-text-secondary">{{ $speaker->title ?? 'Speaker' }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="text-xs text-text-secondary text-center pt-2 border-t border-dark-border">
                        {{ count($speakers) }} selected
                    </div>
                @endif
            </div>

            {{-- Featured Image --}}
            <div class="glass-panel rounded-2xl p-6 space-y-4">
                <h3 class="text-sm font-bold text-text-primary">Featured Image</h3>

                {{-- Featured Image / Banner --}}
                <div class="space-y-2">
                    <span class="text-xs text-text-secondary font-semibold block">Featured Image / Banner</span>
                    @if($existingFeaturedImage)
                        <div class="relative group aspect-video rounded-xl overflow-hidden border border-dark-border bg-dark-surface">
                            <img src="{{ $existingFeaturedImage->url }}" class="w-full h-full object-cover" alt="Featured Image">
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-2 transition-all">
                                <button wire:click="removeFeaturedImage" type="button"
                                    class="px-3 py-1.5 bg-red-500/80 hover:bg-red-500 rounded-lg text-xs font-bold text-white flex items-center gap-1 transition-colors">
                                    <span class="material-symbols-outlined text-xs">delete</span> Remove
                                </button>
                            </div>
                        </div>
                    @elseif($featured_image)
                        <div class="relative group aspect-video rounded-xl overflow-hidden border border-dark-border bg-dark-surface">
                            <img src="{{ $featured_image->temporaryUrl() }}" class="w-full h-full object-cover" alt="New Featured Image">
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-2 transition-all">
                                <button wire:click="removeFeaturedImage" type="button"
                                    class="px-3 py-1.5 bg-red-500/80 hover:bg-red-500 rounded-lg text-xs font-bold text-white flex items-center gap-1 transition-colors">
                                    <span class="material-symbols-outlined text-xs">close</span> Remove
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="aspect-video">
                            <livewire:admin.media-picker
                                field="featured_image"
                                label="Set Featured Image"
                                :multiple="false"
                                :shouldClearAfterSelection="false"
                                :compact="false"
                            />
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- TipTap Media Picker Modal --}}
    <livewire:admin.tiptap-media-picker />
</div>
