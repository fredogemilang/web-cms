{{-- Switcher Block Config --}}
<div class="space-y-4">
    <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">ON Label</label>
            <input wire:model="blocks.{{ $index }}.options.on_label" type="text"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="Enabled" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">OFF Label</label>
            <input wire:model="blocks.{{ $index }}.options.off_label" type="text"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="Disabled" />
        </div>
    </div>
    <div class="flex items-center gap-3">
        <input wire:model="blocks.{{ $index }}.options.default_value" type="checkbox" id="switcher-default-{{ $index }}"
            class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
        <label for="switcher-default-{{ $index }}" class="text-sm text-[#6F767E]">Default to ON</label>
    </div>
</div>
