{{-- Gallery Block Entry --}}
@php
    $images = json_decode($block['value'] ?? '[]', true) ?? [];
    $columns = $block['options']['columns'] ?? 3;
@endphp
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Gallery ({{ count($images) }} images)</label>
        @if(count($images) < ($block['options']['max_items'] ?? 10))
            <button wire:click="openMediaPicker('gallery_{{ $index }}')" type="button"
                class="text-xs font-bold text-primary hover:text-blue-600 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">add</span>
                Add Image
            </button>
        @endif
    </div>

    @if(count($images) > 0)
        <div class="grid grid-cols-{{ $columns }} gap-3">
            @foreach($images as $imageIndex => $image)
                <div class="relative group aspect-square" wire:key="gallery-image-{{ $index }}-{{ $imageIndex }}">
                    <img src="{{ asset('storage/' . $image) }}" alt="Gallery image"
                        class="w-full h-full object-cover rounded-lg border border-gray-200 dark:border-[#272B30]" />
                    <button wire:click="removeGalleryImage({{ $index }}, {{ $imageIndex }})" type="button"
                        class="absolute top-1 right-1 h-6 w-6 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div wire:click="openMediaPicker('gallery_{{ $index }}')"
            class="h-32 rounded-xl bg-[#F4F5F6] dark:bg-[#0B0B0B] border-2 border-dashed border-gray-300 dark:border-[#272B30] flex flex-col items-center justify-center gap-2 hover:bg-gray-100 dark:hover:bg-[#1A1A1A] transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-3xl text-gray-300 dark:text-[#272B30]">collections</span>
            <span class="text-sm text-[#6F767E]">Add images to gallery</span>
        </div>
    @endif
</div>
