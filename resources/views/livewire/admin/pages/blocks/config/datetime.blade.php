{{-- DateTime Block Config --}}
<div class="space-y-4">
    <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Min DateTime</label>
            <input wire:model="blocks.{{ $index }}.options.min_datetime" type="datetime-local"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3" />
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase">Max DateTime</label>
            <input wire:model="blocks.{{ $index }}.options.max_datetime" type="datetime-local"
                class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] px-3" />
        </div>
    </div>
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Display Format</label>
        <select wire:model="blocks.{{ $index }}.options.format"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3">
            <option value="Y-m-d H:i">2024-01-31 14:30</option>
            <option value="d/m/Y H:i">31/01/2024 14:30</option>
            <option value="F j, Y \a\t g:i A">January 31, 2024 at 2:30 PM</option>
        </select>
    </div>
</div>
