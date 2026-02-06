<div class="flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-6 py-6 border-b border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A]">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.taxonomies.terms.index', $taxonomy->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl border border-gray-200 dark:border-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-all">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">
                    {{ $isEdit ? 'Edit' : 'Add New' }} Term
                </h1>
                <div class="text-xs text-[#6F767E] mt-0.5">
                    {{ $taxonomy->singular_label }}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="save" class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-600 shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                <span wire:loading wire:target="save" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                <span>{{ $isEdit ? 'Update Term' : 'Create Term' }}</span>
            </button>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="flex-1 overflow-y-auto p-6 md:p-10 no-scrollbar">
        <div class="max-w-3xl mx-auto space-y-6">
            
            <!-- Basic Info -->
            <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-200 dark:border-[#272B30] overflow-hidden p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Name <span class="text-red-500">*</span></label>
                        <input wire:model.blur="name" type="text" placeholder="Term Name" 
                            class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Slug -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Slug <span class="text-red-500">*</span></label>
                        <input wire:model.blur="slug" type="text" placeholder="URL Friendly Slug" 
                            class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
                        @error('slug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Description</label>
                    <textarea wire:model="description" rows="3" placeholder="Short description..." 
                        class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all resize-none"></textarea>
                    @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Hierarchy -->
            @if($taxonomy->is_hierarchical)
                <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-200 dark:border-[#272B30] overflow-hidden p-6 space-y-4">
                     <h3 class="text-sm font-bold text-[#6F767E] uppercase tracking-widest">Hierarchy & Order</h3>
                     
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Parent -->
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Parent Term</label>
                            <select wire:model="parentId" class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
                                <option value="">(None)</option>
                                @foreach($possibleParents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Order -->
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-[#6F767E]">Order</label>
                            <input wire:model="order" type="number" min="0" class="w-full rounded-xl border-gray-200 dark:border-[#272B30] bg-gray-50 dark:bg-[#0B0B0B] py-2 px-3 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all">
                        </div>
                     </div>
                </div>
            @endif
        </div>
    </div>
</div>
