{{-- Posts Selector Block --}}
@php
    $selectedPosts = is_string($block['value']) ? json_decode($block['value'], true) ?? [] : ($block['value'] ?? []);
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Selected Posts</label>
        <span class="text-xs text-[#6F767E]">{{ count($selectedPosts) }} / {{ $block['options']['max_items'] ?? 5 }} selected</span>
    </div>

    {{-- Options --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-[#6F767E] uppercase">Post Type</label>
            <select wire:model="blocks.{{ $index }}.options.post_type"
                class="w-full h-9 rounded bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-xs text-[#111827] dark:text-[#FCFCFC] px-3">
                <option value="post">Posts</option>
                {{-- Add dynamic post types here --}}
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-[#6F767E] uppercase">Max Items</label>
            <input wire:model="blocks.{{ $index }}.options.max_items" type="number" min="1" max="20"
                class="w-full h-9 rounded bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-xs text-[#111827] dark:text-[#FCFCFC] px-3"
                placeholder="5" />
        </div>
    </div>

    {{-- Manual Post IDs Input --}}
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Post IDs (comma-separated)</label>
        <input wire:model="blocks.{{ $index }}.value" type="text"
            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-mono text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-4"
            placeholder="1, 2, 3, 4, 5" />
        <p class="text-[10px] text-[#6F767E]">Enter post IDs separated by commas. These will be fetched on the frontend.</p>
    </div>
</div>
