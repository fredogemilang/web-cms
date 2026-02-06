{{-- Switcher Block Entry --}}
<div class="space-y-2">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Toggle</label>
    <label class="relative inline-flex items-center cursor-pointer gap-3">
        <input wire:model="blocks.{{ $index }}.value" type="checkbox" class="sr-only peer" />
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer dark:bg-[#272B30] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
        <span class="text-sm text-[#111827] dark:text-[#FCFCFC]">
            {{ $block['value'] ? ($block['options']['on_label'] ?? 'Enabled') : ($block['options']['off_label'] ?? 'Disabled') }}
        </span>
    </label>
</div>
