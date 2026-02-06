{{-- Text Block Config --}}
<div class="space-y-2">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Placeholder</label>
    <input wire:model="blocks.{{ $index }}.options.placeholder" type="text"
        class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
        placeholder="Enter placeholder text..." />
</div>
