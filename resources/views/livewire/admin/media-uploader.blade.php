<div x-data="{ show: false }">
    @if($isModal)
    {{-- Modal Mode --}}
    <div @open-upload-modal.window="show = true" @close-upload-modal.window="show = false">
        {{-- Backdrop with fade animation --}}
        <div 
            x-show="show" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            x-cloak>
            
            {{-- Modal with scale animation --}}
            <div 
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white dark:bg-[#1A1A1A] rounded-3xl max-w-2xl w-full shadow-xl" 
                @click.away="show = false">
                {{-- Header --}}
                <div class="border-b border-gray-200 dark:border-[#272B30] p-6 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Upload Media</h3>
                    <button @click="show = false" class="p-2 hover:bg-gray-100 dark:hover:bg-[#272B30] rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-[#6F767E]">close</span>
                    </button>
                </div>

                {{-- Content --}}
                <div class="p-6">
                    @include('livewire.admin.media-uploader-form')
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Inline Mode --}}
    @include('livewire.admin.media-uploader-form')
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
