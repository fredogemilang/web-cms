<div>
    {{-- Toolbar --}}
    <div class="flex flex-col md:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#6F767E]">search</span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search domiciles by name or code..."
                class="h-12 w-full rounded-xl bg-white dark:bg-[#1A1A1A] pl-12 pr-4 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] ring-1 ring-gray-200 dark:ring-[#272B30] focus:ring-2 focus:ring-[#2563EB] focus:outline-none transition"
            />
        </div>

        <div class="w-full md:w-48 shrink-0">
            <select
                wire:model.live="filterType"
                class="h-12 w-full rounded-xl border border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#1A1A1A] px-4 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:outline-none"
            >
                <option value="">All Types</option>
                <option value="province">Provinces Only</option>
                <option value="regency">Regencies Only</option>
            </select>
        </div>

        <button
            wire:click="importFromApi"
            wire:loading.attr="disabled"
            wire:target="importFromApi"
            class="h-12 px-6 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2 bg-[#83BF6E] hover:bg-green-600 text-white shadow-lg shadow-green-500/20"
        >
            <span wire:loading.remove wire:target="importFromApi" class="material-symbols-outlined text-[20px]">cloud_download</span>
            <span wire:loading wire:target="importFromApi" class="material-symbols-outlined text-[20px] animate-spin">progress_activity</span>
            <span wire:loading.remove wire:target="importFromApi">Pull Data from API</span>
            <span wire:loading wire:target="importFromApi">Pulling Data...</span>
        </button>

        <button
            wire:click="add"
            @class([
                'h-12 px-6 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2 shrink-0',
                'bg-[#2563EB] text-white hover:bg-blue-700 shadow-lg shadow-blue-500/20' => $editingId !== 0,
                'bg-gray-200 dark:bg-[#272B30] text-[#6F767E] cursor-not-allowed' => $editingId === 0,
            ])
            @disabled($editingId === 0)
        >
            <span class="material-symbols-outlined text-[20px]">add</span>
            Add Domicile
        </button>
    </div>

    {{-- Inline form (new or edit) --}}
    @if($editingId !== null)
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] p-6 mb-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-[#111827] dark:text-[#FCFCFC]">
                    {{ $editingId === 0 ? 'Add Domicile Record' : 'Edit Domicile Record' }}
                </h3>
                <button wire:click="cancel" class="text-sm text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC]">
                    <span class="material-symbols-outlined align-middle text-[18px]">close</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Code</label>
                    <input
                        type="text"
                        wire:model="form.code"
                        placeholder="e.g. 11 for Province or 11.01 for Regency"
                        class="w-full rounded-xl border border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#0F1113] px-4 py-2.5 text-sm font-mono text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:outline-none"
                    />
                    @error('form.code')
                        <p class="text-xs text-[#FF6A55] mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Name</label>
                    <input
                        type="text"
                        wire:model="form.name"
                        placeholder="e.g. Jawa Barat or Kota Bandung"
                        class="w-full rounded-xl border border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#0F1113] px-4 py-2.5 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:outline-none"
                    />
                    @error('form.name')
                        <p class="text-xs text-[#FF6A55] mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Type</label>
                    <select
                        wire:model.live="form.type"
                        class="w-full rounded-xl border border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#0F1113] px-4 py-2.5 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:outline-none"
                    >
                        <option value="province">Province</option>
                        <option value="regency">Regency</option>
                    </select>
                </div>

                @if($form['type'] === 'regency')
                    <div class="md:col-span-12">
                        <label class="block text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Parent Province</label>
                        <select
                            wire:model="form.parent_code"
                            class="w-full rounded-xl border border-gray-300 dark:border-[#272B30] bg-white dark:bg-[#0F1113] px-4 py-2.5 text-sm text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB] focus:outline-none"
                        >
                            <option value="">Select Parent Province</option>
                            @foreach($provinces as $prov)
                                <option value="{{ $prov->code }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                        @error('form.parent_code')
                            <p class="text-xs text-[#FF6A55] mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button
                    type="button"
                    wire:click="cancel"
                    class="px-5 py-2.5 rounded-xl text-sm font-bold text-[#6F767E] hover:bg-gray-100 dark:hover:bg-[#272B30] transition"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="px-5 py-2.5 rounded-xl bg-[#2563EB] text-white text-sm font-bold hover:bg-blue-700 transition disabled:opacity-50 flex items-center gap-2"
                >
                    <span wire:loading.remove wire:target="save" class="material-symbols-outlined text-[18px]">save</span>
                    <span wire:loading wire:target="save" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                    {{ $editingId === 0 ? 'Create' : 'Update' }}
                </button>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-[#0F1113]">
                    <tr class="text-left">
                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider">Code</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider">Parent Province</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-[#272B30]">
                    @forelse($domiciles as $d)
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30]/30 transition-colors {{ $editingId === $d->id ? 'bg-blue-50 dark:bg-blue-500/10' : '' }}">
                            <td class="px-6 py-4 font-mono text-xs text-[#111827] dark:text-[#FCFCFC]">{{ $d->code }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-[#111827] dark:text-[#FCFCFC]">{{ $d->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($d->type === 'province')
                                    <span class="text-xs font-bold text-[#2563EB] bg-blue-100 dark:bg-[#2563EB]/15 px-2.5 py-1 rounded-full">Province</span>
                                @else
                                    <span class="text-xs font-bold text-[#83BF6E] bg-green-100 dark:bg-[#83BF6E]/15 px-2.5 py-1 rounded-full">Regency</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-[#6F767E]">
                                @if($d->type === 'regency' && $d->parent)
                                    {{ $d->parent->name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="edit({{ $d->id }})"
                                    class="p-2 rounded-lg text-[#6F767E] hover:text-[#2563EB] hover:bg-blue-500/10 transition"
                                    title="Edit"
                                >
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $d->id }})"
                                    wire:confirm="Are you sure you want to delete this domicile? Regencies under a deleted province will have their parent references removed."
                                    class="p-2 rounded-lg text-[#6F767E] hover:text-[#FF6A55] hover:bg-red-500/10 transition"
                                    title="Delete"
                                >
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-[40px] text-[#6F767E]">location_on</span>
                                    <p class="text-sm font-medium text-[#6F767E]">No domiciles registered yet.</p>
                                    <p class="text-xs text-[#6F767E]">Click "Pull Data from API" or "Add Domicile" to fill the list.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($domiciles->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-[#272B30]">
                {{ $domiciles->links() }}
            </div>
        @endif
    </div>
</div>
