{{-- Repeater Block Config --}}
<div class="space-y-4">
    {{-- Repeater Options --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Min Items</label>
            <input wire:model="blocks.{{ $index }}.options.min_items" type="number" min="0"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="0" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Max Items</label>
            <input wire:model="blocks.{{ $index }}.options.max_items" type="number" min="1"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="10" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Button Label</label>
            <input wire:model="blocks.{{ $index }}.options.button_label" type="text"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="Add Item" />
        </div>
    </div>

    {{-- Field Definitions --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Fields</label>
            <button wire:click="addRepeaterItem({{ $index }})" type="button"
                class="text-xs font-bold text-primary hover:text-blue-600 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">add</span>
                Add Field
            </button>
        </div>

        @foreach($block['children'] ?? [] as $childIndex => $child)
            <div class="relative bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4 border border-gray-200 dark:border-[#272B30] group/field"
                wire:key="repeater-field-{{ $index }}-{{ $childIndex }}">

                {{-- Field Header --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold text-[#6F767E] uppercase">Field {{ $childIndex + 1 }}</span>
                    <div class="flex items-center gap-1 opacity-0 group-hover/field:opacity-100 transition-opacity">
                        <button wire:click="moveRepeaterItemUp({{ $index }}, {{ $childIndex }})" type="button"
                            @disabled($childIndex === 0)
                            class="h-6 w-6 rounded hover:bg-gray-200 dark:hover:bg-[#272B30] text-[#6F767E] flex items-center justify-center disabled:opacity-30">
                            <span class="material-symbols-outlined text-sm">arrow_upward</span>
                        </button>
                        <button wire:click="moveRepeaterItemDown({{ $index }}, {{ $childIndex }})" type="button"
                            @disabled($childIndex === count($block['children']) - 1)
                            class="h-6 w-6 rounded hover:bg-gray-200 dark:hover:bg-[#272B30] text-[#6F767E] flex items-center justify-center disabled:opacity-30">
                            <span class="material-symbols-outlined text-sm">arrow_downward</span>
                        </button>
                        <button wire:click="removeRepeaterItem({{ $index }}, {{ $childIndex }})" type="button"
                            class="h-6 w-6 rounded hover:bg-red-100 dark:hover:bg-red-500/10 text-[#6F767E] hover:text-red-500 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                </div>

                {{-- Field Config --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[9px] font-bold text-[#6F767E] uppercase">Field Name (ID)</label>
                        <input wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.name" type="text"
                            x-on:input="$event.target.value = $event.target.value.replace(/\s+/g, '_').replace(/[^a-z0-9_]/gi, '').toLowerCase()"
                            class="w-full h-8 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs font-mono text-[#111827] dark:text-[#FCFCFC] px-3"
                            placeholder="field_name" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-bold text-[#6F767E] uppercase">Field Type</label>
                        <select wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.type"
                            class="w-full h-8 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs text-[#111827] dark:text-[#FCFCFC] px-3">
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="media">Media</option>
                            <option value="date">Date</option>
                            <option value="switcher">Switcher</option>
                            <option value="color">Color</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <label class="text-[9px] font-bold text-[#6F767E] uppercase">Label</label>
                    <input wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.label" type="text"
                        class="w-full h-8 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs text-[#111827] dark:text-[#FCFCFC] px-3"
                        placeholder="Field Label" />
                </div>
            </div>
        @endforeach

        @if(empty($block['children']))
            <div class="text-center py-6 text-[#6F767E] bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl border border-dashed border-gray-200 dark:border-[#272B30]">
                <span class="material-symbols-outlined text-3xl mb-2 block opacity-30">playlist_add</span>
                <p class="text-xs">No fields defined yet. Click "Add Field" to configure repeater fields.</p>
            </div>
        @endif
    </div>
</div>
