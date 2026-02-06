{{-- Color Block Config --}}
<div class="space-y-4">
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Color Format</label>
        <select wire:model="blocks.{{ $index }}.options.format"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3">
            <option value="hex">HEX (#FF5733)</option>
            <option value="rgb">RGB (rgb(255, 87, 51))</option>
            <option value="hsl">HSL (hsl(14, 100%, 60%))</option>
        </select>
    </div>
    <div class="flex items-center gap-3">
        <input wire:model="blocks.{{ $index }}.options.show_presets" type="checkbox" id="color-presets-{{ $index }}"
            class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" />
        <label for="color-presets-{{ $index }}" class="text-sm text-[#6F767E]">Show color presets</label>
    </div>
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Default Color</label>
        <div class="flex items-center gap-2">
            <input wire:model="blocks.{{ $index }}.options.default_color" type="color"
                class="w-10 h-9 rounded cursor-pointer border-0 p-0" />
            <input wire:model="blocks.{{ $index }}.options.default_color" type="text"
                class="flex-1 h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-mono text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="#000000" />
        </div>
    </div>
</div>
