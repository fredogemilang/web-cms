<div>
    {{-- Step 1: Input URL --}}
    @if($step === 1)
    <div class="space-y-6">
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="h-12 w-12 rounded-2xl bg-[#EAB308]/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#EAB308] text-2xl">event</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Import WordPress Events</h2>
                    <p class="text-sm text-[#6F767E]">Import events from WordPress (Example: The Events Calendar, or Custom Post Types)</p>
                </div>
            </div>
            
            <div class="mb-6 bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                     <span class="material-symbols-outlined text-indigo-600">mic</span>
                     <span class="text-indigo-900 font-medium text-sm">Need to import Speakers too?</span>
                </div>
                <a href="{{ route('admin.events.migration.wordpress.speakers') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700 bg-white px-3 py-1.5 rounded-lg border border-indigo-200 shadow-sm transition-all hover:shadow-md">
                    Go to Speaker Import
                </a>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#6F767E] mb-2">WordPress Site URL</label>
                    <div class="flex gap-3">
                        <input
                            wire:model="wpUrl"
                            type="url"
                            placeholder="https://yoursite.com"
                            class="flex-1 h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#EAB308] transition-all placeholder:text-[#6F767E]"
                        />
                        <button
                            wire:click="fetchPostTypes"
                            wire:loading.attr="disabled"
                            class="h-12 px-6 rounded-xl bg-[#EAB308] text-white font-bold text-sm hover:bg-yellow-600 transition-all shadow-lg shadow-yellow-500/20 disabled:opacity-50 flex items-center gap-2"
                        >
                            <span wire:loading.remove wire:target="fetchPostTypes" class="material-symbols-outlined text-xl">search</span>
                            <span wire:loading wire:target="fetchPostTypes" class="animate-spin h-5 w-5 rounded-full border-2 border-white border-t-transparent"></span>
                            <span wire:loading.remove wire:target="fetchPostTypes">Find Types</span>
                            <span wire:loading wire:target="fetchPostTypes">Loading...</span>
                        </button>
                    </div>
                </div>

                @if($errorMessage)
                <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-500">error</span>
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $errorMessage }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Step 2: Select Post Type --}}
    @if($step === 2)
    <div class="space-y-6">
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6">
            <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC] mb-4">Select Event Post Type</h2>
            
            <div class="space-y-4">
                <label class="block text-sm font-medium text-[#6F767E] mb-2">Which Post Type holds your events?</label>
                <select
                    wire:model.live="selectedWpPostType"
                    class="w-full h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#EAB308]"
                >
                    <option value="">-- Select Post Type --</option>
                    @foreach($availablePostTypes as $type)
                    <option value="{{ $type['slug'] }}">{{ $type['name'] }} ({{ $type['slug'] }})</option>
                    @endforeach
                </select>
                
                <div class="text-xs text-[#6F767E] flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">info</span>
                    Common types: <b>tribe_events</b> (The Events Calendar), <b>event</b>, or just <b>post</b> for simple blogs.
                </div>
            </div>
        </div>

        <div class="flex justify-between">
            <button wire:click="goBack" class="btn-secondary">Back</button>
            <button wire:click="selectWpPostType" class="h-12 px-8 rounded-xl bg-[#EAB308] text-white font-bold hover:bg-yellow-600 transition-all shadow-lg shadow-yellow-500/20">
                Continue
            </button>
        </div>
    </div>
    @endif

    {{-- Step 3: Field Mapping --}}
    @if($step === 3)
    <div class="space-y-6">
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 border border-gray-200 dark:border-[#272B30]">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Map Event Fields</h2>
                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-lg text-sm font-bold">{{ $totalPosts }} Events Found</span>
            </div>
            
            <div class="space-y-4">
                @foreach($cmsEventFields as $cmsField)
                <div class="flex items-center gap-4 p-3 rounded-xl bg-gray-50 dark:bg-[#0B0B0B]">
                    <div class="w-1/3">
                        <p class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">
                            {{ $cmsField['label'] }}
                            @if($cmsField['required']) <span class="text-red-500">*</span> @endif
                        </p>
                        <p class="text-xs text-[#6F767E]">{{ $cmsField['key'] }}</p>
                    </div>
                    <span class="material-symbols-outlined text-[#6F767E]">arrow_forward</span>
                    <div class="flex-1">
                        <select
                            wire:model.live="fieldMappings.{{ $cmsField['key'] }}"
                            class="w-full h-10 rounded-lg border-none bg-white dark:bg-[#1A1A1A] px-3 text-sm ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#EAB308]"
                        >
                            <option value="">-- Select WP Field --</option>
                            @foreach($wpEventFields as $wpField)
                            <option value="{{ $wpField['path'] }}">{{ $wpField['label'] }} ({{ $wpField['sample'] }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endforeach
            </div>
            
             <div class="mt-6 border-t border-gray-100 dark:border-[#272B30] pt-4">
                <label class="flex items-center gap-2 cursor-pointer mb-2">
                    <input type="checkbox" wire:model.live="downloadFeaturedImage" class="custom-checkbox h-5 w-5 text-yellow-500 rounded focus:ring-yellow-500" />
                    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Download Featured Images</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="downloadContentImages" class="custom-checkbox h-5 w-5 text-yellow-500 rounded focus:ring-yellow-500" />
                    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Download Images inside Content</span>
                </label>
            </div>
        </div>

        @if($errorMessage)
            <div class="text-red-500 text-sm font-bold p-4 bg-red-50 border border-red-200 rounded-xl">{{ $errorMessage }}</div>
        @endif

        <div class="flex justify-between">
            <button wire:click="goBack" class="h-12 px-6 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-bold hover:bg-gray-200">Back</button>
            <button wire:click="importAllEvents" wire:loading.attr="disabled" class="h-12 px-8 rounded-xl bg-[#EAB308] text-white font-bold hover:bg-yellow-600 shadow-lg shadow-yellow-500/20 disabled:opacity-50">
                <span wire:loading.remove wire:target="importAllEvents">Start Import</span>
                <span wire:loading wire:target="importAllEvents">Importing...</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Step 4: Results --}}
    @if($step === 4)
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-8 text-center">
        @if($importResults['failed'] === 0)
            <div class="mx-auto h-16 w-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-green-600 text-3xl">check_circle</span>
            </div>
            <h2 class="text-2xl font-bold text-green-600 mb-2">Import Complete!</h2>
        @else
            <div class="mx-auto h-16 w-16 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-amber-600 text-3xl">warning</span>
            </div>
            <h2 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Import Finished with Issues</h2>
        @endif

        <div class="grid grid-cols-3 gap-4 max-w-md mx-auto mb-8 mt-6">
            <div class="bg-green-50 p-4 rounded-xl">
                <div class="text-2xl font-bold text-green-600">{{ $importResults['success'] }}</div>
                <div class="text-xs text-green-800">Success</div>
            </div>
             <div class="bg-amber-50 p-4 rounded-xl">
                <div class="text-2xl font-bold text-amber-600">{{ $importResults['skipped'] }}</div>
                <div class="text-xs text-amber-800">Skipped</div>
            </div>
             <div class="bg-red-50 p-4 rounded-xl">
                <div class="text-2xl font-bold text-red-600">{{ $importResults['failed'] }}</div>
                <div class="text-xs text-red-800">Failed</div>
            </div>
        </div>
        
        @if(!empty($importResults['skipped_items']))
        <div class="text-left max-w-xl mx-auto mb-6">
            <h4 class="font-bold text-amber-600 text-sm mb-2">Skipped Items:</h4>
            <div class="bg-amber-50 rounded-xl p-4 max-h-40 overflow-y-auto text-xs space-y-1">
                @foreach($importResults['skipped_items'] as $item)
                    <div>• {{ $item['title'] }} ({{ $item['reason'] }})</div>
                @endforeach
            </div>
        </div>
        @endif
        
        @if(!empty($importResults['errors']))
        <div class="text-left max-w-xl mx-auto mb-6">
            <h4 class="font-bold text-red-600 text-sm mb-2">Errors:</h4>
            <div class="bg-red-50 rounded-xl p-4 max-h-40 overflow-y-auto text-xs space-y-1">
                @foreach($importResults['errors'] as $error)
                     <div class="text-red-700">• {{ $error['title'] }}: {{ $error['error'] }}</div>
                @endforeach
            </div>
        </div>
        @endif

        <button wire:click="resetMigration" class="btn-secondary">Import More</button>
         <a href="{{ route('events.admin.index') }}" class="ml-2 inline-flex items-center justify-center h-10 px-6 rounded-xl bg-[#EAB308] text-white font-bold hover:bg-yellow-600 transition-all">
            View Events
        </a>
    </div>
    @endif
    
    {{-- Loading Overlay --}}
    @if($isLoading && $step === 3)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1A1A1A] p-8 rounded-3xl shadow-2xl text-center max-w-sm w-full">
            <div class="animate-spin h-10 w-10 border-4 border-[#EAB308] border-t-transparent rounded-full mx-auto mb-4"></div>
            <h3 class="font-bold text-lg mb-2 dark:text-white">Importing Events...</h3>
            <p class="text-sm text-gray-500 mb-4">Page {{ $currentPageImporting }} of {{ $totalPages }}</p>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-[#EAB308] h-2 rounded-full transition-all duration-300" style="width: {{ $importProgress }}%"></div>
            </div>
        </div>
    </div>
    @endif
</div>
