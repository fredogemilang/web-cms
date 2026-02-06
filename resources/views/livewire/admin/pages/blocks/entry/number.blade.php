{{-- Number Block Entry --}}
<div class="space-y-2">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Value</label>
    <div class="flex items-center gap-2">
        @if(!empty($block['options']['prefix']))
            <span class="text-sm text-[#6F767E] font-medium">{{ $block['options']['prefix'] }}</span>
        @endif
        <input wire:model="blocks.{{ $index }}.value" type="number"
            min="{{ $block['options']['min'] ?? '' }}"
            max="{{ $block['options']['max'] ?? '' }}"
            step="{{ $block['options']['step'] ?? 1 }}"
            class="flex-1 h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-4"
            placeholder="0" />
        @if(!empty($block['options']['suffix']))
            <span class="text-sm text-[#6F767E] font-medium">{{ $block['options']['suffix'] }}</span>
        @endif
    </div>
</div>
