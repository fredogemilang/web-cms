{{-- Checkbox Block --}}
@php
    $selectedValues = is_string($block['value']) ? json_decode($block['value'], true) ?? [] : ($block['value'] ?? []);
@endphp

<div class="space-y-4">
    {{-- Selected Values --}}
    <div class="space-y-2">
        <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Selected Values</label>
        <div class="space-y-2">
            @foreach($block['options']['choices'] ?? [] as $choiceIndex => $choice)
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" value="{{ $choice['value'] }}"
                        wire:change="$set('blocks.{{ $index }}.value', $event.target.checked
                            ? JSON.stringify([...{{ json_encode($selectedValues) }}, '{{ $choice['value'] }}'])
                            : JSON.stringify({{ json_encode($selectedValues) }}.filter(v => v !== '{{ $choice['value'] }}')))"
                        @checked(in_array($choice['value'], $selectedValues))
                        class="h-4 w-4 rounded text-primary border-gray-300 dark:border-[#272B30] focus:ring-primary bg-[#F4F5F6] dark:bg-[#0B0B0B]" />
                    <span class="text-sm text-[#111827] dark:text-[#FCFCFC]">{{ $choice['label'] }}</span>
                </label>
            @endforeach

            @if(empty($block['options']['choices']))
                <p class="text-xs text-[#6F767E] italic">No choices defined yet.</p>
            @endif
        </div>
    </div>

    {{-- Choices Editor --}}
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider">Choices</label>
            <button wire:click="addChoice({{ $index }})" type="button"
                class="text-xs font-bold text-primary hover:text-blue-600 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">add</span>
                Add Choice
            </button>
        </div>

        <div class="space-y-2">
            @foreach($block['options']['choices'] ?? [] as $choiceIndex => $choice)
                <div class="flex items-center gap-2" wire:key="checkbox-choice-{{ $index }}-{{ $choiceIndex }}">
                    <input wire:model="blocks.{{ $index }}.options.choices.{{ $choiceIndex }}.label" type="text"
                        class="flex-1 h-8 rounded bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-xs text-[#111827] dark:text-[#FCFCFC] px-3"
                        placeholder="Label" />
                    <input wire:model="blocks.{{ $index }}.options.choices.{{ $choiceIndex }}.value" type="text"
                        class="flex-1 h-8 rounded bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-xs text-[#111827] dark:text-[#FCFCFC] font-mono px-3"
                        placeholder="value" />
                    <button wire:click="removeChoice({{ $index }}, {{ $choiceIndex }})" type="button"
                        class="h-8 w-8 rounded hover:bg-red-50 dark:hover:bg-red-500/10 text-[#6F767E] hover:text-red-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>
