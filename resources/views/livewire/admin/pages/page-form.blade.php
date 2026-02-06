<div class="flex flex-col h-full" wire:poll.30s="autosave">
    {{-- Header --}}
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm border-b border-gray-200 dark:border-[#272B30]">
        <div class="flex items-center gap-4">
            <a class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all"
                href="{{ route('admin.pages.index') }}">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">
                    {{ $isEdit ? 'Edit Page' : 'Add New Page' }}
                </h1>
                <div class="flex items-center gap-2 text-xs text-[#6F767E] mt-0.5">
                    @if($status === 'published')
                        <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                        <span>Published</span>
                    @elseif($status === 'draft')
                        <span class="w-2 h-2 rounded-full bg-yellow-500 inline-block"></span>
                        <span>Draft</span>
                    @elseif($status === 'scheduled')
                        <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                        <span>Scheduled</span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-gray-500 inline-block"></span>
                        <span>{{ ucfirst($status) }}</span>
                    @endif
                    @if($lastSavedAt)
                        <span class="mx-1">•</span>
                        <span>Saved at {{ $lastSavedAt }}</span>
                    @elseif($hasUnsavedChanges)
                        <span class="mx-1">•</span>
                        <span class="text-amber-500">Unsaved changes</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="saveAsDraft" wire:loading.attr="disabled"
                class="px-4 py-2 rounded-lg text-sm font-semibold text-[#6F767E] hover:text-[#111827] dark:hover:text-white transition-all disabled:opacity-50">
                <span wire:loading.remove wire:target="saveAsDraft">Save Draft</span>
                <span wire:loading wire:target="saveAsDraft">Saving...</span>
            </button>
            @if($isEdit)
            <a href="{{ url($slug) }}" target="_blank"
                class="px-4 py-2 rounded-lg text-sm font-semibold text-[#111827] dark:text-white bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] hover:border-[#6F767E] transition-all">
                Preview
            </a>
            @endif
            <button wire:click="publish" wire:loading.attr="disabled"
                class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-primary hover:bg-blue-600 shadow-lg shadow-primary/20 transition-all flex items-center gap-2 disabled:opacity-50">
                <span wire:loading.remove wire:target="publish">Publish</span>
                <span wire:loading wire:target="publish">Publishing...</span>
            </button>
        </div>
    </header>

    {{-- Main Content --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Left Panel: Editor --}}
        <div class="flex-1 overflow-y-auto p-10 no-scrollbar">
            <div class="max-w-4xl mx-auto space-y-10">
                {{-- Title & Slug --}}
                <div class="space-y-4">
                    <input wire:model.live.debounce.500ms="title"
                        class="w-full bg-transparent border-none text-5xl font-extrabold text-[#111827] dark:text-[#FCFCFC] placeholder-gray-400 dark:placeholder-[#272B30] focus:ring-0 px-0"
                        placeholder="Enter Page Title..." type="text" />
                    @error('title')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center gap-2 text-sm text-[#6F767E] font-medium bg-white dark:bg-[#1A1A1A] w-fit px-3 py-1.5 rounded-lg border border-gray-200 dark:border-[#272B30]">
                        <span class="material-symbols-outlined text-[16px]">link</span>
                        <span class="opacity-70">{{ url('/') }}/</span>
                        <input wire:model.live.debounce.500ms="slug"
                            class="bg-transparent border-none text-primary focus:ring-0 p-0 text-sm font-medium w-auto min-w-[100px]"
                            type="text" placeholder="page-slug" />
                        <button wire:click="generateSlug" class="ml-2 text-[#6F767E] hover:text-[#111827] dark:hover:text-white">
                            <span class="material-symbols-outlined text-[14px]">refresh</span>
                        </button>
                    </div>
                    @error('slug')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Content Builder --}}
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">Content Builder</h3>
                        <div class="text-xs text-[#6F767E]">{{ count($blocks) }} blocks</div>
                    </div>

                    <div class="builder-dropzone min-h-[400px] rounded-3xl p-8 flex flex-col gap-6 border border-gray-200 dark:border-[#272B30]/30"
                        style="background-image: radial-gradient(#E5E7EB 1px, transparent 1px); background-size: 24px 24px;"
                        x-data="{ darkMode: document.documentElement.classList.contains('dark') }"
                        :style="darkMode ? 'background-image: radial-gradient(#272B30 1px, transparent 1px)' : ''">

                        @forelse($blocks as $index => $block)
                            @include('livewire.admin.pages.blocks._block-wrapper', ['index' => $index, 'block' => $block])
                        @empty
                            <div class="text-center py-12 text-[#6F767E]">
                                <span class="material-symbols-outlined text-5xl mb-4 block opacity-30">widgets</span>
                                <p class="font-medium">No blocks yet</p>
                                <p class="text-sm">Click "Add Block" to start building your page</p>
                            </div>
                        @endforelse

                        {{-- Add Block Button --}}
                        <button wire:click="openBlockSelector"
                            class="w-full h-16 rounded-2xl border-2 border-dashed border-gray-300 dark:border-[#272B30] hover:border-primary/50 text-[#6F767E] hover:text-primary transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">add_circle</span>
                            <span class="font-bold">Add Block</span>
                        </button>
                    </div>

                    @error('blocks')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Right Panel: Settings --}}
        <aside class="w-[320px] bg-white dark:bg-[#1A1A1A] border-l border-gray-200 dark:border-[#272B30] overflow-y-auto no-scrollbar hidden lg:block">
            <div class="p-6 space-y-8">
                {{-- Page Settings --}}
                <div class="space-y-4">
                    <h4 class="text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Page Settings</h4>

                    {{-- Status --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Status</label>
                        <select wire:model="status"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="private">Private</option>
                        </select>
                    </div>

                    {{-- Publish Date --}}
                    @if($status === 'scheduled' || $status === 'published')
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Publish Date</label>
                        <input wire:model="publishedAt" type="datetime-local"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary" />
                    </div>
                    @endif

                    {{-- Template --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Template</label>
                        <select wire:model="template"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary">
                            @foreach($templates as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Parent Page --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Parent Page</label>
                        <select wire:model="parentId"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary">
                            <option value="">None (Top Level)</option>
                            @foreach($parentPages as $parentPage)
                                <option value="{{ $parentPage->id }}">{{ $parentPage->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Menu Order --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Menu Order</label>
                        <input wire:model="menuOrder" type="number" min="0"
                            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary" />
                    </div>
                </div>

                {{-- Featured Image --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Featured Image</h4>
                        @if($featuredImage)
                            <button wire:click="clearFeaturedImage" class="text-xs font-bold text-red-500 hover:text-red-600">Clear</button>
                        @endif
                    </div>

                    @if($featuredImage)
                        <div class="relative aspect-video w-full rounded-2xl overflow-hidden border border-gray-200 dark:border-[#272B30]">
                            <img src="{{ asset('storage/' . $featuredImage) }}" alt="Featured" class="w-full h-full object-cover" />
                            <button wire:click="openMediaPicker('featured_image')"
                                class="absolute inset-0 bg-black/50 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center text-white font-bold">
                                Change Image
                            </button>
                        </div>
                    @else
                        <div wire:click="openMediaPicker('featured_image')"
                            class="aspect-video w-full rounded-2xl bg-[#F4F5F6] dark:bg-[#0B0B0B] border-2 border-dashed border-gray-200 dark:border-[#272B30] flex flex-col items-center justify-center gap-2 hover:bg-gray-100 dark:hover:bg-[#1A1A1A] transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-3xl text-gray-300 dark:text-[#272B30]">image</span>
                            <span class="text-[10px] font-bold text-[#6F767E] uppercase">Set Featured Image</span>
                        </div>
                    @endif
                </div>

                {{-- SEO Settings --}}
                <div class="space-y-4">
                    <button wire:click="toggleSeoSettings" class="w-full flex items-center justify-between group">
                        <h4 class="text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">SEO Settings</h4>
                        <span class="material-symbols-outlined text-[#6F767E] group-hover:text-[#FCFCFC] transition-colors transform {{ $showSeoSettings ? 'rotate-180' : '' }}">expand_more</span>
                    </button>

                    @if($showSeoSettings)
                    <div class="space-y-4 animate-in slide-in-from-top-2">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Meta Title</label>
                            <input wire:model="metaTitle" type="text"
                                class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary"
                                placeholder="Enter meta title..." />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">Meta Description</label>
                            <textarea wire:model="metaDescription" rows="3"
                                class="w-full rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary p-3 resize-none"
                                placeholder="Enter meta description..."></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">OG Title</label>
                            <input wire:model="ogTitle" type="text"
                                class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary"
                                placeholder="Open Graph title..." />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">OG Description</label>
                            <textarea wire:model="ogDescription" rows="2"
                                class="w-full rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary p-3 resize-none"
                                placeholder="Open Graph description..."></textarea>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </aside>
    </div>

    {{-- Block Selector Modal --}}
    @if($showBlockSelector)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm"
        x-data x-on:keydown.escape.window="$wire.closeBlockSelector()">
        <div class="w-full max-w-[640px] bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-[32px] shadow-2xl flex flex-col max-h-[90vh]"
            x-on:click.outside="$wire.closeBlockSelector()">
            <div class="flex items-center justify-between p-8 border-b border-gray-100 dark:border-[#272B30]">
                <div>
                    <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Add Block</h3>
                    <p class="text-sm text-[#6F767E]">Select a field type to add to your content</p>
                </div>
                <button wire:click="closeBlockSelector"
                    class="h-10 w-10 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($blockTypes as $type => $config)
                        <button wire:click="addBlock('{{ $type }}')"
                            class="group flex flex-col items-center gap-3 p-4 rounded-2xl border border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] hover:bg-gray-50 dark:hover:bg-[#272B30] hover:border-primary transition-all">
                            <div class="h-12 w-12 rounded-xl {{ $colorClasses[$config['color']] ?? 'bg-gray-500/10 text-gray-500' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-2xl">{{ $config['icon'] }}</span>
                            </div>
                            <span class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $config['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="p-8 border-t border-gray-100 dark:border-[#272B30] flex justify-end">
                <button wire:click="closeBlockSelector"
                    class="px-6 py-2.5 rounded-xl text-sm font-bold text-[#111827] dark:text-[#FCFCFC] bg-gray-100 dark:bg-[#272B30] hover:brightness-95 transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Media Picker Modal --}}
    @if($showMediaPicker)
        <livewire:admin.media-picker :field="$mediaPickerField" />
    @endif
</div>
