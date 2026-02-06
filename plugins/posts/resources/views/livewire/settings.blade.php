<div class="flex flex-col h-full overflow-hidden" 
     x-data="{ 
        activeTab: 'general',
        titles: {
            'general': 'General Settings',
            'comments': 'Comments',
            'feed': 'Feed & RSS'
        },
        descriptions: {
            'general': 'Manage configuration for your blog posts.',
            'comments': 'Manage user interaction policies.',
            'feed': 'Configure your syndication feeds.'
        }
     }">
    <!-- Header -->
    <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 md:px-10 md:pt-8 md:pb-6 bg-[#F4F5F6]/95 dark:bg-[#0B0B0B]/95 backdrop-blur-sm sticky top-0 z-30">
        <div>
            <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]" x-text="titles[activeTab]"></h1>
            <p class="text-xs text-[#6F767E] mt-1" x-text="descriptions[activeTab]"></p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="save" wire:loading.attr="disabled"
                class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                <span wire:loading wire:target="save" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                <span>Save Changes</span>
            </button>
        </div>
    </header>

    <!-- Content -->
    <div class="flex-1 overflow-hidden">
        <div class="flex h-full">
            
            <!-- Left Sidebar Navigation -->
            <aside class="w-64 flex-shrink-0 border-r border-gray-200 dark:border-[#272B30] overflow-y-auto bg-[#F4F5F6]/50 dark:bg-[#0B0B0B]/50 hidden md:block pt-6">
                <nav class="space-y-1 px-4">
                    <button @click="activeTab = 'general'"
                            :class="{ 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-black/5 dark:ring-white/5': activeTab === 'general', 'text-[#6F767E] hover:text-[#111827] dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#1A1A1A]/50': activeTab !== 'general' }"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold rounded-lg transition-all text-left">
                        <span class="material-symbols-outlined text-[20px]" :class="{ 'text-[#2563EB]': activeTab === 'general' }">tune</span>
                        General
                    </button>
                    <button @click="activeTab = 'comments'"
                            :class="{ 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-black/5 dark:ring-white/5': activeTab === 'comments', 'text-[#6F767E] hover:text-[#111827] dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#1A1A1A]/50': activeTab !== 'comments' }"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold rounded-lg transition-all text-left">
                        <span class="material-symbols-outlined text-[20px]" :class="{ 'text-[#2563EB]': activeTab === 'comments' }">chat</span>
                        Comments
                    </button>
                    <button @click="activeTab = 'feed'"
                            :class="{ 'bg-white dark:bg-[#1A1A1A] text-[#2563EB] shadow-sm ring-1 ring-black/5 dark:ring-white/5': activeTab === 'feed', 'text-[#6F767E] hover:text-[#111827] dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#1A1A1A]/50': activeTab !== 'feed' }"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold rounded-lg transition-all text-left">
                        <span class="material-symbols-outlined text-[20px]" :class="{ 'text-[#2563EB]': activeTab === 'feed' }">rss_feed</span>
                        Feed / RSS
                    </button>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar bg-white dark:bg-[#111111]">
                <div class="max-w-3xl mx-auto space-y-10">
                    
                    <!-- General Settings -->
                    <div x-show="activeTab === 'general'" class="space-y-8 animate-fade-in">
                        <div>
                            <h2 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">General Settings</h2>
                            <p class="text-sm text-[#6F767E]">Configure how your posts content is displayed and accessed.</p>
                        </div>
                        
                        <div class="space-y-6">
                            <!-- Posts Per Page -->
                            <div class="flex flex-col md:flex-row md:items-start gap-4 md:gap-8 pb-6 border-b border-gray-100 dark:border-[#272B30]">
                                <div class="md:w-1/3">
                                    <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Posts Per Page</label>
                                    <p class="text-xs text-[#6F767E] mt-1">Total items per page on the archive.</p>
                                </div>
                                <div class="md:w-2/3">
                                    <input type="number" wire:model="posts_per_page" class="w-24 px-3 py-2 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-lg text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition-all">
                                    @error('posts_per_page') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Archive Slug -->
                            <div class="flex flex-col md:flex-row md:items-start gap-4 md:gap-8 pb-6 border-b border-gray-100 dark:border-[#272B30]">
                                <div class="md:w-1/3">
                                    <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Archive Slug</label>
                                    <p class="text-xs text-[#6F767E] mt-1">Base URL for your blog section.</p>
                                </div>
                                <div class="md:w-2/3">
                                    <div class="flex items-center">
                                        <span class="px-3 py-2 bg-gray-50 dark:bg-[#272B30] border border-r-0 border-gray-200 dark:border-[#272B30] rounded-l-lg text-sm text-[#6F767E]">{{ url('/') }}/</span>
                                        <input type="text" wire:model="archive_slug" class="flex-1 px-3 py-2 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-r-lg text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition-all">
                                    </div>
                                    @error('archive_slug') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Date Format -->
                            <div class="flex flex-col md:flex-row md:items-start gap-4 md:gap-8">
                                <div class="md:w-1/3">
                                    <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Date Format</label>
                                    <p class="text-xs text-[#6F767E] mt-1">Display format for dates.</p>
                                </div>
                                <div class="md:w-2/3">
                                    <input type="text" wire:model="date_format" class="w-full md:w-2/3 px-3 py-2 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-lg text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition-all">
                                    <p class="mt-2 text-xs text-[#6F767E]">Uses standard PHP date formatting (e.g. <span class="font-mono bg-gray-100 dark:bg-[#272B30] px-1 rounded">M d, Y</span>)</p>
                                    @error('date_format') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comments Settings -->
                    <div x-show="activeTab === 'comments'" class="space-y-8 animate-fade-in" x-cloak>
                         <div>
                            <h2 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Comments</h2>
                            <p class="text-sm text-[#6F767E]">Manage user interaction policies.</p>
                        </div>

                         <div class="space-y-6">
                            <!-- Enable Comments -->
                            <div class="flex items-center justify-between pb-6 border-b border-gray-100 dark:border-[#272B30]">
                                <div>
                                    <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Enable Comments</label>
                                    <p class="text-xs text-[#6F767E] mt-1">Allow visitors to leave comments on your posts.</p>
                                </div>
                                <button type="button" 
                                        wire:click="$toggle('enable_comments')"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2"
                                        :class="{ 'bg-[#2563EB]': @js($enable_comments), 'bg-gray-200 dark:bg-[#272B30]': !@js($enable_comments) }">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="{ 'translate-x-5': @js($enable_comments), 'translate-x-0': !@js($enable_comments) }"></span>
                                </button>
                            </div>

                            <!-- Comment Moderation -->
                            <div class="flex items-center justify-between pb-6 border-b border-gray-100 dark:border-[#272B30]">
                                <div>
                                    <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Moderation</label>
                                    <p class="text-xs text-[#6F767E] mt-1">Require manual approval for all new comments.</p>
                                </div>
                                <button type="button" 
                                        wire:click="$toggle('comment_moderation')"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2"
                                        :class="{ 'bg-[#2563EB]': @js($comment_moderation), 'bg-gray-200 dark:bg-[#272B30]': !@js($comment_moderation) }">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="{ 'translate-x-5': @js($comment_moderation), 'translate-x-0': !@js($comment_moderation) }"></span>
                                </button>
                            </div>

                            <!-- Auto Close -->
                            <div class="flex flex-col md:flex-row md:items-start gap-4 md:gap-8">
                                <div class="md:w-1/3">
                                    <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Auto-close Comments</label>
                                    <p class="text-xs text-[#6F767E] mt-1">Automatically close older posts.</p>
                                </div>
                                <div class="md:w-2/3">
                                    <div class="flex items-center gap-3">
                                        <input type="number" wire:model="close_comments_days" class="w-24 px-3 py-2 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-lg text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition-all">
                                        <span class="text-sm text-[#6F767E]">days after publishing</span>
                                    </div>
                                    <p class="mt-2 text-xs text-[#6F767E]">Set to <span class="font-bold">0</span> to never close comments.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feed Settings -->
                    <div x-show="activeTab === 'feed'" class="space-y-8 animate-fade-in" x-cloak>
                        <div>
                            <h2 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Feed & RSS</h2>
                            <p class="text-sm text-[#6F767E]">Configure your syndication feeds.</p>
                        </div>

                         <div class="space-y-6">
                            <!-- RSS Full Text -->
                            <div class="flex items-center justify-between pb-6 border-b border-gray-100 dark:border-[#272B30]">
                                <div>
                                    <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Include Content</label>
                                    <p class="text-xs text-[#6F767E] mt-1">Include the full text of the post in RSS feeds.</p>
                                </div>
                                <button type="button" 
                                        wire:click="$toggle('rss_full_text')"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2"
                                        :class="{ 'bg-[#2563EB]': @js($rss_full_text), 'bg-gray-200 dark:bg-[#272B30]': !@js($rss_full_text) }">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="{ 'translate-x-5': @js($rss_full_text), 'translate-x-0': !@js($rss_full_text) }"></span>
                                </button>
                            </div>

                            <!-- RSS Items -->
                            <div class="flex flex-col md:flex-row md:items-start gap-4 md:gap-8">
                                <div class="md:w-1/3">
                                    <label class="block text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">Feed Size</label>
                                    <p class="text-xs text-[#6F767E] mt-1">Number of items to show.</p>
                                </div>
                                <div class="md:w-2/3">
                                    <input type="number" wire:model="rss_items" class="w-24 px-3 py-2 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] rounded-lg text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:border-transparent transition-all">
                                    <p class="mt-2 text-xs text-[#6F767E]">Most readers define this, but 10-20 is standard.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
</div>
