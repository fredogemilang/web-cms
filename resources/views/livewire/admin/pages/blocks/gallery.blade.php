{{-- Gallery Block --}}
@php
    $images = is_string($block['value']) ? json_decode($block['value'], true) ?? [] : ($block['value'] ?? []);
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Gallery Images</label>
        <span class="text-xs text-[#6F767E]">{{ count($images) }} / {{ $block['options']['max_items'] ?? 10 }} images</span>
    </div>

    <div class="grid grid-cols-3 gap-2">
        @foreach($images as $imageIndex => $image)
            <div class="relative group aspect-square" wire:key="gallery-image-{{ $index }}-{{ $imageIndex }}">
                <img src="{{ asset('storage/' . $image) }}" alt="Gallery Image"
                    class="w-full h-full object-cover rounded-lg border border-gray-200 dark:border-[#272B30]" />
                <button type="button"
                    wire:click="$set('blocks.{{ $index }}.value', JSON.stringify({{ json_encode($images) }}.filter((_, i) => i !== {{ $imageIndex }})))"
                    class="absolute top-1 right-1 h-6 w-6 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>
        @endforeach

        @if(count($images) < ($block['options']['max_items'] ?? 10))
            <div wire:click="openMediaPicker('block_{{ $index }}')"
                class="aspect-square rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-2 border-dashed border-gray-300 dark:border-[#272B30] flex flex-col items-center justify-center gap-1 hover:bg-gray-100 dark:hover:bg-[#1A1A1A] transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-2xl text-gray-300 dark:text-[#272B30]">add_photo_alternate</span>
                <span class="text-[10px] text-[#6F767E]">Add</span>
            </div>
        @endif
    </div>
</div>
