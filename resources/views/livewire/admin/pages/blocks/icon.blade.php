{{-- Icon Picker Block --}}
@php
    $popularIcons = [
        'home', 'search', 'settings', 'person', 'favorite', 'star', 'check_circle', 'info',
        'warning', 'error', 'help', 'schedule', 'email', 'phone', 'location_on', 'shopping_cart',
        'menu', 'close', 'arrow_forward', 'arrow_back', 'expand_more', 'expand_less', 'add', 'remove',
        'edit', 'delete', 'visibility', 'lock', 'verified', 'trending_up', 'insights', 'lightbulb',
        'rocket_launch', 'thumb_up', 'thumb_down', 'bookmark', 'share', 'download', 'upload', 'cloud',
    ];
@endphp

<div class="space-y-4">
    <div class="flex items-center gap-4">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Selected Icon</label>
        @if($block['value'])
            <div class="flex items-center gap-2 px-3 py-1 rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined">{{ $block['value'] }}</span>
                <span class="text-xs font-mono">{{ $block['value'] }}</span>
            </div>
        @endif
    </div>

    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Search or Select</label>
        <input type="text" placeholder="Type icon name (e.g. home, star, check)"
            class="w-full h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-4"
            x-data
            x-on:input.debounce.300ms="$wire.set('blocks.{{ $index }}.value', $event.target.value)" />
    </div>

    <div class="grid grid-cols-8 gap-2">
        @foreach($popularIcons as $icon)
            <button type="button" wire:click="$set('blocks.{{ $index }}.value', '{{ $icon }}')"
                class="h-10 w-10 rounded-lg {{ $block['value'] === $icon ? 'bg-primary text-white' : 'bg-[#F4F5F6] dark:bg-[#0B0B0B] text-[#6F767E] hover:text-[#111827] dark:hover:text-white' }} flex items-center justify-center transition-all"
                title="{{ $icon }}">
                <span class="material-symbols-outlined text-xl">{{ $icon }}</span>
            </button>
        @endforeach
    </div>

    <p class="text-[10px] text-[#6F767E]">
        Browse more icons at <a href="https://fonts.google.com/icons" target="_blank" class="text-primary hover:underline">Material Symbols</a>
    </p>
</div>
