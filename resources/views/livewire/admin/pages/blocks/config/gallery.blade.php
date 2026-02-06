{{-- Gallery Block Config --}}
<div class="space-y-4">
    <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Min Images</label>
            <input wire:model="blocks.{{ $index }}.options.min_items" type="number" min="0"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="0" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Max Images</label>
            <input wire:model="blocks.{{ $index }}.options.max_items" type="number" min="1"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="10" />
        </div>
    </div>
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Grid Columns</label>
        <select wire:model="blocks.{{ $index }}.options.columns"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3">
            <option value="2">2 Columns</option>
            <option value="3">3 Columns</option>
            <option value="4">4 Columns</option>
            <option value="5">5 Columns</option>
        </select>
    </div>
</div>
