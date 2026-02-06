<div>
    {{-- Backdrop with fade animation --}}
    <div 
        x-data="{ show: @js($showModal) }"
        x-init="
            $watch('show', value => {
                if (!value) {
                    setTimeout(() => @this.call('closeModal'), 200);
                }
            });
            Livewire.on('openMediaDetails', () => {
                setTimeout(() => show = true, 50);
            });
            Livewire.on('closeMediaDetails', () => {
                show = false;
            });
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @if(!$showModal) style="display: none;" @endif
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
        @click="show = false">
        
        {{-- Modal with scale animation --}}
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white dark:bg-[#1A1A1A] rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-xl" 
            wire:click.stop>
            @if($media)
            {{-- Header --}}
            <div class="sticky top-0 bg-white dark:bg-[#1A1A1A] border-b border-gray-200 dark:border-[#272B30] p-6 flex items-center justify-between">
                <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Media Details</h3>
                <button @click="show = false" class="p-2 hover:bg-gray-100 dark:hover:bg-[#272B30] rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[#6F767E]">close</span>
                </button>
            </div>

            {{-- Content --}}
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Preview --}}
                    <div class="space-y-4">
                        <div class="aspect-video rounded-xl overflow-hidden bg-gray-100 dark:bg-[#272B30] flex items-center justify-center">
                            @if($media->isImage())
                                <img 
                                    src="{{ $media->webp_url ?? $media->url }}" 
                                    alt="{{ $media->alt_text ?? $media->original_filename }}"
                                    class="w-full h-full object-contain">
                            @else
                                <span class="material-symbols-outlined text-8xl text-[#6F767E]">description</span>
                            @endif
                        </div>

                        {{-- File Info --}}
                        <div class="rounded-xl bg-gray-50 dark:bg-[#272B30] p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-[#6F767E]">File name:</span>
                                <span class="font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $media->original_filename }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-[#6F767E]">File type:</span>
                                <span class="font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $media->mime_type }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-[#6F767E]">File size:</span>
                                <span class="font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $media->human_readable_size }}</span>
                            </div>
                            @if($media->width && $media->height)
                            <div class="flex justify-between text-sm">
                                <span class="text-[#6F767E]">Dimensions:</span>
                                <span class="font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $media->width }} × {{ $media->height }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-sm">
                                <span class="text-[#6F767E]">Uploaded:</span>
                                <span class="font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $media->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-[#6F767E]">Uploaded by:</span>
                                <span class="font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $media->uploader->name ?? 'Unknown' }}</span>
                            </div>
                        </div>

                        {{-- URL Copy --}}
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC]">File URL</label>
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    value="{{ $media->url }}" 
                                    readonly
                                    class="flex-1 px-4 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm text-[#6F767E] bg-gray-50">
                                <button 
                                    onclick="navigator.clipboard.writeText('{{ $media->url }}')"
                                    class="px-4 py-2 bg-gray-100 dark:bg-[#272B30] rounded-xl hover:bg-gray-200 dark:hover:bg-[#272B30]/80 transition-colors">
                                    <span class="material-symbols-outlined text-[#6F767E]">content_copy</span>
                                </button>
                            </div>
                        </div>

                        @if($media->hasWebp())
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC]">WebP URL</label>
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    value="{{ $media->webp_url }}" 
                                    readonly
                                    class="flex-1 px-4 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm text-[#6F767E] bg-gray-50">
                                <button 
                                    onclick="navigator.clipboard.writeText('{{ $media->webp_url }}')"
                                    class="px-4 py-2 bg-gray-100 dark:bg-[#272B30] rounded-xl hover:bg-gray-200 dark:hover:bg-[#272B30]/80 transition-colors">
                                    <span class="material-symbols-outlined text-[#6F767E]">content_copy</span>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Edit Form --}}
                    <div class="space-y-4">
                        <form wire:submit.prevent="save" class="space-y-4">
                            {{-- Title --}}
                            <div>
                                <label class="block text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] mb-2">Title</label>
                                <input 
                                    type="text" 
                                    wire:model="title"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter media title">
                                @error('title') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Alt Text (for images) --}}
                            @if($media->isImage())
                            <div>
                                <label class="block text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] mb-2">Alternative Text</label>
                                <input 
                                    type="text" 
                                    wire:model="alt_text"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Describe this image for accessibility">
                                @error('alt_text') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>
                            @endif

                            {{-- Description --}}
                            <div>
                                <label class="block text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] mb-2">Description</label>
                                <textarea 
                                    wire:model="description"
                                    rows="4"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                    placeholder="Add a description..."></textarea>
                                @error('description') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex gap-3 pt-4">
                                @can('media.edit')
                                <button 
                                    type="submit"
                                    class="flex-1 px-4 py-3 bg-[#2563EB] text-white rounded-xl font-semibold hover:bg-[#1D4ED8] transition-all">
                                    Save Changes
                                </button>
                                @endcan
                                
                                @can('media.delete')
                                <button 
                                    type="button"
                                    wire:click="delete"
                                    wire:confirm="Are you sure you want to delete this media file?"
                                    class="px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl font-semibold hover:bg-red-100 dark:hover:bg-red-900/30 transition-all">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
