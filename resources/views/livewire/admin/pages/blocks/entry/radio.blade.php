{{-- Radio Block Entry --}}
<div class="space-y-2">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Select Option</label>
    <div class="space-y-2">
        @foreach($block['options']['choices'] ?? [] as $choiceIndex => $choice)
            <label class="flex items-center gap-3 p-3 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] cursor-pointer hover:bg-gray-200 dark:hover:bg-[#272B30] transition-colors">
                <input wire:model="blocks.{{ $index }}.value" type="radio"
                    name="block_{{ $index }}_radio" value="{{ $choice['value'] }}"
                    class="w-4 h-4 text-primary focus:ring-primary border-gray-300" />
                <span class="text-sm text-[#111827] dark:text-[#FCFCFC]">{{ $choice['label'] }}</span>
            </label>
        @endforeach
        @if(empty($block['options']['choices']))
            <p class="text-xs text-[#6F767E] italic">No options configured. Edit settings to add choices.</p>
        @endif
    </div>
</div>
