{{-- Textarea Block --}}
<div class="space-y-2">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Content</label>
    <textarea wire:model="blocks.{{ $index }}.value" rows="5"
        class="w-full rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary p-4 resize-y"
        placeholder="Enter multi-line text..."></textarea>
</div>
