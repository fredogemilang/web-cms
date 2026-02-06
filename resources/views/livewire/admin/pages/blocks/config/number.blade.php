{{-- Number Block Config --}}
<div class="space-y-4">
    <div class="grid grid-cols-3 gap-3">
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Min Value</label>
            <input wire:model="blocks.{{ $index }}.options.min" type="number"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="—" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Max Value</label>
            <input wire:model="blocks.{{ $index }}.options.max" type="number"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="—" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Step</label>
            <input wire:model="blocks.{{ $index }}.options.step" type="number"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="1" />
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Prefix</label>
            <input wire:model="blocks.{{ $index }}.options.prefix" type="text"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="e.g. $, Rp" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Suffix</label>
            <input wire:model="blocks.{{ $index }}.options.suffix" type="text"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="e.g. %, kg" />
        </div>
    </div>
</div>
