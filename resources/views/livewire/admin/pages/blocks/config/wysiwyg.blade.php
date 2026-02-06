{{-- WYSIWYG Block Config (Tiptap Editor) --}}
<div class="space-y-4">
    <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 text-sm">
        <p class="flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">info</span>
            Tiptap rich text editor with full formatting options will be available in entry mode.
        </p>
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Placeholder Text</label>
        <input wire:model="blocks.{{ $index }}.options.placeholder" type="text"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
            placeholder="Write your content here..." />
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Min Height (px)</label>
        <input wire:model="blocks.{{ $index }}.options.min_height" type="number" min="100" step="50"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
            placeholder="200" />
    </div>
</div>
