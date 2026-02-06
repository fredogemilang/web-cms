{{-- Repeater Block --}}
<div class="space-y-4">
    {{-- Repeater Options --}}
    <div class="grid grid-cols-3 gap-2">
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-[#6F767E] uppercase">Min Items</label>
            <input wire:model="blocks.{{ $index }}.options.min_items" type="number" min="0"
                class="w-full h-8 rounded bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-xs text-[#111827] dark:text-[#FCFCFC] px-2"
                placeholder="0" />
        </div>
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-[#6F767E] uppercase">Max Items</label>
            <input wire:model="blocks.{{ $index }}.options.max_items" type="number" min="1"
                class="w-full h-8 rounded bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-xs text-[#111827] dark:text-[#FCFCFC] px-2"
                placeholder="10" />
        </div>
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-[#6F767E] uppercase">Button Label</label>
            <input wire:model="blocks.{{ $index }}.options.button_label" type="text"
                class="w-full h-8 rounded bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-xs text-[#111827] dark:text-[#FCFCFC] px-2"
                placeholder="Add Item" />
        </div>
    </div>

    {{-- Repeater Items --}}
    <div class="space-y-3">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Items ({{ count($block['children'] ?? []) }})</label>

        @foreach($block['children'] ?? [] as $childIndex => $child)
            <div class="relative bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl p-4 border border-gray-200 dark:border-[#272B30] group"
                wire:key="repeater-{{ $index }}-child-{{ $childIndex }}">

                {{-- Item Header --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold text-[#6F767E] uppercase">Item {{ $childIndex + 1 }}</span>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
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

                {{-- Item Fields --}}
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[9px] font-bold text-[#6F767E] uppercase">Name (ID)</label>
                            <input wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.name" type="text"
                                class="w-full h-8 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs font-mono text-[#111827] dark:text-[#FCFCFC] px-3"
                                placeholder="field_name" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-bold text-[#6F767E] uppercase">Type</label>
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
                    <div class="space-y-1">
                        <label class="text-[9px] font-bold text-[#6F767E] uppercase">Value</label>
                        @switch($child['type'] ?? 'text')
                            @case('textarea')
                                <textarea wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.value" rows="2"
                                    class="w-full rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs text-[#111827] dark:text-[#FCFCFC] p-3 resize-none"
                                    placeholder="Enter text..."></textarea>
                                @break
                            @case('number')
                                <input wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.value" type="number"
                                    class="w-full h-8 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs text-[#111827] dark:text-[#FCFCFC] px-3"
                                    placeholder="0" />
                                @break
                            @case('date')
                                <input wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.value" type="date"
                                    class="w-full h-8 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs text-[#111827] dark:text-[#FCFCFC] px-3" />
                                @break
                            @case('switcher')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.value" type="checkbox" class="sr-only peer" />
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-[#272B30] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                                @break
                            @case('color')
                                <div class="flex items-center gap-2">
                                    <input wire:model.live="blocks.{{ $index }}.children.{{ $childIndex }}.value" type="color"
                                        class="w-8 h-8 rounded cursor-pointer border-0 p-0" />
                                    <input wire:model.live="blocks.{{ $index }}.children.{{ $childIndex }}.value" type="text"
                                        class="flex-1 h-8 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs font-mono text-[#111827] dark:text-[#FCFCFC] px-3"
                                        placeholder="#000000" />
                                </div>
                                @break
                            @default
                                <input wire:model="blocks.{{ $index }}.children.{{ $childIndex }}.value" type="text"
                                    class="w-full h-8 rounded bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-xs text-[#111827] dark:text-[#FCFCFC] px-3"
                                    placeholder="Enter value..." />
                        @endswitch
                    </div>
                </div>
            </div>
        @endforeach

        @if(empty($block['children']))
            <div class="text-center py-6 text-[#6F767E] bg-[#F4F5F6] dark:bg-[#0B0B0B] rounded-xl border border-dashed border-gray-200 dark:border-[#272B30]">
                <span class="material-symbols-outlined text-3xl mb-2 block opacity-30">playlist_add</span>
                <p class="text-xs">No items yet. Click the button below to add one.</p>
            </div>
        @endif
    </div>

    {{-- Add Item Button --}}
    @if(count($block['children'] ?? []) < ($block['options']['max_items'] ?? 10))
        <button wire:click="addRepeaterItem({{ $index }})" type="button"
            class="w-full h-10 rounded-xl border border-dashed border-gray-300 dark:border-[#272B30] hover:border-primary text-[#6F767E] hover:text-primary transition-all flex items-center justify-center gap-2 text-sm font-medium">
            <span class="material-symbols-outlined text-lg">add</span>
            {{ $block['options']['button_label'] ?? 'Add Item' }}
        </button>
    @else
        <p class="text-xs text-[#6F767E] text-center">Maximum items reached ({{ $block['options']['max_items'] ?? 10 }})</p>
    @endif
</div>
