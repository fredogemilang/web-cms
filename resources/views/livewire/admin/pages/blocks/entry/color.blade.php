{{-- Color Block Entry --}}
<div class="space-y-3">
    <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Select Color</label>
    <div class="flex items-center gap-3">
        <input wire:model.live="blocks.{{ $index }}.value" type="color"
            class="w-12 h-12 rounded-lg cursor-pointer border-0 p-0" />
        <input wire:model.live="blocks.{{ $index }}.value" type="text"
            class="flex-1 h-10 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-mono text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-4"
            placeholder="#000000" />
    </div>

    @if($block['options']['show_presets'] ?? true)
        <div class="flex flex-wrap gap-2">
            @foreach(['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#6B7280', '#111827', '#FFFFFF'] as $preset)
                <button wire:click="$set('blocks.{{ $index }}.value', '{{ $preset }}')" type="button"
                    class="w-8 h-8 rounded-lg border-2 transition-transform hover:scale-110 {{ $block['value'] === $preset ? 'border-primary ring-2 ring-primary/20' : 'border-gray-200 dark:border-[#272B30]' }}"
                    style="background-color: {{ $preset }}">
                </button>
            @endforeach
        </div>
    @endif
</div>
