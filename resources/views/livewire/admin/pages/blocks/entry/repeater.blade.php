{{-- Repeater Block Entry --}}
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Items ({{ count($block['value'] ?? []) }})</label>
        <button wire:click="addRepeaterRow({{ $index }})" type="button"
            class="text-xs font-bold text-primary hover:text-blue-600 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">add</span>
            {{ $block['options']['button_label'] ?? 'Add Item' }}
        </button>
    </div>

    {{-- Data Rows Loop --}}
    @foreach($block['value'] ?? [] as $rowIndex => $row)
        <div class="relative bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4 border border-gray-200 dark:border-[#272B30] group/item mb-4"
            wire:key="repeater-row-{{ $index }}-{{ $rowIndex }}">
            
            {{-- Row Header / Actions --}}
            <div class="flex justify-end mb-2 absolute top-2 right-2 z-10">
                <button wire:click="removeRepeaterRow({{ $index }}, {{ $rowIndex }})" type="button"
                    class="h-6 w-6 rounded hover:bg-red-100 dark:hover:bg-red-500/10 text-[#6F767E] hover:text-red-500 flex items-center justify-center"
                    title="Remove Item">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            {{-- Fields Loop (Schema) --}}
            <div class="space-y-4">
                @foreach($block['children'] ?? [] as $childIndex => $child)
                    <div class="space-y-1" wire:key="repeater-field-{{ $index }}-{{ $rowIndex }}-{{ $childIndex }}">
                        <label class="text-[10px] font-bold text-[#6F767E] uppercase">
                            {{ $child['label'] ?? $child['name'] ?? 'Field' }}
                        </label>
                        
                        @php
                            $fieldName = $child['name'];
                            $fieldValue = $row[$fieldName] ?? '';
                            // Livewire model binding path
                            $modelPath = "blocks.{$index}.value.{$rowIndex}.{$fieldName}";
                        @endphp

                        @switch($child['type'] ?? 'text')
                            @case('textarea')
                                <textarea wire:model="{{ $modelPath }}" rows="2"
                                    class="w-full rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-sm text-[#111827] dark:text-[#FCFCFC] p-3 resize-none"
                                    placeholder="Enter text..."></textarea>
                                @break
                            @case('number')
                                <input wire:model="{{ $modelPath }}" type="number"
                                    class="w-full h-9 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                                    placeholder="0" />
                                @break
                            @case('date')
                                <input wire:model="{{ $modelPath }}" type="date"
                                    class="w-full h-9 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-sm text-[#111827] dark:text-[#FCFCFC] px-3" />
                                @break
                            @case('switcher')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input wire:model="{{ $modelPath }}" type="checkbox" class="sr-only peer" />
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-[#272B30] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                                @break
                            @case('color')
                                <div class="flex items-center gap-2">
                                    <input wire:model.live="{{ $modelPath }}" type="color"
                                        class="w-9 h-9 rounded cursor-pointer border-0 p-0" />
                                    <input wire:model.live="{{ $modelPath }}" type="text"
                                        class="flex-1 h-9 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-sm font-mono text-[#111827] dark:text-[#FCFCFC] px-3"
                                        placeholder="#000000" />
                                </div>
                                @break
                            @case('media')
                                @if($fieldValue)
                                    <div class="relative group/media w-fit">
                                        <img src="{{ asset('storage/' . $fieldValue) }}" alt="Media"
                                            class="h-32 w-auto max-w-full object-contain rounded-lg border border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#1A1A1A]" />
                                        <button wire:click="$set('{{ $modelPath }}', '')" type="button"
                                            class="absolute top-1 right-1 h-6 w-6 rounded-full bg-red-500 text-white opacity-0 group-hover/media:opacity-100 transition-opacity flex items-center justify-center shadow-sm">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="openMediaPicker('repeater_{{ $index }}_{{ $rowIndex }}_{{ $fieldName }}')" type="button"
                                        class="w-full h-16 rounded-lg bg-white dark:bg-[#1A1A1A] border border-dashed border-gray-300 dark:border-[#272B30] flex items-center justify-center gap-2 hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                                        <span class="material-symbols-outlined text-[#6F767E]">add_a_photo</span>
                                        <span class="text-sm text-[#6F767E]">Select Media</span>
                                    </button>
                                @endif
                                @break
                            @default
                                <input wire:model="{{ $modelPath }}" type="text"
                                    class="w-full h-9 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                                    placeholder="Enter value..." />
                        @endswitch
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if(empty($block['value']))
        <div class="text-center py-6 text-[#6F767E] bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl border border-dashed border-gray-200 dark:border-[#272B30]">
            <span class="material-symbols-outlined text-3xl mb-2 block opacity-30">playlist_add</span>
            <p class="text-xs">No items added yet.</p>
            <button wire:click="addRepeaterRow({{ $index }})" type="button" class="text-xs font-bold text-primary mt-2 hover:underline">
                Add your first item
            </button>
        </div>
    @endif
</div>
