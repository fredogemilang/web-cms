{{-- Time Block --}}
<div class="space-y-2">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Time</label>
    <input wire:model="blocks.{{ $index }}.value" type="time"
        class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-4" />
</div>
