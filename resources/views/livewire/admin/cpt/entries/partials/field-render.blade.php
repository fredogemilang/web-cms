<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        {{ $field->label }}
        @if($field->is_required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    @switch($field->type)
        @case('text')
        @default
            <input 
                type="text"
                wire:model="meta.{{ $field->name }}"
                placeholder="{{ $field->description ?? '' }}"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            @break

        @case('textarea')
            <textarea 
                wire:model="meta.{{ $field->name }}"
                rows="4"
                placeholder="{{ $field->description ?? '' }}"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-y"
            ></textarea>
            @break
        
        @case('number')
            <input 
                type="number"
                wire:model="meta.{{ $field->name }}"
                placeholder="{{ $field->description ?? '' }}"
                @if(isset($field->options['min'])) min="{{ $field->options['min'] }}" @endif
                @if(isset($field->options['max'])) max="{{ $field->options['max'] }}" @endif
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            @break
        
        @case('email')
            <input 
                type="email"
                wire:model="meta.{{ $field->name }}"
                placeholder="{{ $field->description ?? '' }}"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            @break
        
        @case('url')
            <input 
                type="url"
                wire:model="meta.{{ $field->name }}"
                placeholder="{{ $field->description ?? 'https://' }}"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            @break
        
        @case('date')
            <input 
                type="date"
                wire:model="meta.{{ $field->name }}"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            @break
        
        @case('datetime')
            <input 
                type="datetime-local"
                wire:model="meta.{{ $field->name }}"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            @break
        
        @case('switcher')
            <label class="relative inline-flex items-center cursor-pointer">
                <input 
                    type="checkbox" 
                    wire:model="meta.{{ $field->name }}"
                    class="sr-only peer"
                >
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ $field->description ?? 'Enable' }}</span>
            </label>
            @break
        
        @case('select')
            <select 
                wire:model="meta.{{ $field->name }}"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
                <option value="">Select...</option>
                @foreach($field->options['options_list'] ?? [] as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            @break

        @case('radio')
            <div class="space-y-2 pt-1">
                @foreach($field->options['options_list'] ?? [] as $option)
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input 
                            type="radio" 
                            wire:model="meta.{{ $field->name }}" 
                            value="{{ $option['value'] }}"
                            class="w-4 h-4 border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-50 dark:bg-[#272B30] dark:border-gray-700"
                        >
                        <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 transition-colors">{{ $option['label'] }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('checkbox')
            <div class="space-y-2 pt-1">
                @foreach($field->options['options_list'] ?? [] as $option)
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input 
                            type="checkbox" 
                            wire:model="meta.{{ $field->name }}" 
                            value="{{ $option['value'] }}"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-50 dark:bg-[#272B30] dark:border-gray-700"
                        >
                        <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-blue-600 transition-colors">{{ $option['label'] }}</span>
                    </label>
                @endforeach
            </div>
            @break
        
        @case('color')
            <div class="flex items-center gap-3">
                <input 
                    type="color"
                    wire:model="meta.{{ $field->name }}"
                    class="w-12 h-10 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer"
                >
                <input 
                    type="text"
                    wire:model="meta.{{ $field->name }}"
                    placeholder="#000000"
                    class="flex-1 px-4 py-2.5 bg-gray-50 dark:bg-[#272B30] border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                >
            </div>
            @break

        @case('wysiwyg')
            <div wire:ignore x-data="tiptapEditor('meta.{{ $field->name }}')" 
                 @tiptap-undo.window="undo()" 
                 @tiptap-redo.window="redo()"
                 id="field-editor-{{ $field->name }}" class="min-h-[300px] rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#272B30] flex flex-col overflow-hidden">

                <!-- Toolbar -->
                <div class="flex items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 overflow-x-auto flex-wrap bg-white dark:bg-[#1A1A1A]">
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
                    
                    <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

                    <!-- Headings -->
                    <div class="flex items-center gap-0.5">
                        <button type="button" @click="toggleHeading(2)" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('heading', { level: 2 }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Heading 2">
                            <span class="material-symbols-outlined text-[20px]">format_h2</span>
                        </button>
                        <button type="button" @click="toggleHeading(3)" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('heading', { level: 3 }) }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Heading 3">
                            <span class="material-symbols-outlined text-[20px]">format_h3</span>
                        </button>
                    </div>

                    <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

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

                    <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

                    <!-- Lists & Indent -->
                    <div class="flex items-center gap-0.5">
                        <button type="button" @click="toggleBulletList()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('bulletList') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Bullet List">
                            <span class="material-symbols-outlined text-[20px]">format_list_bulleted</span>
                        </button>
                        <button type="button" @click="toggleOrderedList()" :class="{ 'bg-gray-100 dark:bg-[#272B30] text-[#2563EB]': isActive('orderedList') }" class="p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] text-[#6F767E] transition-colors" title="Ordered List">
                            <span class="material-symbols-outlined text-[20px]">format_list_numbered</span>
                        </button>
                    </div>

                    <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

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

                    <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>

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
                <div x-ref="editor" class="flex-1 overflow-y-auto cursor-text relative bg-white dark:bg-[#1A1A1A]"></div>
            </div>
            @break

        @case('media')
            <div>
                <livewire:admin.media-picker 
                    :field="'meta.' . $field->name"
                    :value="$this->meta[$field->name] ?? null"
                    :label="$field->label"
                    :compact="true"
                />
            </div>
            @break

        @case('gallery')
            <div class="space-y-4">
                {{-- Helper to add items --}}
                <livewire:admin.media-picker 
                    :field="'gallery_add.' . $field->name"
                    :label="'Add to ' . $field->label"
                    :shouldClearAfterSelection="true"
                    :compact="true"
                />

                {{-- Grid of selected items --}}
                @if(!empty($this->meta[$field->name]) && is_array($this->meta[$field->name]))
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($this->meta[$field->name] as $index => $path)
                            <div class="relative aspect-square rounded-xl overflow-hidden group border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800">
                                <img src="{{ \Illuminate\Support\Str::startsWith($path, 'http') ? $path : asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button 
                                        type="button"
                                        wire:click="removeGalleryImage('{{ $field->name }}', {{ $index }})"
                                        class="p-2 bg-red-600 rounded-lg text-white hover:bg-red-700 transition-colors"
                                    >
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @break

        @case('repeater')
            <div class="space-y-4">
                {{-- Repeater Rows --}}
                @if(!empty($this->meta[$field->name]) && is_array($this->meta[$field->name]))
                    @foreach($this->meta[$field->name] as $index => $row)
                        <div class="p-4 bg-gray-50 dark:bg-[#1A1A1A] border border-gray-200 dark:border-gray-700 rounded-2xl relative group">
                            {{-- Remove Button --}}
                            <button 
                                type="button" 
                                wire:click="removeRepeaterRow('{{ $field->name }}', {{ $index }})"
                                class="absolute top-2 right-2 p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="Remove Row"
                            >
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>

                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach($field->options['repeater_fields'] ?? [] as $subField)
                                    @php
                                        // Fallback ID generation matches EntryForm
                                        $subFieldId = $subField['id'] ?? \Illuminate\Support\Str::snake($subField['label'] ?? 'field_' . $loop->index);
                                    @endphp
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                            {{ $subField['label'] }}
                                        </label>
                                        
                                        @if($subField['type'] === 'text')
                                            <input 
                                                type="text" 
                                                wire:model="meta.{{ $field->name }}.{{ $index }}.{{ $subFieldId }}"
                                                class="w-full px-3 py-2 bg-white dark:bg-[#0B0B0B] border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            >
                                        @elseif($subField['type'] === 'select')
                                            <select 
                                                wire:model="meta.{{ $field->name }}.{{ $index }}.{{ $subFieldId }}"
                                                class="w-full px-3 py-2 bg-white dark:bg-[#0B0B0B] border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            >
                                                <option value="">Select...</option>
                                                {{-- Handle select options --}}
                                                @if(isset($subField['options']))
                                                    @php
                                                        $optionsSource = $subField['options'];
                                                        if (is_array($optionsSource) && isset($optionsSource['options_list'])) {
                                                            $options = $optionsSource['options_list'];
                                                        } elseif (is_string($optionsSource)) {
                                                            $options = explode(',', $optionsSource);
                                                        } elseif (is_array($optionsSource)) {
                                                            $options = $optionsSource;
                                                        } else {
                                                            $options = [];
                                                        }
                                                    @endphp
                                                    @foreach($options as $opt)
                                                        @php
                                                            if (is_array($opt)) {
                                                                 $val = $opt['value'] ?? $opt['label'] ?? '';
                                                                 $lbl = $opt['label'] ?? $opt['value'] ?? '';
                                                            } else {
                                                                $parts = explode(':', trim($opt));
                                                                $val = $parts[1] ?? $parts[0];
                                                                $lbl = $parts[0];
                                                            }
                                                        @endphp
                                                        <option value="{{ trim($val) }}">{{ trim($lbl) }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @elseif($subField['type'] === 'textarea')
                                            <textarea 
                                                wire:model="meta.{{ $field->name }}.{{ $index }}.{{ $subFieldId }}"
                                                rows="2"
                                                class="w-full px-3 py-2 bg-white dark:bg-[#0B0B0B] border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                            ></textarea>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Add Button --}}
                <button 
                    type="button"
                    wire:click="addRepeaterRow('{{ $field->name }}')"
                    class="w-full py-3 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-500 hover:text-blue-600 hover:border-blue-300 dark:hover:border-blue-700 hover:bg-gray-50 dark:hover:bg-[#1A1A1A] transition-all flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined">add</span>
                    <span>Add Row</span>
                </button>
            </div>
            @break
    @endswitch
    
    @if($field->description && !in_array($field->type, ['switcher']))
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $field->description }}</p>
    @endif
    
    @error('meta.' . $field->name)
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>
