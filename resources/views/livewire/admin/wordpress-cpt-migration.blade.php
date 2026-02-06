<div>
    {{-- Step 1: Input URL --}}
    @if($step === 1)
    <div class="space-y-6">
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="h-12 w-12 rounded-2xl bg-[#8B5CF6]/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#8B5CF6] text-2xl">widgets</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Import WordPress CPT</h2>
                    <p class="text-sm text-[#6F767E]">Import Custom Post Types from WordPress</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#6F767E] mb-2">WordPress Site URL</label>
                    <div class="flex gap-3">
                        <input
                            wire:model="wpUrl"
                            type="url"
                            placeholder="https://yoursite.com"
                            class="flex-1 h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#8B5CF6] transition-all placeholder:text-[#6F767E]"
                        />
                        <button
                            wire:click="fetchCptTypes"
                            wire:loading.attr="disabled"
                            class="h-12 px-6 rounded-xl bg-[#8B5CF6] text-white font-bold text-sm hover:bg-purple-700 transition-all shadow-lg shadow-purple-500/20 disabled:opacity-50 flex items-center gap-2"
                        >
                            <span wire:loading.remove wire:target="fetchCptTypes" class="material-symbols-outlined text-xl">search</span>
                            <svg wire:loading wire:target="fetchCptTypes" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="fetchCptTypes">Discover CPTs</span>
                            <span wire:loading wire:target="fetchCptTypes">Loading...</span>
                        </button>
                    </div>
                </div>

                @if($errorMessage)
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-500">error</span>
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
                    </div>
                </div>
                @endif

                <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-purple-500 mt-0.5">info</span>
                        <div class="text-sm text-purple-600 dark:text-purple-400">
                            <p class="font-medium mb-1">Custom Post Type Migration:</p>
                            <ul class="list-disc list-inside space-y-1 text-purple-500">
                                <li>Discover available CPTs from WordPress</li>
                                <li>Map WordPress fields to CMS fields</li>
                                <li>Support for ACF and custom meta fields</li>
                                <li>Images downloaded to Media Library</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 2: Select WordPress CPT --}}
    @if($step === 2)
    <div class="space-y-6">
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="h-12 w-12 rounded-2xl bg-[#83BF6E]/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#83BF6E] text-2xl">check_circle</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Select Post Type</h2>
                    <p class="text-sm text-[#6F767E]">Choose a WordPress post type to import</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#6F767E] mb-2">WordPress Post Type</label>
                    <select
                        wire:model.live="selectedWpCpt"
                        class="w-full h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#8B5CF6]"
                    >
                        <option value="">-- Select Post Type --</option>
                        @foreach($availableCpts as $cpt)
                        <option value="{{ $cpt['slug'] }}">{{ $cpt['name'] }} ({{ $cpt['slug'] }})</option>
                        @endforeach
                    </select>
                </div>

                @if($errorMessage)
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-500">error</span>
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
                    </div>
                </div>
                @endif

                {{-- Available CPTs List --}}
                <div class="border-t border-gray-100 dark:border-[#272B30] pt-4">
                    <h4 class="text-sm font-bold text-[#6F767E] mb-3">Available Post Types:</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($availableCpts as $cpt)
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-[#0B0B0B] {{ $selectedWpCpt === $cpt['slug'] ? 'ring-2 ring-[#8B5CF6]' : '' }}">
                            <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $cpt['name'] }}</p>
                            <p class="text-xs text-[#6F767E]">{{ $cpt['slug'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between">
            <button
                wire:click="goBack"
                class="h-12 px-6 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-bold text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition-all flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-xl">arrow_back</span>
                Back
            </button>
            <button
                wire:click="selectWpCpt"
                wire:loading.attr="disabled"
                class="h-12 px-8 rounded-xl bg-[#8B5CF6] text-white font-bold text-sm hover:bg-purple-700 transition-all shadow-lg shadow-purple-500/20 disabled:opacity-50 flex items-center gap-2"
            >
                <span wire:loading.remove wire:target="selectWpCpt" class="material-symbols-outlined text-xl">arrow_forward</span>
                <svg wire:loading wire:target="selectWpCpt" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Continue
            </button>
        </div>
    </div>
    @endif

    {{-- Step 3: Field Mapping --}}
    @if($step === 3)
    <div class="space-y-6">
        {{-- Summary --}}
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-[#8B5CF6]/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[#8B5CF6] text-2xl">sync_alt</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Field Mapping</h2>
                        <p class="text-sm text-[#6F767E]">{{ $totalPosts }} items found in "{{ $selectedWpCpt }}"</p>
                    </div>
                </div>
                <span class="px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-[#8B5CF6] text-sm font-bold">
                    {{ $totalPosts }} Items
                </span>
            </div>

            {{-- Target CMS CPT Selection --}}
            <div class="border-t border-gray-100 dark:border-[#272B30] pt-4 mb-4">
                <label class="block text-sm font-medium text-[#6F767E] mb-2">Target CMS Post Type</label>
                <select
                    wire:model.live="selectedCmsCpt"
                    class="w-full h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#8B5CF6]"
                >
                    <option value="">-- Select Target Post Type --</option>
                    @foreach($cmsCpts as $cpt)
                    <option value="{{ $cpt['id'] }}">{{ $cpt['name'] }} ({{ $cpt['slug'] }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Field Mapping Table --}}
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6">
            <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-4">Map Fields</h3>
            
            <div class="space-y-3">
                @foreach($cmsCptFields as $cmsField)
                <div class="flex items-center gap-4 p-3 rounded-xl bg-gray-50 dark:bg-[#0B0B0B]">
                    <div class="w-1/3">
                        <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $cmsField['label'] }}</p>
                        <p class="text-xs text-[#6F767E]">{{ $cmsField['key'] }}</p>
                    </div>
                    <span class="material-symbols-outlined text-[#6F767E]">arrow_forward</span>
                    <div class="flex-1">
                        <select
                            wire:model.live="fieldMappings.{{ $cmsField['key'] }}"
                            class="w-full h-10 rounded-lg border-none bg-white dark:bg-[#1A1A1A] px-3 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#8B5CF6]"
                        >
                            <option value="">-- Don't import --</option>
                            @foreach($wpCptFields as $wpField)
                            <option value="{{ $wpField['path'] }}">{{ $wpField['label'] }} {{ $wpField['sample'] ? '(' . $wpField['sample'] . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Import Options --}}
            <div class="border-t border-gray-100 dark:border-[#272B30] pt-4 mt-4">
                <h4 class="text-sm font-bold text-[#6F767E] mb-3">Image Options</h4>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="downloadFeaturedImage" class="custom-checkbox" />
                        <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Download Featured Images</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="downloadContentImages" class="custom-checkbox" />
                        <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Download Content Images</span>
                    </label>
                </div>
            </div>
        </div>

        @if($errorMessage)
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-500">error</span>
                <p class="text-sm text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between">
            <button
                wire:click="goBack"
                class="h-12 px-6 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-bold text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition-all flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-xl">arrow_back</span>
                Back
            </button>
            <button
                wire:click="importAllPosts"
                wire:loading.attr="disabled"
                class="h-12 px-8 rounded-xl bg-[#8B5CF6] text-white font-bold text-sm hover:bg-purple-700 transition-all shadow-lg shadow-purple-500/20 disabled:opacity-50 flex items-center gap-2"
            >
                <span wire:loading.remove wire:target="importAllPosts" class="material-symbols-outlined text-xl">cloud_download</span>
                <svg wire:loading wire:target="importAllPosts" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Import All {{ $totalPosts }} Items
            </button>
        </div>
    </div>
    @endif

    {{-- Step 4: Import Results --}}
    @if($step === 4)
    <div class="space-y-6">
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-8">
            <div class="flex flex-col items-center text-center">
                @if($importResults['failed'] === 0)
                <div class="h-16 w-16 rounded-full bg-[#83BF6E]/10 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-[#83BF6E] text-3xl">check_circle</span>
                </div>
                <h2 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Import Completed!</h2>
                @else
                <div class="h-16 w-16 rounded-full bg-amber-500/10 flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-amber-500 text-3xl">warning</span>
                </div>
                <h2 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Import Completed with Issues</h2>
                @endif
                <p class="text-[#6F767E] mb-8">Your WordPress CPT entries have been imported.</p>

                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-4 w-full max-w-md mb-8">
                    <div class="p-4 rounded-2xl bg-[#83BF6E]/10 border border-[#83BF6E]/20">
                        <p class="text-3xl font-bold text-[#83BF6E]">{{ $importResults['success'] }}</p>
                        <p class="text-sm font-medium text-[#6F767E]">Imported</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                        <p class="text-3xl font-bold text-amber-500">{{ $importResults['skipped'] }}</p>
                        <p class="text-sm font-medium text-[#6F767E]">Skipped</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20">
                        <p class="text-3xl font-bold text-red-500">{{ $importResults['failed'] }}</p>
                        <p class="text-sm font-medium text-[#6F767E]">Failed</p>
                    </div>
                </div>

                {{-- Skipped Posts List --}}
                @if(!empty($importResults['skipped_posts']))
                <div class="w-full max-w-lg text-left mb-6">
                    <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">skip_next</span>
                        Skipped Items ({{ count($importResults['skipped_posts']) }}):
                    </h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach(array_slice($importResults['skipped_posts'], 0, 10) as $skipped)
                        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                            <p class="text-sm font-medium text-amber-700 dark:text-amber-300">{{ Str::limit($skipped['title'], 50) }}</p>
                            <p class="text-xs text-amber-500">{{ $skipped['reason'] }} — <code class="bg-amber-100 dark:bg-amber-800/30 px-1 rounded">{{ $skipped['slug'] }}</code></p>
                        </div>
                        @endforeach
                        @if(count($importResults['skipped_posts']) > 10)
                        <p class="text-xs text-[#6F767E] text-center py-2">... and {{ count($importResults['skipped_posts']) - 10 }} more skipped</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Errors List --}}
                @if(!empty($importResults['errors']))
                <div class="w-full max-w-lg text-left mb-8">
                    <h4 class="text-sm font-bold text-red-600 dark:text-red-400 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">error</span>
                        Failed Imports ({{ count($importResults['errors']) }}):
                    </h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach(array_slice($importResults['errors'], 0, 10) as $error)
                        <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">{!! Str::limit(strip_tags($error['title']), 50) !!}</p>
                            <p class="text-xs text-red-500">{{ $error['error'] }}</p>
                        </div>
                        @endforeach
                        @if(count($importResults['errors']) > 10)
                        <p class="text-xs text-[#6F767E] text-center">... and {{ count($importResults['errors']) - 10 }} more errors</p>
                        @endif
                    </div>
                </div>
                @endif

                <div class="flex items-center gap-4">
                    <button
                        wire:click="resetMigration"
                        class="h-12 px-6 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-bold text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition-all"
                    >
                        Import More
                    </button>
                    @if($selectedCmsCpt)
                    @php
                        $targetCpt = collect($cmsCpts)->firstWhere('id', $selectedCmsCpt);
                    @endphp
                    <a
                        href="{{ route('admin.cpt.entries.index', $targetCpt['slug'] ?? '') }}"
                        wire:navigate
                        class="h-12 px-6 rounded-xl bg-[#8B5CF6] text-white font-bold text-sm hover:bg-purple-700 transition-all shadow-lg shadow-purple-500/20 flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined text-xl">list</span>
                        View Entries
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Loading Overlay (for import) --}}
    @if($isLoading && $step === 3)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl p-8 shadow-2xl text-center max-w-sm w-full mx-4">
            <div class="mb-6">
                <svg class="animate-spin h-12 w-12 text-[#8B5CF6] mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Importing CPT Entries...</h3>
            <p class="text-sm text-[#6F767E] mb-2">Processing page {{ $currentPageImporting }} of {{ $totalPages }}</p>
            <p class="text-xs text-[#6F767E] mb-4">Please wait while we import your entries.</p>
            <div class="w-full bg-gray-100 dark:bg-[#272B30] rounded-full h-2.5">
                <div class="bg-[#8B5CF6] h-2.5 rounded-full transition-all duration-300" style="width: {{ $importProgress }}%"></div>
            </div>
            <p class="text-sm font-bold text-[#8B5CF6] mt-2">{{ $importProgress }}%</p>
        </div>
    </div>
    @endif
</div>
