<div class="space-y-6">
    {{-- Drag & Drop Zone --}}
    <div 
        x-data="{ 
            isDragging: false,
            handleDrop(e) {
                this.isDragging = false;
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    // Get the file input element
                    const input = $refs.fileInput;
                    // Create a new DataTransfer object and add the dropped files
                    const dataTransfer = new DataTransfer();
                    Array.from(files).forEach(file => {
                        dataTransfer.items.add(file);
                    });
                    // Set the files to the input element
                    input.files = dataTransfer.files;
                    // Trigger the change event to notify Livewire
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }"
        @drop.prevent="handleDrop($event)"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @dragend.prevent="isDragging = false"
        :class="isDragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-[#272B30]'"
        class="border-2 border-dashed rounded-xl p-8 text-center transition-all">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-4xl text-[#6F767E]">cloud_upload</span>
            </div>
            <h4 class="text-lg font-semibold text-[#111827] dark:text-[#FCFCFC] mb-2">
                Drag and drop files here or click to browse
            </h4>
            <p class="text-sm text-[#6F767E] mb-4">
                Maximum file size: {{ config('media.max_file_size') / 1024 }}MB
            </p>
            <label class="cursor-pointer px-6 py-3 bg-[#2563EB] text-white rounded-xl font-semibold hover:bg-[#1D4ED8] transition-all">
                <span>Select Files</span>
                <input 
                    x-ref="fileInput"
                    type="file" 
                    wire:model="files" 
                    multiple 
                    accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, config('media.allowed_extensions'))) }}"
                    class="hidden">
            </label>
        </div>
    </div>

    {{-- File Upload Progress --}}
    <div wire:loading wire:target="files" class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
        <div class="flex items-center gap-3">
            <div class="animate-spin">
                <span class="material-symbols-outlined text-blue-600">refresh</span>
            </div>
            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Processing files...</span>
        </div>
    </div>

    @if($uploading)
    <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
        <div class="flex items-center gap-3">
            <div class="animate-spin">
                <span class="material-symbols-outlined text-blue-600">refresh</span>
            </div>
            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Uploading files...</span>
        </div>
    </div>
    @endif

    {{-- Selected Files Preview --}}
    @if(is_array($files) && count($files) > 0 && !$uploading)
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC]">
                Selected Files ({{ count($files) }})
            </h4>
            <button 
                type="button"
                wire:click="clearAll"
                class="text-sm text-red-600 hover:text-red-700 font-medium">
                Clear All
            </button>
        </div>
        <div class="space-y-2 max-h-48 overflow-y-auto">
            @foreach($files as $index => $file)
            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-[#272B30]">
                <span class="material-symbols-outlined text-[#6F767E]">description</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC] truncate">
                        {{ $file->getClientOriginalName() }}
                    </p>
                    <p class="text-xs text-[#6F767E]">
                        {{ number_format($file->getSize() / 1024, 2) }} KB
                    </p>
                </div>
                <button 
                    type="button"
                    wire:click="removeFile({{ $index }})"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-[#1A1D1F] rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-red-600 text-lg">close</span>
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Validation Errors --}}
    @error('files.*')
    <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $message }}</p>
    </div>
    @enderror

    {{-- Action Buttons --}}
    <div class="flex gap-3 pt-4">
        @if(!$isModal)
        {{-- Inline mode: show back button --}}
        <a wire:navigate
            href="{{ route('admin.media.index') }}"
            class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all text-center">
            Cancel
        </a>
        @else
        {{-- Modal mode: show close button --}}
        <button 
            type="button"
            @click="show = false"
            class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">
            Cancel
        </button>
        @endif
        <button 
            type="button"
            wire:click="save"
            wire:loading.attr="disabled"
            wire:target="save,files"
            @if(empty($files)) disabled @endif
            class="flex-1 px-4 py-3 bg-[#2563EB] text-white rounded-xl font-semibold hover:bg-[#1D4ED8] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="save">
                Upload @if(is_array($files) && count($files) > 0) ({{ count($files) }}) @endif
            </span>
            <span wire:loading wire:target="save">
                Uploading...
            </span>
        </button>
    </div>
</div>
