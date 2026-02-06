@php
    $blockConfig = \App\Models\PageBlock::$blockTypes[$block['type']] ?? [];
    $colorClasses = \App\Models\PageBlock::$colorClasses[$blockConfig['color'] ?? 'gray'] ?? 'bg-gray-500/10 text-gray-500';
    $isConfigured = $block['is_configured'] ?? false;
@endphp

<div class="group relative bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-2xl p-6 hover:border-primary transition-all {{ !$block['is_active'] ? 'opacity-50' : '' }}"
    wire:key="block-{{ $index }}">

    {{-- Block Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
            <div class="h-8 w-8 rounded-lg {{ $colorClasses }} flex items-center justify-center">
                <span class="material-symbols-outlined text-lg">{{ $blockConfig['icon'] ?? 'help' }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#6F767E] uppercase">{{ $blockConfig['label'] ?? 'Block' }}</span>
                @if(!empty($block['name']))
                    <span class="text-xs text-primary font-mono">{{ $block['name'] }}</span>
                @endif
            </div>
            {{-- Mode Badge --}}
            @if(!$isConfigured)
                <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 text-[10px] font-bold uppercase">
                    Setup
                </span>
            @endif
        </div>

        {{-- Block Actions (visible on hover) --}}
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            {{-- Settings Button (only in entry mode) --}}
            @if($isConfigured)
                <button wire:click="editBlockSettings({{ $index }})"
                    class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white transition-all flex items-center justify-center"
                    title="Edit Settings">
                    <span class="material-symbols-outlined text-lg">settings</span>
                </button>
            @endif
            <button wire:click="moveBlockUp({{ $index }})" @disabled($index === 0)
                class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white transition-all flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed"
                title="Move Up">
                <span class="material-symbols-outlined text-lg">arrow_upward</span>
            </button>
            <button wire:click="moveBlockDown({{ $index }})" @disabled($index === count($blocks) - 1)
                class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white transition-all flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed"
                title="Move Down">
                <span class="material-symbols-outlined text-lg">arrow_downward</span>
            </button>
            <button wire:click="duplicateBlock({{ $index }})"
                class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white transition-all flex items-center justify-center"
                title="Duplicate">
                <span class="material-symbols-outlined text-lg">content_copy</span>
            </button>
            <button wire:click="toggleBlockActive({{ $index }})"
                class="h-8 w-8 rounded-lg hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white transition-all flex items-center justify-center"
                title="{{ $block['is_active'] ? 'Disable' : 'Enable' }}">
                <span class="material-symbols-outlined text-lg">{{ $block['is_active'] ? 'visibility' : 'visibility_off' }}</span>
            </button>
            <button wire:click="removeBlock({{ $index }})" wire:confirm="Are you sure you want to delete this block?"
                class="h-8 w-8 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-[#6F767E] hover:text-red-500 transition-all flex items-center justify-center"
                title="Delete">
                <span class="material-symbols-outlined text-lg">delete</span>
            </button>
        </div>
    </div>

    @if(!$isConfigured)
        {{-- ========================================= --}}
        {{-- CONFIGURATION MODE --}}
        {{-- ========================================= --}}
        <div class="space-y-4">
            {{-- Block Name Input --}}
            <div>
                <label class="text-[10px] font-bold text-[#6F767E] uppercase tracking-wider block mb-1">Block Name (ID) <span class="text-red-500">*</span></label>
                <input wire:model.live="blocks.{{ $index }}.name" type="text"
                    x-on:input="$event.target.value = $event.target.value.replace(/\s+/g, '_').replace(/[^a-z0-9_]/gi, '').toLowerCase()"
                    class="w-full h-9 rounded-lg bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-mono text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-primary px-3"
                    placeholder="e.g. hero_title, about_content" />
                @error("blocks.{$index}.name")
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Block-specific Configuration --}}
            <div class="block-config">
                @switch($block['type'])
                    @case('text')
                        @include('livewire.admin.pages.blocks.config.text', ['index' => $index, 'block' => $block])
                        @break
                    @case('textarea')
                        @include('livewire.admin.pages.blocks.config.textarea', ['index' => $index, 'block' => $block])
                        @break
                    @case('wysiwyg')
                        @include('livewire.admin.pages.blocks.config.wysiwyg', ['index' => $index, 'block' => $block])
                        @break
                    @case('number')
                        @include('livewire.admin.pages.blocks.config.number', ['index' => $index, 'block' => $block])
                        @break
                    @case('select')
                        @include('livewire.admin.pages.blocks.config.select', ['index' => $index, 'block' => $block])
                        @break
                    @case('radio')
                        @include('livewire.admin.pages.blocks.config.radio', ['index' => $index, 'block' => $block])
                        @break
                    @case('checkbox')
                        @include('livewire.admin.pages.blocks.config.checkbox', ['index' => $index, 'block' => $block])
                        @break
                    @case('switcher')
                        @include('livewire.admin.pages.blocks.config.switcher', ['index' => $index, 'block' => $block])
                        @break
                    @case('media')
                        @include('livewire.admin.pages.blocks.config.media', ['index' => $index, 'block' => $block])
                        @break
                    @case('gallery')
                        @include('livewire.admin.pages.blocks.config.gallery', ['index' => $index, 'block' => $block])
                        @break
                    @case('date')
                        @include('livewire.admin.pages.blocks.config.date', ['index' => $index, 'block' => $block])
                        @break
                    @case('time')
                        @include('livewire.admin.pages.blocks.config.time', ['index' => $index, 'block' => $block])
                        @break
                    @case('datetime')
                        @include('livewire.admin.pages.blocks.config.datetime', ['index' => $index, 'block' => $block])
                        @break
                    @case('icon')
                        @include('livewire.admin.pages.blocks.config.icon', ['index' => $index, 'block' => $block])
                        @break
                    @case('color')
                        @include('livewire.admin.pages.blocks.config.color', ['index' => $index, 'block' => $block])
                        @break
                    @case('posts')
                        @include('livewire.admin.pages.blocks.config.posts', ['index' => $index, 'block' => $block])
                        @break
                    @case('repeater')
                        @include('livewire.admin.pages.blocks.config.repeater', ['index' => $index, 'block' => $block])
                        @break
                @endswitch
            </div>

            {{-- Save Config Button --}}
            <div class="pt-2 border-t border-gray-200 dark:border-[#272B30]">
                <button wire:click="saveBlockConfig({{ $index }})"
                    class="w-full h-10 rounded-xl bg-primary hover:bg-primary/90 text-white font-medium text-sm transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">check</span>
                    Save Configuration
                </button>
            </div>
        </div>
    @else
        {{-- ========================================= --}}
        {{-- ENTRY MODE --}}
        {{-- ========================================= --}}
        <div class="block-content">
            @switch($block['type'])
                @case('text')
                    @include('livewire.admin.pages.blocks.entry.text', ['index' => $index, 'block' => $block])
                    @break
                @case('textarea')
                    @include('livewire.admin.pages.blocks.entry.textarea', ['index' => $index, 'block' => $block])
                    @break
                @case('wysiwyg')
                    @include('livewire.admin.pages.blocks.entry.wysiwyg', ['index' => $index, 'block' => $block])
                    @break
                @case('number')
                    @include('livewire.admin.pages.blocks.entry.number', ['index' => $index, 'block' => $block])
                    @break
                @case('select')
                    @include('livewire.admin.pages.blocks.entry.select', ['index' => $index, 'block' => $block])
                    @break
                @case('radio')
                    @include('livewire.admin.pages.blocks.entry.radio', ['index' => $index, 'block' => $block])
                    @break
                @case('checkbox')
                    @include('livewire.admin.pages.blocks.entry.checkbox', ['index' => $index, 'block' => $block])
                    @break
                @case('switcher')
                    @include('livewire.admin.pages.blocks.entry.switcher', ['index' => $index, 'block' => $block])
                    @break
                @case('media')
                    @include('livewire.admin.pages.blocks.entry.media', ['index' => $index, 'block' => $block])
                    @break
                @case('gallery')
                    @include('livewire.admin.pages.blocks.entry.gallery', ['index' => $index, 'block' => $block])
                    @break
                @case('date')
                    @include('livewire.admin.pages.blocks.entry.date', ['index' => $index, 'block' => $block])
                    @break
                @case('time')
                    @include('livewire.admin.pages.blocks.entry.time', ['index' => $index, 'block' => $block])
                    @break
                @case('datetime')
                    @include('livewire.admin.pages.blocks.entry.datetime', ['index' => $index, 'block' => $block])
                    @break
                @case('icon')
                    @include('livewire.admin.pages.blocks.entry.icon', ['index' => $index, 'block' => $block])
                    @break
                @case('color')
                    @include('livewire.admin.pages.blocks.entry.color', ['index' => $index, 'block' => $block])
                    @break
                @case('posts')
                    @include('livewire.admin.pages.blocks.entry.posts', ['index' => $index, 'block' => $block])
                    @break
                @case('repeater')
                    @include('livewire.admin.pages.blocks.entry.repeater', ['index' => $index, 'block' => $block])
                    @break
                @default
                    <div class="text-[#6F767E] italic">Unknown block type: {{ $block['type'] }}</div>
            @endswitch
        </div>
    @endif
</div>
