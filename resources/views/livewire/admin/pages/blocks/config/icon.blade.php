{{-- Icon Block Config --}}
<div class="space-y-4">
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Icon Style</label>
        <select wire:model="blocks.{{ $index }}.options.style"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3">
            <option value="outlined">Outlined</option>
            <option value="rounded">Rounded</option>
            <option value="sharp">Sharp</option>
        </select>
    </div>
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Default Size</label>
        <select wire:model="blocks.{{ $index }}.options.size"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3">
            <option value="text-lg">Small (18px)</option>
            <option value="text-2xl">Medium (24px)</option>
            <option value="text-4xl">Large (36px)</option>
            <option value="text-6xl">Extra Large (60px)</option>
        </select>
    </div>
    <p class="text-[10px] text-[#6F767E] flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">info</span>
        Uses Material Symbols font. Browse icons at
        <a href="https://fonts.google.com/icons" target="_blank" class="text-primary hover:underline">fonts.google.com/icons</a>
    </p>
</div>
