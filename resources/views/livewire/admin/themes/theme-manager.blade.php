<div class="space-y-6">
    {{-- Upload Section --}}
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-6 border border-gray-200 dark:border-[#272B30]">
        <h3 class="text-lg font-semibold text-[#111827] dark:text-[#FCFCFC] mb-4">Upload New Theme</h3>

        <form wire:submit.prevent="uploadTheme" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">
                    Theme ZIP File
                </label>
                <input
                    type="file"
                    wire:model="themeZip"
                    accept=".zip"
                    class="block w-full text-sm text-[#111827] dark:text-[#FCFCFC]
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-lg file:border-0
                           file:text-sm file:font-semibold
                           file:bg-[#2563EB] file:text-white
                           hover:file:bg-[#1E40AF]
                           cursor-pointer
                           border border-gray-200 dark:border-[#272B30] rounded-lg
                           bg-white dark:bg-[#0B0B0B]"
                >
                @error('themeZip')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            @if ($themeZip)
                <div class="text-sm text-[#6F767E]">
                    Selected: {{ $themeZip->getClientOriginalName() }}
                </div>
            @endif

            <div>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="themeZip,uploadTheme"
                    class="inline-flex items-center px-4 py-2 bg-[#2563EB] text-white rounded-lg
                           hover:bg-[#1E40AF] transition-colors duration-200
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="uploadTheme">
                        <span class="material-symbols-outlined text-sm mr-2">upload</span>
                        Upload Theme
                    </span>
                    <span wire:loading wire:target="uploadTheme">
                        <span class="material-symbols-outlined text-sm mr-2 animate-spin">progress_activity</span>
                        Installing...
                    </span>
                </button>
            </div>
        </form>
    </div>

    {{-- Themes Grid --}}
    <div>
        <h3 class="text-lg font-semibold text-[#111827] dark:text-[#FCFCFC] mb-4">Installed Themes</h3>

        @if ($themes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($themes as $theme)
                    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] overflow-hidden
                                {{ $theme->is_active ? 'ring-2 ring-[#2563EB]' : '' }}">
                        {{-- Theme Screenshot --}}
                        <div class="aspect-video bg-gradient-to-br from-[#2563EB] to-[#1E40AF] relative">
                            @if ($theme->screenshot_url)
                                <img src="{{ $theme->screenshot_url }}" alt="{{ $theme->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center h-full">
                                    <span class="material-symbols-outlined text-white text-6xl opacity-50">palette</span>
                                </div>
                            @endif

                            {{-- Active Badge --}}
                            @if ($theme->is_active)
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#83BF6E] text-white">
                                        <span class="material-symbols-outlined text-sm mr-1">check_circle</span>
                                        Active
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Theme Info --}}
                        <div class="p-5">
                            <h4 class="text-lg font-semibold text-[#111827] dark:text-[#FCFCFC] mb-1">
                                {{ $theme->name }}
                            </h4>

                            @if ($theme->description)
                                <p class="text-sm text-[#6F767E] mb-3 line-clamp-2">
                                    {{ $theme->description }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-[#6F767E] mb-4">
                                <span>Version {{ $theme->version }}</span>
                                @if ($theme->author)
                                    <span>by {{ $theme->author }}</span>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2">
                                @if (!$theme->is_active)
                                    <button
                                        wire:click="activateTheme({{ $theme->id }})"
                                        wire:loading.attr="disabled"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-[#2563EB] text-white rounded-lg
                                               hover:bg-[#1E40AF] transition-colors duration-200 text-sm font-medium
                                               disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="activateTheme({{ $theme->id }})">
                                            Activate
                                        </span>
                                        <span wire:loading wire:target="activateTheme({{ $theme->id }})">
                                            Activating...
                                        </span>
                                    </button>

                                    <button
                                        wire:click="confirmDelete({{ $theme->id }})"
                                        class="inline-flex items-center justify-center p-2 border border-gray-200 dark:border-[#272B30]
                                               text-[#FF6A55] rounded-lg hover:bg-[#FF6A55] hover:text-white hover:border-[#FF6A55]
                                               transition-colors duration-200">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                @else
                                    <div class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-[#83BF6E] text-white rounded-lg text-sm font-medium cursor-not-allowed">
                                        <span class="material-symbols-outlined text-sm mr-2">check</span>
                                        Currently Active
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] p-12 text-center border border-gray-200 dark:border-[#272B30]">
                <span class="material-symbols-outlined text-6xl text-[#6F767E] mb-4">palette</span>
                <h3 class="text-lg font-semibold text-[#111827] dark:text-[#FCFCFC] mb-2">No Themes Installed</h3>
                <p class="text-[#6F767E]">Upload a theme ZIP file to get started.</p>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal && $themeToDelete)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="cancelDelete">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl p-6 max-w-md w-full mx-4 border border-gray-200 dark:border-[#272B30]"
                 wire:click.stop
                 x-data
                 x-init="$el.focus()"
                 @keydown.escape.window="$wire.cancelDelete()">
                <h3 class="text-xl font-semibold text-[#111827] dark:text-[#FCFCFC] mb-4">Delete Theme</h3>

                <p class="text-[#6F767E] mb-6">
                    Are you sure you want to delete the theme <strong>{{ $themeToDelete->name }}</strong>?
                    This action cannot be undone and all theme files will be permanently removed.
                </p>

                <div class="flex items-center justify-end gap-3">
                    <button
                        wire:click="cancelDelete"
                        class="px-4 py-2 border border-gray-200 dark:border-[#272B30] text-[#111827] dark:text-[#FCFCFC]
                               rounded-lg hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors duration-200">
                        Cancel
                    </button>

                    <button
                        wire:click="deleteTheme"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-[#FF6A55] text-white rounded-lg hover:bg-[#FF5544]
                               transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="deleteTheme">Delete Theme</span>
                        <span wire:loading wire:target="deleteTheme">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
