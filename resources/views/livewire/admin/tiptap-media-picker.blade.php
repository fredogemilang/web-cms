<div>
    {{-- Modal for TipTap Image Selection --}}
    @if($showModal)
    <div 
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4"
        @keydown.escape.window="$wire.closeModal()">
        
        {{-- Modal Content --}}
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white dark:bg-[#1A1A1A] rounded-3xl max-w-5xl w-full max-h-[85vh] flex flex-col shadow-xl"
            @click.away="$wire.closeModal()">
            
            {{-- Header --}}
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-[#272B30]">
                <div class="flex items-center gap-4">
                    <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Insert Image</h3>
                    
                    {{-- Tabs --}}
                    <div class="flex bg-gray-100 dark:bg-[#272B30] rounded-lg p-1">
                        <button 
                            type="button"
                            wire:click="$set('activeTab', 'library')"
                            class="px-4 py-1.5 rounded-md text-xs font-bold transition-all {{ $activeTab === 'library' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-white' }}">
                            Media Library
                        </button>
                        @can('media.upload')
                        <button 
                            type="button"
                            wire:click="$set('activeTab', 'upload')"
                            class="px-4 py-1.5 rounded-md text-xs font-bold transition-all {{ $activeTab === 'upload' ? 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-white' }}">
                            Upload New
                        </button>
                        @endcan
                    </div>
                </div>
                
                <button 
                    type="button"
                    wire:click="closeModal" 
                    class="p-2 hover:bg-gray-100 dark:hover:bg-[#272B30] rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-[#6F767E]">close</span>
                </button>
            </div>

            {{-- Flash Messages --}}
            @if(session()->has('tiptap-picker-success'))
            <div class="mx-6 mt-4 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('tiptap-picker-success') }}</p>
            </div>
            @endif
            @if(session()->has('tiptap-picker-error'))
            <div class="mx-6 mt-4 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('tiptap-picker-error') }}</p>
            </div>
            @endif

            {{-- Content --}}
            <div class="flex-1 overflow-hidden p-6">
                @if($activeTab === 'library')
                    {{-- Library Tab --}}
                    <div class="h-full flex flex-col">
                        {{-- Search --}}
                        <div class="flex gap-4 mb-4">
                            <div class="flex-1 relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#6F767E]">search</span>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Search images..." 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#0B0B0B] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        {{-- Media Grid --}}
                        <div class="flex-1 overflow-y-auto">
                            @if($mediaItems->count() > 0)
                            <div class="grid grid-cols-4 md:grid-cols-6 gap-3">
                                @foreach($mediaItems as $item)
                                <div 
                                    wire:click="selectMedia({{ $item->id }})"
                                    class="aspect-square rounded-xl overflow-hidden border-2 cursor-pointer transition-all relative {{ $selectedMediaId === $item->id ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-800' : 'border-gray-200 dark:border-[#272B30] hover:border-blue-300 dark:hover:border-blue-700' }}">
                                    <img 
                                        src="{{ $item->webp_url ?? $item->url }}" 
                                        alt="{{ $item->alt_text ?? $item->original_filename }}"
                                        class="w-full h-full object-cover">
                                    
                                    {{-- Selection Indicator --}}
                                    @if($selectedMediaId === $item->id)
                                    <div class="absolute top-2 right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white text-sm">check</span>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            
                            {{-- Pagination --}}
                            <div class="mt-4">
                                {{ $mediaItems->links() }}
                            </div>
                            @else
                            <div class="flex flex-col items-center justify-center h-full text-center">
                                <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-[#272B30] mb-3">perm_media</span>
                                <p class="text-sm text-[#6F767E]">No images found</p>
                                @can('media.upload')
                                <button 
                                    type="button"
                                    wire:click="$set('activeTab', 'upload')"
                                    class="mt-3 text-sm font-semibold text-[#2563EB] hover:underline">
                                    Upload new image
                                </button>
                                @endcan
                            </div>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Upload Tab --}}
                    <div class="h-full flex flex-col">
                        {{-- Drag & Drop Zone --}}
                        <div 
                            x-data="{ 
                                isDragging: false,
                                handleDrop(e) {
                                    this.isDragging = false;
                                    const files = e.dataTransfer.files;
                                    if (files.length > 0) {
                                        const input = $refs.tiptapFileInput;
                                        const dataTransfer = new DataTransfer();
                                        dataTransfer.items.add(files[0]);
                                        input.files = dataTransfer.files;
                                        input.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            }"
                            @drop.prevent="handleDrop($event)"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            :class="isDragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-[#272B30]'"
                            class="flex-1 border-2 border-dashed rounded-xl flex flex-col items-center justify-center transition-all">
                            
                            @if($uploadFile)
                                {{-- Preview uploaded file --}}
                                <div class="text-center">
                                    <div class="w-32 h-32 mx-auto mb-4 rounded-xl overflow-hidden bg-gray-100 dark:bg-[#272B30]">
                                        <img src="{{ $uploadFile->temporaryUrl() }}" class="w-full h-full object-cover">
                                    </div>
                                    <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $uploadFile->getClientOriginalName() }}</p>
                                    <p class="text-xs text-[#6F767E] mt-1">{{ number_format($uploadFile->getSize() / 1024, 2) }} KB</p>
                                    <button 
                                        type="button"
                                        wire:click="clearUpload"
                                        class="mt-3 text-sm font-semibold text-red-600 hover:underline">
                                        Remove
                                    </button>
                                </div>
                            @else
                                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-4xl text-[#6F767E]">cloud_upload</span>
                                </div>
                                <h4 class="text-lg font-semibold text-[#111827] dark:text-[#FCFCFC] mb-2">
                                    Drag and drop or click to browse
                                </h4>
                                <p class="text-sm text-[#6F767E] mb-4">
                                    Supports: JPG, PNG, GIF, WebP, SVG
                                </p>
                                <label class="cursor-pointer px-6 py-3 bg-[#2563EB] text-white rounded-xl font-semibold hover:bg-[#1D4ED8] transition-all">
                                    <span>Select Image</span>
                                    <input 
                                        x-ref="tiptapFileInput"
                                        type="file" 
                                        wire:model="uploadFile" 
                                        accept="image/*"
                                        class="hidden">
                                </label>
                            @endif
                        </div>

                        {{-- Upload Progress --}}
                        <div wire:loading wire:target="uploadFile" class="mt-4 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center gap-3">
                                <div class="animate-spin">
                                    <span class="material-symbols-outlined text-blue-600">refresh</span>
                                </div>
                                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Processing file...</span>
                            </div>
                        </div>

                        @error('uploadFile')
                        <div class="mt-4 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $message }}</p>
                        </div>
                        @enderror
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-[#272B30]">
                <div>
                    @if($selectedMedia && $activeTab === 'library')
                    <p class="text-sm text-[#6F767E]">
                        Selected: <span class="font-semibold text-[#111827] dark:text-[#FCFCFC]">{{ $selectedMedia['original_filename'] }}</span>
                    </p>
                    @endif
                </div>
                <div class="flex gap-3">
                    <button 
                        type="button"
                        wire:click="closeModal"
                        class="px-6 py-2.5 rounded-xl border border-gray-300 dark:border-[#272B30] text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">
                        Cancel
                    </button>
                    
                    @if($activeTab === 'library')
                        <button 
                            type="button"
                            wire:click="confirmSelection"
                            @if(!$selectedMediaId) disabled @endif
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] text-white text-sm font-semibold hover:bg-[#1D4ED8] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            Insert Image
                        </button>
                    @else
                        <button 
                            type="button"
                            wire:click="uploadAndSelect"
                            wire:loading.attr="disabled"
                            @if(!$uploadFile) disabled @endif
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] text-white text-sm font-semibold hover:bg-[#1D4ED8] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="uploadAndSelect">Upload & Insert</span>
                            <span wire:loading wire:target="uploadAndSelect">Uploading...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
