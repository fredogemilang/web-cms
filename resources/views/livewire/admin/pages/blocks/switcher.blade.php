{{-- Switcher Block --}}
<div class="space-y-2">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Toggle</label>
    <label class="relative inline-flex items-center cursor-pointer">
        <input wire:model="blocks.{{ $index }}.value" type="checkbox"
            class="sr-only peer" />
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:peer-focus:ring-primary/30 rounded-full peer dark:bg-[#272B30] peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
        <span class="ms-3 text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">
            {{ $block['value'] ? 'Enabled' : 'Disabled' }}
        </span>
    </label>
</div>
