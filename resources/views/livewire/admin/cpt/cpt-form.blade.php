<div x-data="{ iconPickerOpen: false }" class="bg-[#F4F5F6] dark:bg-[#0B0B0B] text-[#111827] dark:text-[#FCFCFC] transition-colors duration-200 antialiased min-h-screen font-sans relative">
    <header class="sticky top-0 z-30 flex flex-col gap-6 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.cpt.index') }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-[#111827] dark:text-[#FCFCFC]">
                {{ $isEdit ? 'Edit Custom Post Type' : 'Add New Custom Post Type' }}
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
            @if($isEdit)
                <button 
                    type="button"
                    wire:click="delete"
                    wire:confirm="Are you sure you want to delete this Post Type? This will delete all associated entries and settings."
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-red-600 bg-red-50 dark:bg-red-900/10 border border-transparent hover:border-red-200 hover:bg-red-100 dark:hover:bg-red-900/20 transition-all"
                >
                    Delete
                </button>
            @endif
            <button 
                wire:click="save" 
                wire:loading.attr="disabled"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all disabled:opacity-70 disabled:cursor-not-allowed"
            >
                <span wire:loading wire:target="save" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                <span>{{ $isEdit ? 'Update Post Type' : 'Publish' }}</span>
            </button>
        </div>
    </header>

    <div class="px-6 pb-20 md:px-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Main Column -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Post Type Labels -->
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-8 border border-transparent dark:border-[#272B30]/50 shadow-sm">
                    <h2 class="text-lg font-bold mb-6 flex items-center gap-2 text-gray-900 dark:text-white">
                        <span class="material-symbols-outlined text-blue-600">label</span>
                        Post Type Labels
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Singular Name</label>
                            <input 
                                wire:model.live.debounce.500ms="singularLabel"
                                class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                                placeholder="e.g. Portfolio Item" type="text" 
                            />
                            @error('singularLabel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Plural Name</label>
                            <input 
                                wire:model.live.debounce.500ms="pluralLabel"
                                class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                                placeholder="e.g. Portfolio Items" type="text" 
                            />
                            @error('pluralLabel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">ID (Internal)</label>
                            <input 
                                wire:model.live.debounce.500ms="name"
                                class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                                placeholder="e.g. portfolio_item" type="text" 
                            />
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Post Type Slug</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-4 rounded-l-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] text-sm border-r border-[#0B0B0B]">
                                    /
                                </span>
                                <input 
                                    wire:model.live.debounce.500ms="slug"
                                    class="w-full rounded-r-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                                    placeholder="portfolio" type="text" 
                                />
                            </div>
                            @error('slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Menu Icon -->
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-8 border border-transparent dark:border-[#272B30]/50 shadow-sm">
                    <h2 class="text-lg font-bold mb-6 flex items-center gap-2 text-gray-900 dark:text-white">
                        <span class="material-symbols-outlined text-blue-600">category</span>
                        Menu Icon
                    </h2>
                    <div class="space-y-4">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Dashboard Icon</label>
                        <div class="grid grid-cols-6 sm:grid-cols-10 gap-3 mt-4">
                            @php
                                $commonIcons = ['article', 'work', 'group', 'shopping_cart', 'grid_view', 'settings', 'person', 'home', 'star', 'favorite'];
                            @endphp
                            @foreach($commonIcons as $iconName)
                                <button 
                                    type="button"
                                    wire:click="selectIcon('{{ $iconName }}')"
                                    class="h-12 w-12 flex items-center justify-center rounded-xl transition-all {{ $icon === $iconName ? 'bg-blue-600 text-white ring-2 ring-blue-600 ring-offset-4 ring-offset-white dark:ring-offset-[#1A1A1A]' : 'bg-gray-100 dark:bg-[#272B30]/40 text-[#6F767E] hover:text-gray-900 dark:hover:text-[#FCFCFC] hover:bg-gray-200 dark:hover:bg-[#272B30]' }}"
                                >
                                    <span class="material-symbols-outlined">{{ $iconName }}</span>
                                </button>
                            @endforeach
                            
                            @if(!in_array($icon, $commonIcons))
                                <button 
                                    type="button"
                                    @click="iconPickerOpen = true"
                                    class="h-12 w-12 flex items-center justify-center rounded-xl transition-all bg-blue-600 text-white ring-2 ring-blue-600 ring-offset-4 ring-offset-white dark:ring-offset-[#1A1A1A]"
                                >
                                    <span class="material-symbols-outlined">{{ $icon }}</span>
                                </button>
                            @endif

                            <button 
                                type="button"
                                @click="iconPickerOpen = true"
                                class="h-12 w-12 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-[#272B30]/40 text-blue-600 border-2 border-dashed border-blue-600/40 hover:border-blue-600 hover:bg-blue-600/5 transition-all"
                            >
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Meta Boxes -->
                <div class="space-y-8">
                    @foreach($metaBoxes as $index => $box)
                        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-8 border border-transparent dark:border-[#272B30]/50 shadow-sm space-y-8">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="p-2 text-[#6F767E] cursor-move">
                                        <span class="material-symbols-outlined">drag_indicator</span>
                                    </div>
                                    <h2 class="text-lg font-bold flex items-center gap-2 text-gray-900 dark:text-white">
                                        <span class="material-symbols-outlined text-blue-600">view_quilt</span>
                                        {{ $box['title'] }}
                                        <span class="text-[10px] text-[#6F767E] font-mono ml-2">ID: {{ $box['id'] }}</span>
                                    </h2>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="toggleMetaBoxSettings('{{ $box['id'] }}')" class="p-2 text-[#6F767E] hover:text-blue-400 transition-colors">
                                        <span class="material-symbols-outlined">settings</span>
                                    </button>
                                    <button type="button" wire:click="confirmDeleteMetaBox({{ $index }})" class="p-2 text-[#6F767E] hover:text-red-400 transition-colors">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Box Settings (Collapsible) -->
                            @if($openMetaBoxes[$box['id']] ?? false)
                                <div class="p-6 bg-gray-50 dark:bg-[#0B0B0B]/30 rounded-2xl border border-gray-200 dark:border-[#272B30] grid grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Box Title</label>
                                        <input type="text" wire:model="metaBoxes.{{ $index }}.title" class="w-full rounded-xl border-none bg-white dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Context</label>
                                        <select wire:model="metaBoxes.{{ $index }}.context" class="w-full rounded-xl border-none bg-white dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 transition-all">
                                            <option value="normal">Normal</option>
                                            <option value="side">Side</option>
                                            <option value="advanced">Advanced</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="border border-gray-200 dark:border-[#272B30] rounded-2xl overflow-hidden bg-gray-50 dark:bg-[#0B0B0B]/30">
                                <div class="p-6 space-y-8">
                                    @php
                                        $boxFields = array_filter($metaFields, fn($f) => ($f['field_group'] ?? '') === $box['id']);
                                    @endphp

                                    @foreach($metaFields as $fieldIndex => $field)
                                        @if(($field['field_group'] ?? '') === $box['id'])
                                            <div class="bg-white dark:bg-[#272B30]/20 rounded-xl p-5 border border-gray-200 dark:border-[#272B30] relative group mb-4 last:mb-0">
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Field Label</label>
                                                        <input 
                                                            type="text" 
                                                            wire:model="metaFields.{{ $fieldIndex }}.label" 
                                                            x-on:input="
                                                                let slug = $el.value.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_');
                                                                let idInput = $el.parentElement.nextElementSibling.querySelector('input');
                                                                if (idInput) {
                                                                    idInput.value = slug;
                                                                    idInput.dispatchEvent(new Event('input', { bubbles: true }));
                                                                }
                                                            "
                                                            placeholder="e.g. Client Name" 
                                                            class="w-full rounded-lg border-none bg-gray-100 dark:bg-[#272B30]/60 py-2 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600 transition-all"
                                                        >
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Field ID</label>
                                                        <input type="text" wire:model="metaFields.{{ $fieldIndex }}.name" placeholder="e.g. client_name" class="w-full rounded-lg border-none bg-gray-100 dark:bg-[#272B30]/60 py-2 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600 transition-all">
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Field Type</label>
                                                        <select wire:model.live="metaFields.{{ $fieldIndex }}.type" class="w-full rounded-lg border-none bg-gray-100 dark:bg-[#272B30]/60 py-2 px-3 text-xs text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-600 transition-all">
                                                            @foreach($fieldTypes as $key => $type)
                                                                <option value="{{ $key }}">{{ $type['label'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="space-y-2">
                                                        <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Description</label>
                                                        <input type="text" wire:model="metaFields.{{ $fieldIndex }}.description" placeholder="Enter description..." class="w-full rounded-lg border-none bg-gray-100 dark:bg-[#272B30]/60 py-2 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600 transition-all">
                                                    </div>
                                                </div>

                                                {{-- Field Options (Select, Radio, Checkbox) --}}
                                                @if(in_array($field['type'], ['select', 'radio', 'checkbox']))
                                                    <div class="mt-4 p-4 bg-gray-50 dark:bg-[#0B0B0B]/30 rounded-xl border border-gray-200 dark:border-[#272B30]">
                                                        <div class="flex items-center justify-between mb-4">
                                                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Options</h4>
                                                            <button 
                                                                type="button" 
                                                                wire:click="addFieldOption({{ $fieldIndex }})" 
                                                                class="text-xs text-blue-600 font-bold hover:text-blue-500 transition-colors flex items-center gap-1"
                                                            >
                                                                <span class="material-symbols-outlined text-sm">add</span>
                                                                Add Option
                                                            </button>
                                                        </div>
                                                        <div class="space-y-2">
                                                            @foreach($field['options']['options_list'] ?? [] as $optionIndex => $option)
                                                                <div class="flex items-center gap-2">
                                                                    <div class="flex-1 grid grid-cols-2 gap-2">
                                                                        <input 
                                                                            type="text" 
                                                                            wire:model="metaFields.{{ $fieldIndex }}.options.options_list.{{ $optionIndex }}.label"
                                                                            x-on:input="
                                                                                let slug = $el.value.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_');
                                                                                let valueInput = $el.nextElementSibling;
                                                                                if (valueInput) {
                                                                                    valueInput.value = slug;
                                                                                    valueInput.dispatchEvent(new Event('input', { bubbles: true }));
                                                                                }
                                                                            "
                                                                            placeholder="Label" 
                                                                            class="w-full rounded-lg border-none bg-gray-100 dark:bg-[#272B30]/60 py-2 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600 transition-all"
                                                                        >
                                                                        <input 
                                                                            type="text" 
                                                                            wire:model="metaFields.{{ $fieldIndex }}.options.options_list.{{ $optionIndex }}.value"
                                                                            placeholder="Value" 
                                                                            class="w-full rounded-lg border-none bg-gray-100 dark:bg-[#272B30]/60 py-2 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600 transition-all"
                                                                        >
                                                                    </div>
                                                                        <label class="flex items-center gap-2 cursor-pointer" title="Is Default">
                                                                            <input type="checkbox" wire:model="metaFields.{{ $fieldIndex }}.options.options_list.{{ $optionIndex }}.is_default" class="rounded border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-blue-600 focus:ring-blue-600 h-4 w-4">
                                                                        </label>
                                                                    <button 
                                                                        type="button" 
                                                                        wire:click="removeFieldOption({{ $fieldIndex }}, {{ $optionIndex }})" 
                                                                        class="p-1.5 text-[#6F767E] hover:text-red-500 transition-colors"
                                                                    >
                                                                        <span class="material-symbols-outlined text-sm">close</span>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                            @if(empty($field['options']['options_list'] ?? []))
                                                                <div class="text-center py-4 text-[#6F767E] text-xs italic">
                                                                    No options added yet.
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Repeater Fields --}}
                                                @if($field['type'] === 'repeater')
                                                    <div class="mt-4 p-4 bg-gray-50 dark:bg-[#0B0B0B]/30 rounded-xl border border-gray-200 dark:border-[#272B30]">
                                                        <div class="flex items-center justify-between mb-4">
                                                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Repeater Fields</h4>
                                                            <button 
                                                                type="button" 
                                                                wire:click="addRepeaterField({{ $fieldIndex }})" 
                                                                class="text-xs text-blue-600 font-bold hover:text-blue-500 transition-colors flex items-center gap-1"
                                                            >
                                                                <span class="material-symbols-outlined text-sm">add</span>
                                                                Add Field
                                                            </button>
                                                        </div>
                                                        <div class="space-y-3">
                                                            @foreach($field['options']['repeater_fields'] ?? [] as $subFieldIndex => $subField)
                                                                <div class="bg-gray-100 dark:bg-[#272B30]/40 rounded-lg p-3 border border-gray-200 dark:border-[#272B30] relative group">
                                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                                        <div class="space-y-1">
                                                                            <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Label</label>
                                                                            <input 
                                                                                type="text" 
                                                                                wire:model="metaFields.{{ $fieldIndex }}.options.repeater_fields.{{ $subFieldIndex }}.label"
                                                                                x-on:input="
                                                                                    let slug = $el.value.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_');
                                                                                    let idInput = $el.parentElement.nextElementSibling.querySelector('input');
                                                                                    if (idInput) {
                                                                                        idInput.value = slug;
                                                                                        idInput.dispatchEvent(new Event('input', { bubbles: true }));
                                                                                    }
                                                                                "
                                                                                placeholder="Label" 
                                                                                class="w-full rounded-lg border-none bg-white dark:bg-[#272B30] py-1.5 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600"
                                                                            >
                                                                        </div>
                                                                        <div class="space-y-1">
                                                                            <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">ID</label>
                                                                            <input type="text" wire:model="metaFields.{{ $fieldIndex }}.options.repeater_fields.{{ $subFieldIndex }}.name" placeholder="id" class="w-full rounded-lg border-none bg-white dark:bg-[#272B30] py-1.5 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600">
                                                                        </div>
                                                                        <div class="space-y-1">
                                                                            <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Type</label>
                                                                            <select wire:model.live="metaFields.{{ $fieldIndex }}.options.repeater_fields.{{ $subFieldIndex }}.type" class="w-full rounded-lg border-none bg-white dark:bg-[#272B30] py-1.5 px-3 text-xs text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-600">
                                                                                @foreach($fieldTypes as $k => $t)
                                                                                    @if($k !== 'repeater') {{-- Prevent nested repeaters for now to keep it simple --}}
                                                                                        <option value="{{ $k }}">{{ $t['label'] }}</option>
                                                                                    @endif
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- Sub Field Options (Dynamic Builder) --}}
                                                                    @if(in_array($subField['type'] ?? 'text', ['select', 'radio', 'checkbox']))
                                                                        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-[#272B30]/50">
                                                                            <div class="flex items-center justify-between mb-2">
                                                                                <div class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Options</div>
                                                                                <button 
                                                                                    type="button" 
                                                                                    wire:click="addRepeaterFieldOption({{ $fieldIndex }}, {{ $subFieldIndex }})" 
                                                                                    class="text-[10px] text-blue-600 font-bold hover:text-blue-500 transition-colors flex items-center gap-1"
                                                                                >
                                                                                    <span class="material-symbols-outlined text-xs">add</span>
                                                                                    Add Option
                                                                                </button>
                                                                            </div>
                                                                            
                                                                            <div class="space-y-2">
                                                                                @foreach($subField['options']['options_list'] ?? [] as $optionIndex => $option)
                                                                                    <div class="flex items-center gap-2">
                                                                                        <div class="flex-1 grid grid-cols-2 gap-2">
                                                                                            <input 
                                                                                                type="text" 
                                                                                                wire:model="metaFields.{{ $fieldIndex }}.options.repeater_fields.{{ $subFieldIndex }}.options.options_list.{{ $optionIndex }}.label"
                                                                                                x-on:input="
                                                                                                    let slug = $el.value.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_');
                                                                                                    let valueInput = $el.nextElementSibling;
                                                                                                    if (valueInput) {
                                                                                                        valueInput.value = slug;
                                                                                                        valueInput.dispatchEvent(new Event('input', { bubbles: true }));
                                                                                                    }
                                                                                                "
                                                                                                placeholder="Label" 
                                                                                                class="w-full rounded-lg border-none bg-white dark:bg-[#272B30] py-1.5 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600"
                                                                                            >
                                                                                            <input 
                                                                                                type="text" 
                                                                                                wire:model="metaFields.{{ $fieldIndex }}.options.repeater_fields.{{ $subFieldIndex }}.options.options_list.{{ $optionIndex }}.value"
                                                                                                placeholder="Value" 
                                                                                                class="w-full rounded-lg border-none bg-white dark:bg-[#272B30] py-1.5 px-3 text-xs text-gray-900 dark:text-white placeholder-[#6F767E] focus:ring-1 focus:ring-blue-600"
                                                                                            >
                                                                                        </div>
                                                                                        <label class="flex items-center gap-2 cursor-pointer" title="Is Default">
                                                                                            <input type="checkbox" wire:model="metaFields.{{ $fieldIndex }}.options.repeater_fields.{{ $subFieldIndex }}.options.options_list.{{ $optionIndex }}.is_default" class="rounded border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-blue-600 focus:ring-blue-600 h-4 w-4">
                                                                                        </label>
                                                                                        <button 
                                                                                            type="button" 
                                                                                            wire:click="removeRepeaterFieldOption({{ $fieldIndex }}, {{ $subFieldIndex }}, {{ $optionIndex }})" 
                                                                                            class="p-1 text-[#6F767E] hover:text-red-500 transition-colors"
                                                                                        >
                                                                                            <span class="material-symbols-outlined text-xs">close</span>
                                                                                        </button>
                                                                                    </div>
                                                                                @endforeach
                                                                                
                                                                                @if(empty($subField['options']['options_list'] ?? []))
                                                                                    <div class="text-center py-2 text-[#6F767E] text-[10px] italic">
                                                                                        No options added.
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                    {{-- Sub Field Required Toggle --}}
                                                                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-[#272B30]/50 flex items-center justify-between">
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="material-symbols-outlined text-blue-600 text-[10px]">priority_high</span>
                                                                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Required</span>
                                                                        </div>
                                                                        <label class="relative inline-flex items-center cursor-pointer">
                                                                            <input type="checkbox" wire:model="metaFields.{{ $fieldIndex }}.options.repeater_fields.{{ $subFieldIndex }}.is_required" class="sr-only peer">
                                                                            <div class="w-7 h-4 bg-gray-200 dark:bg-[#272B30] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-[#6F767E] peer-checked:after:bg-blue-600 after:border-gray-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-blue-600/20"></div>
                                                                        </label>
                                                                    </div>

                                                                    <button 
                                                                        type="button" 
                                                                        wire:click="removeRepeaterField({{ $fieldIndex }}, {{ $subFieldIndex }})" 
                                                                        class="absolute -right-2 -top-2 h-5 w-5 rounded-full bg-red-500/20 text-red-500 border border-red-500/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 hover:text-white"
                                                                    >
                                                                        <span class="material-symbols-outlined text-[10px]">close</span>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                            @if(empty($field['options']['repeater_fields'] ?? []))
                                                                <div class="text-center py-4 text-[#6F767E] text-xs italic">
                                                                    No repeater fields added yet.
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Required Toggle -->
                                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-[#272B30]">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-2">
                                                            <span class="material-symbols-outlined text-blue-600 text-sm">priority_high</span>
                                                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#6F767E]">Required Field</span>
                                                        </div>
                                                        <label class="relative inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" wire:model="metaFields.{{ $fieldIndex }}.is_required" class="sr-only peer">
                                                            <div class="w-9 h-5 bg-gray-200 dark:bg-[#272B30] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-[#6F767E] peer-checked:after:bg-blue-600 after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600/20"></div>
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Conditional Logic -->
                                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-[#272B30]">
                                                    <div class="flex items-center justify-between mb-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="material-symbols-outlined text-blue-600 text-sm">filter_alt</span>
                                                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#6F767E]">Conditional Logic</span>
                                                        </div>
                                                        <label class="relative inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" wire:model.live="metaFields.{{ $fieldIndex }}.options.conditional_logic.enabled" class="sr-only peer">
                                                            <div class="w-9 h-5 bg-gray-200 dark:bg-[#272B30] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-[#6F767E] peer-checked:after:bg-blue-600 after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600/20"></div>
                                                        </label>
                                                    </div>

                                                    @if($field['options']['conditional_logic']['enabled'] ?? false)
                                                        <div class="space-y-4 mt-4">
                                                            <div class="flex items-center gap-3 mb-4">
                                                                <span class="text-[11px] text-[#6F767E] font-medium uppercase tracking-tight">Show this field if</span>
                                                                <select wire:model="metaFields.{{ $fieldIndex }}.options.conditional_logic.relation" class="bg-gray-100 dark:bg-[#0B0B0B] border-none rounded-lg py-1 px-3 text-[11px] text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-600">
                                                                    <option value="all">ALL (AND)</option>
                                                                    <option value="any">ANY (OR)</option>
                                                                </select>
                                                                <span class="text-[11px] text-[#6F767E] font-medium uppercase tracking-tight">rules match:</span>
                                                            </div>

                                                            <div class="space-y-3">
                                                                @foreach($field['options']['conditional_logic']['rules'] ?? [] as $ruleIndex => $rule)
                                                                    <div class="flex items-center gap-3">
                                                                        <select wire:model="metaFields.{{ $fieldIndex }}.options.conditional_logic.rules.{{ $ruleIndex }}.field" class="flex-1 bg-gray-100 dark:bg-[#272B30]/40 border-none rounded-lg text-xs text-gray-900 dark:text-white px-3 py-2 focus:ring-1 focus:ring-blue-600">
                                                                            <option value="">Select Field</option>
                                                                            @foreach($metaFields as $otherField)
                                                                                @if($otherField['name'] !== $field['name'])
                                                                                    <option value="{{ $otherField['name'] }}">{{ $otherField['label'] }}</option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                        <select wire:model="metaFields.{{ $fieldIndex }}.options.conditional_logic.rules.{{ $ruleIndex }}.operator" class="w-32 bg-gray-100 dark:bg-[#272B30]/40 border-none rounded-lg text-xs text-gray-900 dark:text-white px-3 py-2 focus:ring-1 focus:ring-blue-600">
                                                                            <option value="equals">Equals</option>
                                                                            <option value="not_equals">Not Equals</option>
                                                                            <option value="contains">Contains</option>
                                                                        </select>
                                                                        <input type="text" wire:model="metaFields.{{ $fieldIndex }}.options.conditional_logic.rules.{{ $ruleIndex }}.value" class="flex-1 bg-gray-100 dark:bg-[#272B30]/40 border-none rounded-lg text-xs text-gray-900 dark:text-white px-3 py-2 focus:ring-1 focus:ring-blue-600" placeholder="Value">
                                                                        <button type="button" wire:click="removeConditionalRule({{ $fieldIndex }}, {{ $ruleIndex }})" class="p-1.5 text-[#6F767E] hover:text-red-400">
                                                                            <span class="material-symbols-outlined text-base">remove_circle</span>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                            <button type="button" wire:click="addConditionalRule({{ $fieldIndex }})" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-bold text-blue-600 bg-blue-600/5 border border-blue-600/20 hover:bg-blue-600/10 transition-all uppercase tracking-wider mt-2">
                                                                <span class="material-symbols-outlined text-sm">add</span>
                                                                Add Rule
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>

                                                <button type="button" wire:click="removeField({{ $fieldIndex }})" class="absolute -right-2 -top-2 h-6 w-6 rounded-full bg-red-500/20 text-red-500 border border-red-500/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <span class="material-symbols-outlined text-xs">close</span>
                                                </button>
                                            </div>
                                        @endif
                                    @endforeach

                                    <button 
                                        type="button"
                                        wire:click="addField('{{ $box['id'] }}')"
                                        class="w-full flex items-center justify-center gap-2 py-3 border-2 border-dashed border-gray-300 dark:border-[#272B30] rounded-xl text-[#6F767E] hover:text-gray-900 dark:hover:text-white hover:border-blue-600/50 transition-all text-xs font-bold uppercase tracking-wider"
                                    >
                                        <span class="material-symbols-outlined text-sm">add</span>
                                        Add Meta Field
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-center pt-4">
                        <button 
                            type="button"
                            wire:click="openMetaBoxModal()"
                            class="flex items-center gap-2 px-8 py-4 rounded-2xl text-sm font-bold text-gray-900 dark:text-white bg-white dark:bg-[#272B30]/40 border border-gray-200 dark:border-[#272B30] hover:border-blue-600/50 hover:bg-blue-600/5 transition-all"
                        >
                            <span class="material-symbols-outlined">add_circle</span>
                            Create Another Meta Box
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Settings -->
                <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-8 border border-transparent dark:border-[#272B30]/50 shadow-sm">
                    <h2 class="text-lg font-bold mb-6 flex items-center gap-2 text-gray-900 dark:text-white">
                        <span class="material-symbols-outlined text-blue-600">settings</span>
                        Settings
                    </h2>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-900 dark:text-[#FCFCFC]">Show in Menu</span>
                                <span class="text-[11px] text-[#6F767E]">Display in admin sidebar</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="showInMenu" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-[#272B30] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-[#6F767E] peer-checked:after:bg-blue-600 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600/20"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-900 dark:text-[#FCFCFC]">Show in REST API</span>
                                <span class="text-[11px] text-[#6F767E]">Expose via REST endpoints</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="showInRest" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-[#272B30] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-[#6F767E] peer-checked:after:bg-blue-600 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600/20"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-900 dark:text-[#FCFCFC]">Has Archive</span>
                                <span class="text-[11px] text-[#6F767E]">Enable archive pages</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="hasArchive" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-[#272B30] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-[#6F767E] peer-checked:after:bg-blue-600 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600/20"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-900 dark:text-[#FCFCFC]">Hierarchical</span>
                                <span class="text-[11px] text-[#6F767E]">Allow parent/child</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="isHierarchical" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 dark:bg-[#272B30] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-[#6F767E] peer-checked:after:bg-blue-600 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600/20"></div>
                            </label>
                        </div>
                        
                        <hr class="border-gray-200 dark:border-[#272B30]" />
                        
                        <div class="space-y-3">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E] block mb-2">Supports</label>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($availableSupports as $key => $label)
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input 
                                            type="checkbox" 
                                            wire:click="toggleSupport('{{ $key }}')"
                                            @checked(in_array($key, $supports))
                                            class="h-5 w-5 rounded border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-blue-600 focus:ring-blue-600 focus:ring-offset-[#1A1A1A] transition-all"
                                        />
                                        <span class="text-sm text-[#6F767E] group-hover:text-gray-900 dark:group-hover:text-[#FCFCFC]">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-[#272B30]" />

                        <div class="space-y-3">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E] block mb-2">Taxonomies</label>
                            <div class="space-y-3">
                                @foreach($availableTaxonomies as $taxonomy)
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input 
                                            type="checkbox" 
                                            wire:click="toggleTaxonomy('{{ $taxonomy->slug }}')"
                                            @checked(in_array($taxonomy->slug, $taxonomies))
                                            class="h-5 w-5 rounded border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-blue-600 focus:ring-blue-600 focus:ring-offset-[#1A1A1A] transition-all"
                                        />
                                        <span class="text-sm text-[#6F767E] group-hover:text-gray-900 dark:group-hover:text-[#FCFCFC]">{{ $taxonomy->plural_label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Meta Box Modal -->
    <div 
        x-data="{ open: @entangle('showMetaBoxModal').live }"
        x-show="open" 
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-modal="true"
    >
        <div class="flex min-h-screen items-center justify-center p-4">
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/50 dark:bg-[#0B0B0B]/90 backdrop-blur-xl transition-opacity" 
                @click="open = false; $wire.closeMetaBoxModal()"
            ></div>
            
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-lg bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-3xl shadow-2xl"
            >
                <div class="flex items-center justify-between p-8 border-b border-gray-200 dark:border-[#272B30]">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-600">view_quilt</span>
                        {{ $editingMetaBoxIndex !== null ? 'Edit Meta Box' : 'Add New Meta Box' }}
                    </h3>
                    <button wire:click="closeMetaBoxModal" class="text-[#6F767E] hover:text-gray-900 dark:hover:text-white transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30]/50">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="p-8 space-y-6">
                    <!-- Box Title -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Box Title</label>
                        <input 
                            type="text" 
                            wire:model.live.debounce.500ms="newMetaBox.title"
                            placeholder="e.g. Project Details"
                            class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                        >
                        @error('newMetaBox.title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Box ID -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Box ID (Internal)</label>
                        <input 
                            type="text" 
                            wire:model.live.debounce.500ms="newMetaBox.id"
                            placeholder="e.g. project_details"
                            class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] placeholder-[#6F767E] focus:ring-2 focus:ring-blue-600 transition-all"
                        >
                        @error('newMetaBox.id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Context -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-[#6F767E]">Context</label>
                        <select 
                            wire:model="newMetaBox.context"
                            class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#272B30]/40 py-3 px-4 text-sm text-gray-900 dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 transition-all"
                        >
                            <option value="normal">Normal</option>
                            <option value="side">Side</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 p-8 border-t border-gray-200 dark:border-[#272B30]">
                    <button 
                        type="button"
                        wire:click="closeMetaBoxModal"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-[#6F767E] hover:text-gray-900 dark:hover:text-[#FCFCFC] bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] transition-all"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        wire:click="saveMetaBox"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all"
                    >
                        {{ $editingMetaBoxIndex !== null ? 'Update Box' : 'Add Box' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <div 
        x-show="iconPickerOpen"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none" 
        id="icon-picker-modal"
    >
        <div 
            x-show="iconPickerOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/50 dark:bg-[#0B0B0B]/90 backdrop-blur-xl transition-opacity" 
            @click="iconPickerOpen = false"
        ></div>
        <div 
            x-show="iconPickerOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-2xl mx-auto my-6 z-10 px-4"
        >
            <div class="relative flex flex-col w-full bg-white dark:bg-[#1A1A1A] border border-[#272B30] rounded-3xl shadow-2xl outline-none focus:outline-none">
                <div class="flex items-center justify-between p-8 border-b border-gray-200 dark:border-[#272B30]">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="material-symbols-outlined text-blue-600">auto_awesome</span>
                        Select Menu Icon
                    </h3>
                    <button class="text-[#6F767E] hover:text-gray-900 dark:hover:text-white transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30]/50" @click="iconPickerOpen = false">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="relative p-8 flex-auto">
                    <div class="grid grid-cols-6 sm:grid-cols-8 gap-4 max-h-[380px] overflow-y-auto no-scrollbar pr-2">
                        @php
                            $allIcons = ['article', 'work', 'group', 'shopping_cart', 'grid_view', 'settings', 'person', 'home', 'star', 'favorite', 'description', 'label', 'category', 'dynamic_form', 'list', 'add_box', 'keyboard_arrow_up', 'keyboard_arrow_down', 'arrow_back', 'save', 'close', 'search', 'auto_awesome', 'edit', 'delete', 'visibility', 'archive', 'account_tree', 'layers', 'extension', 'public', 'lock', 'event', 'schedule', 'location_on', 'phone', 'email', 'link', 'image', 'video_library', 'audio_file', 'attachment', 'cloud_upload', 'download', 'sync', 'refresh', 'done', 'error', 'warning', 'info', 'help', 'notifications', 'chat', 'send', 'share', 'more_vert', 'menu', 'apps', 'search', 'filter_list', 'sort', 'view_list', 'view_module', 'view_quilt', 'view_stream', 'view_agenda', 'view_day', 'view_headline', 'view_carousel', 'view_column', 'view_comfy', 'view_compact'];
                        @endphp
                        @foreach($allIcons as $iconName)
                            <button 
                                type="button"
                                wire:click="selectIcon('{{ $iconName }}')"
                                @click="iconPickerOpen = false"
                                class="flex flex-col items-center justify-center p-4 aspect-square rounded-2xl transition-all group {{ $icon === $iconName ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-gray-100 dark:bg-[#272B30]/20 border border-transparent hover:border-blue-600 hover:bg-blue-600/5 text-[#6F767E] hover:text-gray-900 dark:hover:text-white' }}"
                            >
                                <span class="material-symbols-outlined text-2xl group-hover:scale-125 transition-transform">{{ $iconName }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Meta Box Confirmation Modal -->
    <div 
        x-data="{ open: @entangle('showDeleteMetaBoxModal').live }"
        x-show="open" 
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-modal="true"
    >
        <div class="flex min-h-screen items-center justify-center p-4">
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/50 dark:bg-[#0B0B0B]/90 backdrop-blur-xl transition-opacity" 
                @click="open = false; $wire.cancelDeleteMetaBox()"
            ></div>
            
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-md bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-3xl shadow-2xl overflow-hidden"
            >
                <div class="p-8 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-3xl text-red-500">warning</span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Delete Meta Box?</h3>
                    <p class="text-[#6F767E] text-sm leading-relaxed">
                        You are about to delete this meta box. What would you like to do with the fields inside it?
                    </p>
                </div>
                
                <div class="flex flex-col gap-3 p-8 pt-0">
                    <button 
                        type="button"
                        wire:click="deleteMetaBox(true)"
                        class="w-full py-3.5 rounded-xl text-sm font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-[#272B30] hover:bg-gray-200 dark:hover:bg-[#32363B] transition-all flex items-center justify-center gap-2 group"
                    >
                        <span class="material-symbols-outlined text-yellow-500 group-hover:scale-110 transition-transform">move_item</span>
                        Keep Fields & Move to Uncategorized
                    </button>
                    
                    <button 
                        type="button"
                        wire:click="deleteMetaBox(false)"
                        class="w-full py-3.5 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-600/20 transition-all flex items-center justify-center gap-2 group"
                    >
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">delete_forever</span>
                        Delete Everything
                    </button>
                    
                    <button 
                        type="button"
                        wire:click="cancelDeleteMetaBox"
                        class="w-full py-3 rounded-xl text-xs font-bold text-[#6F767E] hover:text-gray-900 dark:hover:text-white transition-all mt-2"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
