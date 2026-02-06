{{-- Media Block --}}
<div class="space-y-2">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Image/File</label>

    @if($block['value'])
        <div class="relative group">
            @if(Str::startsWith($block['value'], ['image/', 'data:image']) || preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $block['value']))
                <img src="{{ asset('storage/' . $block['value']) }}" alt="Media"
                    class="w-full h-48 object-cover rounded-xl border border-gray-200 dark:border-[#272B30]" />
            @else
                <div class="w-full h-48 rounded-xl border border-gray-200 dark:border-[#272B30] bg-[#F4F5F6] dark:bg-[#0B0B0B] flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-[#6F767E]">description</span>
                    <span class="text-xs text-[#6F767E] mt-2">{{ basename($block['value']) }}</span>
                </div>
            @endif
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center gap-2">
                <button wire:click="openMediaPicker('block_{{ $index }}')" type="button"
                    class="h-10 w-10 rounded-full bg-white text-[#111827] flex items-center justify-center hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button wire:click="$set('blocks.{{ $index }}.value', '')" type="button"
                    class="h-10 w-10 rounded-full bg-red-500 text-white flex items-center justify-center hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </div>
        </div>
    @else
        <div wire:click="openMediaPicker('block_{{ $index }}')"
            class="h-48 rounded-xl bg-[#F4F5F6] dark:bg-[#0B0B0B] border-2 border-dashed border-gray-300 dark:border-[#272B30] flex flex-col items-center justify-center gap-2 hover:bg-gray-100 dark:hover:bg-[#1A1A1A] transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-[#272B30]">add_a_photo</span>
            <span class="text-sm text-[#6F767E]">Upload or choose from library</span>
        </div>
    @endif
</div>
