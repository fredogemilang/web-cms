{{-- Media Block Config --}}
<div class="space-y-4">
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Allowed Types</label>
        <select wire:model="blocks.{{ $index }}.options.allowed_types" multiple
            class="w-full h-24 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3 py-2">
            <option value="image/*">Images (JPG, PNG, GIF, etc.)</option>
            <option value="video/*">Videos (MP4, WebM, etc.)</option>
            <option value="application/pdf">PDF Documents</option>
            <option value="*/*">All Files</option>
        </select>
        <p class="text-[10px] text-[#6F767E]">Hold Ctrl/Cmd to select multiple</p>
    </div>
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Max File Size (KB)</label>
        <input wire:model="blocks.{{ $index }}.options.max_size" type="number" min="1"
            class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
            placeholder="2048" />
    </div>
</div>
