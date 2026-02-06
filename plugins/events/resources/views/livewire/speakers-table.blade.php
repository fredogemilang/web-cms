<div>
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center mb-6">
        <div class="w-full md:w-72 relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
            <input wire:model.live.debounce.300ms="search" type="text" 
                class="w-full pl-10 h-10 rounded-xl border-gray-200 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] text-sm focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Search speakers...">
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('admin.events.migration.wordpress.speakers') }}" class="flex items-center gap-2 px-4 py-2 bg-purple-500 text-white rounded-xl text-sm font-bold hover:bg-purple-600 transition-all shadow-lg shadow-purple-500/20">
                <span class="material-symbols-outlined text-lg">cloud_download</span>
                Import
            </a>
            <a href="#" class="flex items-center gap-2 px-4 py-2 bg-[#2563EB] text-white rounded-xl text-sm font-bold hover:bg-blue-600 transition-all shadow-lg shadow-blue-500/20">
                <span class="material-symbols-outlined text-lg">add</span>
                Add Speaker
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-200 dark:border-[#272B30] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-[#272B30] bg-gray-50/50 dark:bg-[#0B0B0B]">
                        <th class="p-4 text-xs font-bold text-[#6F767E] uppercase tracking-wider w-16">Image</th>
                        <th class="p-4 text-xs font-bold text-[#6F767E] uppercase tracking-wider cursor-pointer hover:text-indigo-600" wire:click="sortBy('name')">Name</th>
                        <th class="p-4 text-xs font-bold text-[#6F767E] uppercase tracking-wider">Title / Company</th>
                        <th class="p-4 text-xs font-bold text-[#6F767E] uppercase tracking-wider text-center">Active</th>
                        <th class="p-4 text-xs font-bold text-[#6F767E] uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#272B30]">
                    @forelse($speakers as $speaker)
                    <tr class="group hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                        <td class="p-4">
                            @if($speaker->photo_id)
                                <img src="/storage/{{ $speaker->photo->path }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-[#272B30]">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center text-gray-500 text-xs font-bold border border-gray-200 dark:border-[#272B30]">
                                    {{ substr($speaker->name, 0, 2) }}
                                </div>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $speaker->name }}</div>
                            <div class="text-xs text-[#6F767E]">{{ $speaker->email }}</div>
                        </td>
                        <td class="p-4 text-sm text-[#111827] dark:text-[#FCFCFC]">
                            <div class="font-medium">{{ $speaker->title ?: '-' }}</div>
                            <div class="text-xs text-[#6F767E]">{{ $speaker->company ?: 'No Company' }}</div>
                        </td>
                        <td class="p-4 text-center">
                            <button wire:click="toggleStatus({{ $speaker->id }})" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $speaker->is_active ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $speaker->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </td>
                        <td class="p-4 text-right">
                             <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <button wire:click="delete({{ $speaker->id }})" wire:confirm="Are you sure?" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-[#6F767E]">
                            <div class="mb-2"><span class="material-symbols-outlined text-4xl text-gray-300">person_off</span></div>
                            No speakers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-100 dark:border-[#272B30]">
            {{ $speakers->links() }}
        </div>
    </div>
</div>
