<div>
    <!-- Add/Edit Category Form -->
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden p-6 mb-6">
        <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-4">
            {{ $editingCategory ? 'Edit Category' : 'Add New Category' }}
        </h3>
        
        <form wire:submit.prevent="{{ $editingCategory ? 'update' : 'store' }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Name</label>
                    <input wire:model.live="name" type="text" 
                        class="w-full h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all"
                        placeholder="Category name" required>
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Slug</label>
                    <input wire:model="slug" type="text" 
                        class="w-full h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all"
                        placeholder="category-slug" required>
                    @error('slug') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Color</label>
                    <input wire:model="color" type="color" 
                        class="w-full h-12 rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Category Image</label>
                    <div class="relative">
                        <input wire:model="image" type="file" accept="image/*" id="category-image"
                            class="hidden">
                        <label for="category-image" 
                            class="flex items-center justify-center h-12 rounded-xl border-2 border-dashed border-gray-300 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] cursor-pointer hover:border-[#2563EB] transition-all">
                            <span class="material-symbols-outlined text-[#6F767E] mr-2">upload</span>
                            <span class="text-sm font-medium text-[#6F767E]">Choose Image</span>
                        </label>
                    </div>
                    @error('image') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    
                    <!-- Image Preview -->
                    @if($image)
                        <div class="mt-3 relative inline-block">
                            <img src="{{ $image->temporaryUrl() }}" class="h-24 w-24 rounded-xl object-cover border-2 border-gray-200 dark:border-[#272B30]">
                            <button type="button" wire:click="$set('image', null)" 
                                class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition-all">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                    @elseif($editingCategory && $editingCategory->image)
                        <div class="mt-3 relative inline-block">
                            <img src="{{ $editingCategory->image->url }}" class="h-24 w-24 rounded-xl object-cover border-2 border-gray-200 dark:border-[#272B30]">
                            <span class="absolute top-1 right-1 px-2 py-0.5 rounded-lg bg-blue-500 text-white text-[10px] font-bold">Current</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-[#111827] dark:text-[#FCFCFC] mb-2">Description</label>
                <textarea wire:model="description" rows="3"
                    class="w-full rounded-xl border-none bg-gray-50 dark:bg-[#0B0B0B] px-4 py-3 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] transition-all"
                    placeholder="Category description"></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" 
                    class="h-12 px-6 rounded-xl bg-[#2563EB] text-white text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                    {{ $editingCategory ? 'Update Category' : 'Add Category' }}
                </button>
                
                @if($editingCategory)
                <button type="button" wire:click="cancelEdit"
                    class="h-12 px-6 rounded-xl bg-gray-100 dark:bg-[#272B30] text-gray-700 dark:text-[#FCFCFC] text-sm font-bold hover:bg-gray-200 dark:hover:bg-[#33383f] transition-all">
                    Cancel
                </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                        <th class="px-8 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Category</th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Slug</th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Color</th>
                        <th class="px-4 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest">Events</th>
                        <th class="px-8 py-6 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                @if($category->image)
                                    <img src="{{ $category->image->url }}" alt="{{ $category->name }}" 
                                        class="h-12 w-12 rounded-xl object-cover ring-2 ring-gray-200 dark:ring-[#272B30]">
                                @else
                                    <div class="h-12 w-12 rounded-xl flex items-center justify-center" style="background-color: {{ $category->color }}20;">
                                        <span class="material-symbols-outlined text-xl" style="color: {{ $category->color }};">category</span>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $category->name }}</span>
                                    @if($category->description)
                                        <p class="text-xs text-[#6F767E] line-clamp-1">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-5">
                            <span class="text-sm text-[#6F767E]">{{ $category->slug }}</span>
                        </td>
                        <td class="px-4 py-5">
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-lg ring-1 ring-gray-200 dark:ring-[#272B30]" style="background-color: {{ $category->color }};"></div>
                                <span class="text-xs text-[#6F767E]">{{ $category->color }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-5">
                            <span class="inline-flex items-center rounded-lg bg-blue-100 dark:bg-blue-900/30 px-2.5 py-1 text-xs font-bold text-blue-600 dark:text-blue-400">
                                {{ $category->events_count }} events
                            </span>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex gap-2 items-center justify-end">
                                <button wire:click="edit({{ $category->id }})"
                                    class="w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#2563EB] transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                
                                <button 
                                    x-data
                                    @click="if(confirm('Are you sure you want to delete this category?')) $wire.delete({{ $category->id }})"
                                    class="w-9 h-9 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-[#272B30] text-[#6F767E] hover:text-[#FF6A55] transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-16 w-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mb-4">
                                    <span class="material-symbols-outlined text-3xl text-[#6F767E]">category</span>
                                </div>
                                <p class="text-[#6F767E] font-medium">No categories yet. Create your first category!</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($categories->hasPages())
        <div class="px-8 py-6 border-t border-gray-100 dark:border-[#272B30]">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>
