<div class="bg-[#F4F5F6] dark:bg-[#0B0B0B] text-[#111827] dark:text-[#FCFCFC] transition-colors duration-200 antialiased min-h-screen font-sans relative">
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.taxonomies.index') }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-[#111827] dark:text-[#FCFCFC]">
                {{ $isEdit ? 'Edit Taxonomy' : 'Add New Taxonomy' }}
            </h1>
        </div>
        <div class="flex items-center gap-3">
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
            
            <button 
                wire:click="save" 
                wire:loading.attr="disabled"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all disabled:opacity-70 disabled:cursor-not-allowed"
            >
                <span wire:loading wire:target="save" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                <span>{{ $isEdit ? 'Update Taxonomy' : 'Create Taxonomy' }}</span>
            </button>
        </div>
    </header>

    <div class="px-6 pb-20 md:px-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Main Column -->
            <div class="lg:col-span-8 space-y-6">
                <!-- General Settings -->
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-8 border border-transparent dark:border-[#272B30]/50 shadow-sm">
                    <h2 class="text-lg font-bold mb-6 flex items-center gap-2 text-white">
                        <span class="material-symbols-outlined text-blue-600">info</span>
                        General Settings
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Singular Label -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">
                                    Singular Label <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.500ms="singularLabel"
                                    placeholder="e.g. Category"
                                    class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                                >
                                @error('singularLabel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Plural Label -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">
                                    Plural Label <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model="pluralLabel"
                                    placeholder="e.g. Categories"
                                    class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                                >
                                @error('pluralLabel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name (ID) -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">
                                    ID <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model="name"
                                    placeholder="e.g. product_category"
                                    class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                                >
                                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                <p class="text-[10px] text-[#6F767E]">Internal identifier. Lowercase letters, numbers, and underscores only</p>
                            </div>

                            <!-- Slug -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">
                                    Slug <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.500ms="slug"
                                    placeholder="e.g. product-category"
                                    class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                                >
                                @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                <p class="text-[10px] text-[#6F767E]">URL-friendly identifier</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Configuration -->
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-8 border border-transparent dark:border-[#272B30]/50 shadow-sm">
                    <h2 class="text-lg font-bold mb-6 flex items-center gap-2 text-white">
                        <span class="material-symbols-outlined text-blue-600">settings</span>
                        Configuration
                    </h2>

                    <div class="space-y-6">
                        <!-- Type -->
                        <div class="space-y-3">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Taxonomy Type</label>
                            <div class="space-y-3">
                                <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-[#272B30]/40 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all border {{ $isHierarchical ? 'border-blue-500' : 'border-transparent' }}">
                                    <input type="radio" wire:model.live="isHierarchical" name="is_hierarchical" value="1" class="mt-1 w-4 h-4 text-blue-600 focus:ring-blue-500 bg-[#1A1A1A] border-gray-600">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm text-blue-500">account_tree</span>
                                            <span class="font-bold text-sm text-gray-900 dark:text-white">Hierarchical</span>
                                        </div>
                                        <p class="text-xs text-[#6F767E] mt-1">Like Categories. Supports parent-child processing.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-[#272B30]/40 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all border {{ !$isHierarchical ? 'border-orange-500' : 'border-transparent' }}">
                                    <input type="radio" wire:model.live="isHierarchical" name="is_hierarchical" value="0" class="mt-1 w-4 h-4 text-orange-600 focus:ring-orange-500 bg-[#1A1A1A] border-gray-600">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm text-orange-500">label</span>
                                            <span class="font-bold text-sm text-gray-900 dark:text-white">Flat</span>
                                        </div>
                                        <p class="text-xs text-[#6F767E] mt-1">Like Tags. Simple list of terms using commas.</p>
                                    </div>
                                </label>
                            </div>
                            @error('isHierarchical') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
                        </div>

                        <!-- Visibility -->
                        <div class="space-y-3 pt-4 border-t border-gray-200 dark:border-[#272B30]">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Visibility</label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" wire:model="showInMenu" class="peer sr-only">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-[#272B30] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-500 transition-colors">Show in Menu</span>
                            </label>
                            
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" wire:model="showInRest" class="peer sr-only">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-[#272B30] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-500 transition-colors">Show in REST API</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Post Types -->
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-8 border border-transparent dark:border-[#272B30]/50 shadow-sm">
                    <h2 class="text-lg font-bold mb-6 flex items-center gap-2 text-white">
                        <span class="material-symbols-outlined text-blue-600">link</span>
                        Attach directly to
                    </h2>

                    @if($availablePostTypes->count() > 0)
                        <div class="space-y-3">
                            @foreach($availablePostTypes as $cpt)
                                <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-[#272B30]/40 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all {{ in_array($cpt->slug, $postTypes) ? 'ring-1 ring-blue-500/50' : '' }}">
                                    <input 
                                        type="checkbox" 
                                        wire:click="togglePostType('{{ $cpt->slug }}')"
                                        @checked(in_array($cpt->slug, $postTypes))
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-[#1A1A1A] border-gray-600"
                                    >
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm text-gray-500">{{ $cpt->icon }}</span>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $cpt->plural_label }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-700 dark:text-amber-400">
                            <span class="material-symbols-outlined">info</span>
                            <div>
                                <p class="font-medium text-xs">No CPTs found</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
