<div>
    {{-- Step 1: Input URL --}}
    @if($step === 1)
    <div class="space-y-6">
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="h-12 w-12 rounded-2xl bg-[#2563EB]/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#2563EB] text-2xl">cloud_download</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Import from WordPress</h2>
                    <p class="text-sm text-[#6F767E]">Enter your WordPress site URL to import all posts</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#6F767E] mb-2">WordPress Site URL</label>
                    <div class="flex gap-3">
                        <input
                            wire:model="wpUrl"
                            type="url"
                            placeholder="https://yoursite.com or https://yoursite.com/wp-json/wp/v2/posts"
                            class="flex-1 h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all placeholder:text-[#6F767E]"
                        />
                        <button
                            wire:click="fetchPostsInfo"
                            wire:loading.attr="disabled"
                            class="h-12 px-6 rounded-xl bg-[#2563EB] text-white font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 disabled:opacity-50 flex items-center gap-2"
                        >
                            <span wire:loading.remove wire:target="fetchPostsInfo" class="material-symbols-outlined text-xl">search</span>
                            <svg wire:loading wire:target="fetchPostsInfo" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="fetchPostsInfo">Check Posts</span>
                            <span wire:loading wire:target="fetchPostsInfo">Checking...</span>
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

                <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-500 mt-0.5">info</span>
                        <div class="text-sm text-blue-600 dark:text-blue-400">
                            <p class="font-medium mb-1">How it works:</p>
                            <ul class="list-disc list-inside space-y-1 text-blue-500">
                                <li>Enter your WordPress site URL</li>
                                <li>We'll automatically fetch and import ALL posts</li>
                                <li>Images will be downloaded to your Media Library</li>
                                <li>Original publication dates are preserved for SEO</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Step 2: Configure & Import --}}
    @if($step === 2)
    <div class="space-y-6">
        {{-- Summary Card --}}
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-[#83BF6E]/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[#83BF6E] text-2xl">check_circle</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">WordPress Site Found</h2>
                        <p class="text-sm text-[#6F767E]">{{ $totalPosts }} posts ready to import ({{ $totalPages }} pages)</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-[#2563EB] text-sm font-bold">
                        {{ $totalPosts }} Posts
                    </span>
                </div>
            </div>

            {{-- Preview Posts --}}
            @if(count($previewPosts) > 0)
            <div class="border-t border-gray-100 dark:border-[#272B30] pt-4">
                <h4 class="text-sm font-bold text-[#6F767E] mb-3">Preview (first 5 posts):</h4>
                <div class="space-y-2">
                    @foreach($previewPosts as $post)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-[#0B0B0B]">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC] truncate">{{ $post['title'] }}</p>
                            <p class="text-xs text-[#6F767E]">{{ $post['slug'] }}</p>
                        </div>
                        <span class="text-xs text-[#6F767E] ml-4">{{ \Carbon\Carbon::parse($post['date'])->format('M d, Y') }}</span>
                    </div>
                    @endforeach
                    @if($totalPosts > 5)
                    <p class="text-xs text-[#6F767E] text-center py-2">... and {{ $totalPosts - 5 }} more posts</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Import Options --}}
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] p-6">
            <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-4">Import Options</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                @foreach(['title' => 'Title', 'slug' => 'Slug', 'content' => 'Content', 'excerpt' => 'Excerpt', 'published_at' => 'Original Date (SEO)', 'categories' => 'Categories', 'tags' => 'Tags'] as $field => $label)
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="fieldMappings.{{ $field }}" class="custom-checkbox" />
                    <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            
            {{-- Image Options --}}
            <div class="border-t border-gray-100 dark:border-[#272B30] pt-4">
                <h4 class="text-sm font-bold text-[#6F767E] mb-3">Image Options</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#6F767E] mb-2">Featured Image</label>
                        <select wire:model.live="fieldMappings.featured_image" class="w-full h-10 rounded-lg border-none bg-gray-50 dark:bg-[#0B0B0B] px-3 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB]">
                            <option value="download">Download to Media Library</option>
                            <option value="url">Keep as External URL</option>
                            <option value="skip">Skip featured images</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 cursor-pointer h-10">
                            <input type="checkbox" wire:model.live="fieldMappings.content_images" class="custom-checkbox" />
                            <span class="text-sm font-medium text-[#111827] dark:text-[#FCFCFC]">Download images in content</span>
                        </label>
                    </div>
                </div>
                <p class="text-xs text-[#6F767E] mt-2">Downloaded images will be saved to the Media Library.</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between">
            <button
                wire:click="resetMigration"
                class="h-12 px-6 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] font-bold text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition-all flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-xl">arrow_back</span>
                Back
            </button>
            <button
                wire:click="importAllPosts"
                wire:loading.attr="disabled"
                class="h-12 px-8 rounded-xl bg-[#2563EB] text-white font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 disabled:opacity-50 flex items-center gap-2"
            >
                <span wire:loading.remove wire:target="importAllPosts" class="material-symbols-outlined text-xl">cloud_download</span>
                <svg wire:loading wire:target="importAllPosts" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Import All {{ $totalPosts }} Posts
            </button>
        </div>
    </div>
    @endif

    {{-- Step 3: Import Results --}}
    @if($step === 3)
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
                <p class="text-[#6F767E] mb-8">Your WordPress posts have been imported.</p>

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
                        Skipped Posts ({{ count($importResults['skipped_posts']) }}):
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
                    <a
                        href="{{ route('admin.posts.index') }}"
                        wire:navigate
                        class="h-12 px-6 rounded-xl bg-[#2563EB] text-white font-bold text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined text-xl">article</span>
                        View All Posts
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Loading Overlay (for import) --}}
    @if($isLoading && $step === 2)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 dark:bg-[#0B0B0B]/80 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl p-8 shadow-2xl text-center max-w-sm w-full mx-4">
            <div class="mb-6">
                <svg class="animate-spin h-12 w-12 text-[#2563EB] mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">Importing Posts...</h3>
            <p class="text-sm text-[#6F767E] mb-2">Processing page {{ $currentPageImporting }} of {{ $totalPages }}</p>
            <p class="text-xs text-[#6F767E] mb-4">Please wait while we download and import your posts.</p>
            <div class="w-full bg-gray-100 dark:bg-[#272B30] rounded-full h-2.5">
                <div class="bg-[#2563EB] h-2.5 rounded-full transition-all duration-300" style="width: {{ $importProgress }}%"></div>
            </div>
            <p class="text-sm font-bold text-[#2563EB] mt-2">{{ $importProgress }}%</p>
        </div>
    </div>
    @endif
</div>
