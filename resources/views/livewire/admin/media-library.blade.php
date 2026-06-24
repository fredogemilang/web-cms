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
            <div class="flex flex-wrap gap-2 w-full md:w-auto">
                <select wire:model.live="filterType"
                    class="px-3 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All types</option>
                    <option value="images">Images</option>
                    <option value="documents">Documents</option>
                </select>

                <select wire:model.live="filterExtension"
                    class="px-3 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All extensions</option>
                    @foreach($availableExtensions as $ext)
                        <option value="{{ $ext }}">.{{ strtoupper($ext) }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterAltStatus"
                    class="px-3 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    title="Filter by alt text status (images only)">
                    <option value="">Any alt status</option>
                    <option value="missing">Missing alt</option>
                    <option value="has">Has alt</option>
                </select>

                <select wire:model.live="filterUploader"
                    class="px-3 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Any uploader</option>
                    @foreach($uploaders as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterUsage"
                    class="px-3 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Any usage</option>
                    <option value="used">Used</option>
                    <option value="orphan">Orphans only</option>
                </select>

                <input type="date" wire:model.live="dateFrom" title="From"
                    class="px-3 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                <input type="date" wire:model.live="dateTo" title="To"
                    class="px-3 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent" />

                <select wire:model.live="sortBy"
                    class="px-3 py-2 rounded-xl border border-gray-300 dark:border-[#272B30] dark:bg-[#1A1D1F] text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="latest">Latest</option>
                    <option value="oldest">Oldest</option>
                    <option value="name">Name A-Z</option>
                    <option value="size">Size ↓</option>
                </select>

                @if($this->hasActiveFilters())
                    <button wire:click="clearFilters"
                        class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] hover:bg-gray-200 dark:hover:bg-[#333] text-sm font-medium flex items-center gap-1 transition">
                        <span class="material-symbols-outlined text-lg">close</span>
                        Clear
                    </button>
                @endif

                <div class="flex gap-1 p-1 bg-gray-100 dark:bg-[#272B30] rounded-xl">
                    <button wire:click="$set('viewMode', 'grid')"
                        class="px-3 py-2 rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-white dark:bg-[#1A1D1F] text-[#2563EB] shadow-sm' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                        <span class="material-symbols-outlined text-xl">grid_view</span>
                    </button>
                    <button wire:click="$set('viewMode', 'list')"
                        class="px-3 py-2 rounded-lg transition-all {{ $viewMode === 'list' ? 'bg-white dark:bg-[#1A1D1F] text-[#2563EB] shadow-sm' : 'text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]' }}">
                        <span class="material-symbols-outlined text-xl">view_list</span>
                    </button>
                </div>
            </div>
        </div>

        @php
            $pageSelected = count(array_intersect($currentPageIds, $selectedMedia));
            $allOnPage    = $pageSelected === count($currentPageIds) && count($currentPageIds) > 0;
            $someOnPage   = $pageSelected > 0 && !$allOnPage;
        @endphp

        {{-- Header: select-all-on-page checkbox + total count --}}
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-[#272B30] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="toggleSelectCurrentPage({{ json_encode($currentPageIds) }})"
                    class="flex items-center gap-2 text-sm font-medium text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition"
                    title="Select all on this page"
                >
                    <span @class([
                        'h-5 w-5 rounded border-2 flex items-center justify-center transition',
                        'bg-[#2563EB] border-[#2563EB] text-white' => $allOnPage,
                        'bg-[#2563EB]/60 border-[#2563EB]/60 text-white' => $someOnPage,
                        'bg-white dark:bg-transparent border-gray-300 dark:border-[#6F767E]' => !$allOnPage && !$someOnPage,
                    ])>
                        @if($allOnPage)
                            <span class="material-symbols-outlined text-sm">check</span>
                        @elseif($someOnPage)
                            <span class="material-symbols-outlined text-sm">remove</span>
                        @endif
                    </span>
                    Select page ({{ count($currentPageIds) }})
                </button>

                <span class="text-xs text-[#6F767E]">·</span>

                <span class="text-xs text-[#6F767E]">
                    {{ $media->total() }} total
                </span>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        @if(count($selectedMedia) > 0)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-[#272B30] flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-[#111827] dark:text-[#FCFCFC]">
                    {{ count($selectedMedia) }} selected
                </span>
                <button wire:click="deselectAll"
                    class="text-sm text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition">
                    Clear selection
                </button>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('media.edit')
                <button wire:click="openBulkAlt"
                    class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-[#2563EB] dark:text-blue-400 rounded-xl font-semibold text-sm hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                    <span class="material-symbols-outlined text-lg">text_fields</span>
                    Set alt text
                </button>
                @endcan
                <button wire:click="downloadSelectedZip"
                    class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-xl font-semibold text-sm hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Download .zip
                </button>
                @can('media.delete')
                <button wire:click="confirmDelete"
                    class="flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl font-semibold text-sm hover:bg-red-100 dark:hover:bg-red-900/30 transition">
                    <span class="material-symbols-outlined text-lg">delete</span>
                    Delete
                </button>
                @endcan
            </div>
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

                        {{-- Usage badge --}}
                        @php $uses = $usageMap[$item->id] ?? 0; @endphp
                        <div class="absolute top-2 right-2">
                            @if($uses === 0)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/95 text-white" title="Not referenced anywhere">Orphan</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-black/60 text-white" title="Referenced in {{ $uses }} place(s)">{{ $uses }}×</span>
                            @endif
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
                            <div class="flex items-center gap-3 mt-1 flex-wrap">
                                <span class="text-xs text-[#6F767E]">{{ $item->human_readable_size }}</span>
                                @if($item->width && $item->height)
                                    <span class="text-xs text-[#6F767E]">{{ $item->width }} × {{ $item->height }}</span>
                                @endif
                                <span class="text-xs text-[#6F767E]">{{ $item->created_at->format('M d, Y') }}</span>
                                @php $uses = $usageMap[$item->id] ?? 0; @endphp
                                @if($uses === 0)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-400">Orphan</span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-500/15 text-[#2563EB]">{{ $uses }}× used</span>
                                @endif
                                @if($item->isImage() && empty($item->alt_text))
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-500/15 text-red-600 dark:text-red-400" title="Missing alt text">No alt</span>
                                @endif
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

            <!-- Pagination -->
            @if($media->hasPages())
            <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30] flex items-center justify-between mt-6">
                <p class="text-sm font-medium text-[#6F767E]">
                    Showing {{ $media->firstItem() }} to {{ $media->lastItem() }} of {{ $media->total() }} media files
                </p>
                <div class="flex items-center gap-2">
                    @if($media->onFirstPage())
                    <button disabled
                        class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] opacity-50 cursor-not-allowed">
                        <span class="material-symbols-outlined text-xl">chevron_left</span>
                    </button>
                    @else
                    <button wire:click="previousPage"
                        class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                        <span class="material-symbols-outlined text-xl">chevron_left</span>
                    </button>
                    @endif

                    @foreach($media->getUrlRange(max(1, $media->currentPage() - 2), min($media->lastPage(), $media->currentPage() + 2)) as $page => $url)
                        @if($page == $media->currentPage())
                        <button class="h-10 w-10 rounded-xl bg-[#2563EB] text-white flex items-center justify-center text-sm font-bold shadow-lg shadow-blue-500/20">{{ $page }}</button>
                        @else
                        <button wire:click="gotoPage({{ $page }})" class="h-10 w-10 rounded-xl bg-white dark:bg-[#1A1A1A] flex items-center justify-center text-sm font-bold text-[#6F767E] hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all">{{ $page }}</button>
                        @endif
                    @endforeach

                    @if($media->hasMorePages())
                    <button wire:click="nextPage"
                        class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition-all">
                        <span class="material-symbols-outlined text-xl">chevron_right</span>
                    </button>
                    @else
                    <button disabled
                        class="h-10 w-10 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] flex items-center justify-center text-[#6F767E] opacity-50 cursor-not-allowed">
                        <span class="material-symbols-outlined text-xl">chevron_right</span>
                    </button>
                    @endif
                </div>
            </div>
            @endif
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

    {{-- Bulk Alt Text Modal --}}
    @if($showBulkAltModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="$set('showBulkAltModal', false)">
        <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl p-8 max-w-md w-full mx-4 shadow-xl" wire:click.stop>
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#2563EB] dark:text-blue-400">text_fields</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Bulk Set Alt Text</h3>
                    <p class="text-sm text-[#6F767E]">Applies to selected images only ({{ count($selectedMedia) }} item(s))</p>
                </div>
            </div>
            <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Alt text</label>
            <textarea
                wire:model.lazy="bulkAltText"
                rows="3"
                placeholder="e.g. Product photo on white background"
                class="w-full rounded-xl border border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#0F1113] px-4 py-2.5 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:outline-none"
            ></textarea>
            <p class="text-xs text-[#6F767E] mt-2">Tip: leave blank to clear the alt text from all selected images.</p>
            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showBulkAltModal', false)"
                    class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-[#272B30] text-sm font-semibold text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#272B30] transition">
                    Cancel
                </button>
                <button wire:click="applyBulkAlt"
                    class="flex-1 px-4 py-3 rounded-xl bg-[#2563EB] text-white text-sm font-semibold hover:bg-blue-700 transition">
                    Apply
                </button>
            </div>
        </div>
    </div>
    @endif

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
