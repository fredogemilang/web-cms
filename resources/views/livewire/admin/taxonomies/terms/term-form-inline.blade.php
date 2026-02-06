<div class="bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-200 dark:border-[#272B30] p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">
            {{ $isEdit ? 'Edit ' . $name : 'Add New ' . $taxonomy->singular_label }}
        </h2>
        @if($isEdit)
            <button wire:click="cancelEdit" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancel</button>
        @endif
    </div>
    
    <div class="space-y-4">
        <!-- Name -->
        <div class="space-y-1">
            <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Name <span class="text-red-500">*</span></label>
            <input wire:model.blur="name" type="text" placeholder="Term Name" 
                class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
            @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            <p class="text-[10px] text-[#6F767E]">The name is how it appears on your site.</p>
        </div>

        <!-- Slug -->
        <div class="space-y-1">
            <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Slug</label>
            <input wire:model.blur="slug" type="text" placeholder="URL Friendly Slug" 
                class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
            @error('slug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            <p class="text-[10px] text-[#6F767E]">The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
        </div>

        <!-- Parent -->
        @if($taxonomy->is_hierarchical)
            <div class="space-y-1">
                <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Parent {{ $taxonomy->singular_label }}</label>
                <select wire:model="parentId" class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
                    <option value="">(None)</option>
                    @foreach($possibleParents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-[#6F767E]">Assign a parent term to create a hierarchy. For example, a Jazz category might be a child of a Music category.</p>
            </div>
        @endif

        <!-- Description -->
        <div class="space-y-1">
            <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Description</label>
            <textarea wire:model="description" rows="3" placeholder="Short description..." 
                class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all resize-none"></textarea>
            @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            <p class="text-[10px] text-[#6F767E]">The description is not prominent by default; however, some themes may show it.</p>
        </div>

        <button wire:click="save" class="w-full py-2 rounded-lg text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
            <span wire:loading wire:target="save" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
            <span>{{ $isEdit ? 'Update Term' : 'Add New ' . $taxonomy->singular_label }}</span>
        </button>
    </div>
</div>
