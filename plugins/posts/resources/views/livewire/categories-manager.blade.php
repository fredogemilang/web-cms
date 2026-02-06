<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">Categories</h2>
            <p class="text-[#6F767E] mt-1">Organize your posts with categories</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Form -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
                <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-4">
                    {{ $editingCategory ? 'Edit Category' : 'Add New Category' }}
                </h3>
                
                <form wire:submit.prevent="{{ $editingCategory ? 'update' : 'store' }}" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="form-label">Name</label>
                        <input type="text" wire:model.live="name" class="form-input-field" placeholder="e.g. Technology">
                        <p class="mt-1 text-xs text-[#6F767E]">The name is how it appears on your site.</p>
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="form-label">Slug</label>
                        <input type="text" wire:model="slug" class="form-input-field" placeholder="e.g. technology">
                        <p class="mt-1 text-xs text-[#6F767E]">The "slug" is the URL-friendly version of the name.</p>
                        @error('slug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Parent -->
                    <div>
                        <label class="form-label">Parent Category</label>
                        <select wire:model="parent_id" class="form-input-field">
                            <option value="">None</option>
                            @foreach($parents as $parent)
                                @if(!$editingCategory || $parent->id !== $editingCategory->id)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-[#6F767E]">Categories can have a hierarchy.</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="form-label">Description</label>
                        <textarea wire:model="description" rows="4" class="form-input-field" placeholder="Description..."></textarea>
                        <p class="mt-1 text-xs text-[#6F767E]">The description is not prominent by default; however, some themes may show it.</p>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="submit" class="px-4 py-2 bg-[#2563EB] text-white rounded-lg font-semibold hover:bg-[#1D4ED8] transition-all text-sm">
                            {{ $editingCategory ? 'Update Category' : 'Add New Category' }}
                        </button>
                        @if($editingCategory)
                        <button type="button" wire:click="cancelEdit" class="px-4 py-2 bg-gray-100 dark:bg-[#272B30] text-[#6F767E] rounded-lg font-semibold hover:bg-gray-200 dark:hover:bg-[#374151] transition-all text-sm">
                            Cancel
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: List -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-200 dark:border-[#272B30] shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B]">
                            <th class="px-6 py-4 text-xs font-bold text-[#6F767E] uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-xs font-bold text-[#6F767E] uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-4 text-xs font-bold text-[#6F767E] uppercase tracking-wider text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-[#272B30]">
                        @forelse($categories as $category)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-[#272B30]/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2" style="padding-left: {{ ($category->depth ?? 0) * 1.5 }}rem">
                                    @if(($category->depth ?? 0) > 0)
                                        <span class="material-symbols-outlined text-gray-400 text-base shrink-0 select-none">subdirectory_arrow_right</span>
                                    @endif
                                    <div>
                                        <div class="font-bold text-[#111827] dark:text-[#FCFCFC] group-hover:text-blue-600 transition-colors">
                                            {{ $category->name }}
                                        </div>
                                        
                                        <!-- WP Style Hover Actions -->
                                        <div class="flex items-center gap-2 mt-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                            <button wire:click="edit({{ $category->id }})" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 uppercase tracking-tighter">Edit</button>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <button wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category?" class="text-[11px] font-bold text-red-500 hover:text-red-700 uppercase tracking-tighter">Delete</button>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <a href="#" class="text-[11px] font-bold text-[#6F767E] hover:text-gray-900 dark:hover:text-white uppercase tracking-tighter">View</a>
                                        </div>

                                        @if($category->description)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs mt-1">{{ $category->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-[#6F767E]">
                                {{ $category->slug }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-md bg-gray-100 dark:bg-[#272B30] font-bold text-xs">
                                    {{ $category->posts_count }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-[#6F767E]">
                                No categories found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-[#272B30]">
                    {{ $categories->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
