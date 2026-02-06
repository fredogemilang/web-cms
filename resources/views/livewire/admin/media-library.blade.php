<div>
    {{-- Filter Bar --}}
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 shadow-sm border border-gray-200 dark:border-[#272B30] mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
            {{-- Search --}}
            <div class="flex-1 w-full md:max-w-md">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#6F767E]">search</span>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search media..." 
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            {{-- Filters --}}
            <div class="flex gap-3 w-full md:w-auto">
                {{-- Type Filter --}}
                <select 
                    wire:model.live="filterType"
                    class="px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All Types</option>
                    <option value="images">Images</option>
                    <option value="documents">Documents</option>
                </select>

                {{-- Sort By --}}
                <select 
                    wire:model.live="sortBy"
                    class="px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="latest">Latest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name">Name (A-Z)</option>
                    <option value="size">Size (Largest)</option>
                </select>

                {{-- View Mode Toggle --}}
                <div class="flex gap-1 p-1 bg-gray-100 dark:bg-[#272B30] rounded-xl">
                    <button 
                        wire:click="$set('viewMode', 'grid')"
                        class="px-3 py-2 rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-white dark:bg-[#1A1D1F] text-[#2563EB] shadow-sm' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                        <span class="material-symbols-outlined text-xl">grid_view</span>
                    </button>
                    <button 
                        wire:click="$set('viewMode', 'list')"
                        class="px-3 py-2 rounded-lg transition-all {{ $viewMode === 'list' ? 'bg-white dark:bg-[#1A1D1F] text-[#2563EB] shadow-sm' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                        <span class="material-symbols-outlined text-xl">view_list</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        @if(count($selectedMedia) > 0)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-[#272B30] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC]">
                    {{ count($selectedMedia) }} selected
                </span>
                <button 
                    wire:click="deselectAll"
                    class="text-sm text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-colors">
                    Clear selection
                </button>
            </div>
            @can('media.delete')
            <button 
                wire:click="confirmDelete"
                class="flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl font-semibold hover:bg-red-100 dark:hover:bg-red-900/30 transition-all">
                <span class="material-symbols-outlined text-lg">delete</span>
                <span>Delete Selected</span>
            </button>
            @endcan
        </div>
        @endif
    </div>

    {{-- Media Grid/List --}}
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 shadow-sm border border-gray-200 dark:border-[#272B30]">
        @if($media->count() > 0)
            @if($viewMode === 'grid')
                {{-- Grid View --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                    @foreach($media as $item)
                    <div 
                        class="group relative aspect-square rounded-xl overflow-hidden border-2 transition-all cursor-pointer {{ in_array($item->id, $selectedMedia) ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-800' : 'border-gray-200 dark:border-[#272B30] hover:border-blue-300 dark:hover:border-blue-700' }}"
                        wire:click="selectMedia({{ $item->id }})">
                        
                        {{-- Media Preview --}}
                        @if($item->isImage())
                            <img 
                                src="{{ $item->webp_url ?? $item->url }}" 
                                alt="{{ $item->alt_text ?? $item->original_filename }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-[#272B30]">
                                <span class="material-symbols-outlined text-5xl text-[#6F767E]">description</span>
                            </div>
                        @endif

                        {{-- Overlay --}}
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <button 
                                wire:click.stop="$dispatch('openMediaDetails', { mediaId: {{ $item->id }} })"
                                class="p-2 bg-white/90 dark:bg-black/90 rounded-lg hover:bg-white dark:hover:bg-black transition-colors"
                                title="View Details">
                                <span class="material-symbols-outlined text-[#111827] dark:text-[#FCFCFC]">info</span>
                            </button>
                            @can('media.delete')
                            <button 
                                wire:click.stop="confirmDelete({{ $item->id }})"
                                class="p-2 bg-white/90 dark:bg-black/90 rounded-lg hover:bg-white dark:hover:bg-black transition-colors"
                                title="Delete">
                                <span class="material-symbols-outlined text-red-600">delete</span>
                            </button>
                            @endcan
                        </div>

                        {{-- Selection Checkbox --}}
                        <div class="absolute top-2 left-2">
                            <div class="w-5 h-5 rounded border-2 {{ in_array($item->id, $selectedMedia) ? 'bg-blue-500 border-blue-500' : 'bg-white/80 border-white' }} flex items-center justify-center">
                                @if(in_array($item->id, $selectedMedia))
                                    <span class="material-symbols-outlined text-white text-sm">check</span>
                                @endif
                            </div>
                        </div>

                        {{-- File Info --}}
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <p class="text-xs font-medium text-white truncate">{{ $item->original_filename }}</p>
                            <p class="text-[10px] text-white/80">{{ $item->human_readable_size }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                {{-- List View --}}
                <div class="space-y-2">
                    @foreach($media as $item)
                    <div 
                        class="group flex items-center gap-4 p-4 rounded-xl border-2 transition-all cursor-pointer {{ in_array($item->id, $selectedMedia) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/10' : 'border-gray-200 dark:border-[#272B30] hover:border-blue-300 dark:hover:border-blue-700 hover:bg-gray-50 dark:hover:bg-[#272B30]/30' }}"
                        wire:click="selectMedia({{ $item->id }})">
                        
                        {{-- Selection Checkbox --}}
                        <div class="flex-shrink-0">
                            <div class="w-5 h-5 rounded border-2 {{ in_array($item->id, $selectedMedia) ? 'bg-blue-500 border-blue-500' : 'border-gray-300 dark:border-[#6F767E]' }} flex items-center justify-center">
                                @if(in_array($item->id, $selectedMedia))
                                    <span class="material-symbols-outlined text-white text-sm">check</span>
                                @endif
                            </div>
                        </div>

                        {{-- Thumbnail --}}
                        <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-[#272B30]">
                            @if($item->isImage())
                                <img 
                                    src="{{ $item->webp_url ?? $item->url }}" 
                                    alt="{{ $item->alt_text ?? $item->original_filename }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-2xl text-[#6F767E]">description</span>
                                </div>
                            @endif
                        </div>

                        {{-- File Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] truncate">
                                {{ $item->original_filename }}
                            </p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-xs text-[#6F767E]">{{ $item->human_readable_size }}</span>
                                @if($item->width && $item->height)
                                    <span class="text-xs text-[#6F767E]">{{ $item->width }} × {{ $item->height }}</span>
                                @endif
                                <span class="text-xs text-[#6F767E]">{{ $item->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex-shrink-0 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button 
                                wire:click.stop="$dispatch('openMediaDetails', { mediaId: {{ $item->id }} })"
                                class="p-2 bg-gray-100 dark:bg-[#272B30] rounded-lg hover:bg-gray-200 dark:hover:bg-[#1A1D1F] transition-colors"
                                title="View Details">
                                <span class="material-symbols-outlined text-[#6F767E]">info</span>
                            </button>
                            @can('media.delete')
                            <button 
                                wire:click.stop="confirmDelete({{ $item->id }})"
                                class="p-2 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                                title="Delete">
                                <span class="material-symbols-outlined text-red-600">delete</span>
                            </button>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $media->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-16">
                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-5xl text-[#6F767E]">perm_media</span>
                </div>
                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">No media found</h3>
                <p class="text-sm text-[#6F767E] mb-6">
                    @if($search || $filterType !== 'all')
                        Try adjusting your filters or search terms
                    @else
                        Upload your first media file to get started
                    @endif
                </p>
                @can('media.upload')
                    @if(!$search && $filterType === 'all')
                    <button 
                        onclick="Livewire.dispatch('open-upload-modal')"
                        class="flex items-center gap-2 px-6 py-3 bg-[#2563EB] text-white rounded-xl font-semibold hover:bg-[#1D4ED8] transition-all">
                        <span class="material-symbols-outlined text-xl">add</span>
                        <span>Upload Media</span>
                    </button>
                    @endif
                @endcan
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    {{-- Backdrop with fade animation --}}
    <div 
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
        wire:click="cancelDelete">
        
        {{-- Modal with scale animation --}}
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white dark:bg-[#1A1A1A] rounded-3xl p-8 max-w-md w-full mx-4 shadow-xl" 
            wire:click.stop>
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">delete</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Delete Media</h3>
                    <p class="text-sm text-[#6F767E]">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-[#6F767E] mb-6">
                Are you sure you want to delete 
                @if($mediaToDelete)
                    this media file?
                @else
                    {{ count($selectedMedia) }} media file(s)?
                @endif
            </p>
            <div class="flex gap-3">
                <button 
                    wire:click="cancelDelete"
                    class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">
                    Cancel
                </button>
                <button 
                    wire:click="deleteSelected"
                    class="flex-1 px-4 py-3 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-all">
                    Delete
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
