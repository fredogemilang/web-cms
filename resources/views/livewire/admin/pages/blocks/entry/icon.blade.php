{{-- Icon Block Entry --}}
@php
    $iconSize = $block['options']['size'] ?? 'text-4xl';
@endphp
<div class="space-y-3">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Select Icon</label>
    <div class="flex items-center gap-4">
        <div class="h-16 w-16 rounded-xl bg-[#F4F5F6] dark:bg-[#0B0B0B] flex items-center justify-center">
            @if($block['value'])
                <span class="material-symbols-outlined {{ $iconSize }} text-primary">{{ $block['value'] }}</span>
            @else
                <span class="material-symbols-outlined text-3xl text-gray-300">help</span>
            @endif
        </div>
        <div class="flex-1">
            <input wire:model.live="blocks.{{ $index }}.value" type="text"
                class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-4"
                placeholder="e.g. home, settings, person" />
            <p class="text-[10px] text-[#6F767E] mt-1">
                Enter icon name from <a href="https://fonts.google.com/icons" target="_blank" class="text-primary hover:underline">Material Symbols</a>
            </p>
        </div>
    </div>
</div>
