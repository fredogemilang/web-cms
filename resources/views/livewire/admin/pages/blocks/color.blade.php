{{-- Color Picker Block --}}
@php
    $presetColors = [
        '#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16', '#22C55E', '#10B981', '#14B8A6',
        '#06B6D4', '#0EA5E9', '#3B82F6', '#6366F1', '#8B5CF6', '#A855F7', '#D946EF', '#EC4899',
        '#F43F5E', '#1F2937', '#374151', '#6B7280', '#9CA3AF', '#D1D5DB', '#F3F4F6', '#FFFFFF',
    ];
@endphp

<div class="space-y-4">
    <div class="flex items-center gap-4">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Color</label>
        @if($block['value'])
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg border border-gray-200 dark:border-[#272B30]"
                    style="background-color: {{ $block['value'] }}"></div>
                <span class="text-xs font-mono text-[#111827] dark:text-[#FCFCFC]">{{ $block['value'] }}</span>
            </div>
        @endif
    </div>

    <div class="flex items-center gap-4">
        <input wire:model.live="blocks.{{ $index }}.value" type="color"
            class="w-12 h-12 rounded-lg cursor-pointer border-0 p-0"
            style="background: transparent;" />
        <input wire:model.live="blocks.{{ $index }}.value" type="text"
            class="flex-1 h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-mono text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-4"
            placeholder="#000000" />
    </div>

    <div class="grid grid-cols-12 gap-1">
        @foreach($presetColors as $color)
            <button type="button" wire:click="$set('blocks.{{ $index }}.value', '{{ $color }}')"
                class="w-full aspect-square rounded {{ $block['value'] === $color ? 'ring-2 ring-primary ring-offset-2 dark:ring-offset-[#1A1A1A]' : '' }} hover:scale-110 transition-transform"
                style="background-color: {{ $color }}"
                title="{{ $color }}">
            </button>
        @endforeach
    </div>
</div>
