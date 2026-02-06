{{-- Time Block Config --}}
<div class="space-y-4">
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Time Format</label>
        <select wire:model="blocks.{{ $index }}.options.format"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3">
            <option value="H:i">24-hour (14:30)</option>
            <option value="h:i A">12-hour (02:30 PM)</option>
            <option value="H:i:s">24-hour with seconds</option>
        </select>
    </div>
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Step (minutes)</label>
        <select wire:model="blocks.{{ $index }}.options.step"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3">
            <option value="1">1 minute</option>
            <option value="5">5 minutes</option>
            <option value="10">10 minutes</option>
            <option value="15">15 minutes</option>
            <option value="30">30 minutes</option>
            <option value="60">1 hour</option>
        </select>
    </div>
</div>
