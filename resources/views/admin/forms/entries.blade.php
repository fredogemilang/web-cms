@extends('layouts.admin')

@section('title', 'Form Entries')
@section('page-title')
    <div class="flex flex-col">
        <span class="text-xs font-bold text-[#6F767E] uppercase tracking-widest mb-1">Form Entries</span>
        <span class="text-[#111827] dark:text-white">{{ $form->name }}</span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Header Actions --}}
        <div class="flex flex-wrap justify-between items-center gap-4">
            <a href="{{ route('admin.forms.index') }}" 
                class="flex items-center gap-2 text-sm font-bold text-[#6F767E] hover:text-[#111827] dark:hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                Back to Forms
            </a>

            <div class="flex items-center gap-3">
                {{-- Export Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" 
                        class="flex items-center gap-2 h-10 px-4 rounded-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] text-sm font-bold text-[#111827] dark:text-white hover:bg-gray-50 dark:hover:bg-[#272B30]/80 transition-all">
                        <span class="material-symbols-outlined text-[20px]">download</span>
                        Export
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </button>
                    
                    <div x-show="open" x-transition.origin.top.right
                        class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-[#1A1A1A] rounded-xl shadow-lg border border-gray-200 dark:border-[#272B30] overflow-hidden z-50">
                        <a href="{{ route('admin.forms.export', $form) }}?format=csv" class="block px-4 py-2.5 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#272B30]">Export CSV</a>
                        <a href="{{ route('admin.forms.export', $form) }}?format=xlsx" class="block px-4 py-2.5 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#272B30]">Export Excel</a>
                        <a href="{{ route('admin.forms.export', $form) }}?format=pdf" class="block px-4 py-2.5 text-sm font-medium text-[#111827] dark:text-[#FCFCFC] hover:bg-gray-50 dark:hover:bg-[#272B30]">Export PDF</a>
                    </div>
                </div>

                <a href="{{ route('admin.forms.edit', $form) }}" 
                    class="flex items-center gap-2 h-10 px-4 rounded-xl bg-[#2563EB] text-white text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                    <span class="material-symbols-outlined text-[20px]">edit</span>
                    Edit Form
                </a>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl p-5 border border-gray-200 dark:border-[#272B30] relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-6xl text-blue-500">inbox</span>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-[#6F767E] uppercase tracking-widest mb-1">Total</p>
                    <h3 class="text-2xl font-bold text-[#111827] dark:text-white">{{ $stats['total'] }}</h3>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl p-5 border border-gray-200 dark:border-[#272B30] relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-6xl text-green-500">today</span>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-[#6F767E] uppercase tracking-widest mb-1">Today</p>
                    <h3 class="text-2xl font-bold text-[#111827] dark:text-white">{{ $stats['today'] }}</h3>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl p-5 border border-gray-200 dark:border-[#272B30] relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-6xl text-yellow-500">date_range</span>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-[#6F767E] uppercase tracking-widest mb-1">This Week</p>
                    <h3 class="text-2xl font-bold text-[#111827] dark:text-white">{{ $stats['this_week'] }}</h3>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl p-5 border border-gray-200 dark:border-[#272B30] relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-6xl text-purple-500">calendar_month</span>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-[#6F767E] uppercase tracking-widest mb-1">This Month</p>
                    <h3 class="text-2xl font-bold text-[#111827] dark:text-white">{{ $stats['this_month'] }}</h3>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-200 dark:border-[#272B30] p-5">
            <form method="GET" action="{{ route('admin.forms.entries', $form) }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">Search</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-[#6F767E]">search</span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="w-full h-10 pl-10 pr-4 rounded-xl bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#111827] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]"
                            placeholder="Search in entries...">
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full h-10 px-4 rounded-xl bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#6F767E] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                </div>
                <div class="md:col-span-3">
                    <label class="text-xs font-bold text-[#111827] dark:text-[#FCFCFC] mb-2 block">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full h-10 px-4 rounded-xl bg-[#F4F5F6] dark:bg-[#0B0B0B] border-none text-sm font-medium text-[#6F767E] dark:text-[#FCFCFC] focus:ring-2 focus:ring-[#2563EB]">
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 h-10 rounded-xl bg-[#2563EB] text-white text-sm font-bold hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'date_from', 'date_to']))
                    <a href="{{ route('admin.forms.entries', $form) }}" 
                        class="h-10 w-10 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] hover:text-red-500 transition-all flex items-center justify-center"
                        title="Clear filters">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Entries Table --}}
        <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden">
            @if($entries->isEmpty())
                <div class="text-center py-16">
                    <div class="h-20 w-20 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-4xl text-[#6F767E]">inbox</span>
                    </div>
                    @if(request()->hasAny(['search', 'date_from', 'date_to']))
                        <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-1">No entries found</h3>
                        <p class="text-[#6F767E]">Try adjusting your search criteria</p>
                    @else
                        <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] mb-1">No submissions yet</h3>
                        <p class="text-[#6F767E] mb-4">Share your form to start collecting data</p>
                        <div class="flex items-center justify-center gap-2 bg-gray-50 dark:bg-[#272B30] py-2 px-4 rounded-lg inline-flex">
                            <span class="text-sm font-mono text-[#6F767E]">{{ url('/forms/' . $form->slug) }}</span>
                            <button class="text-[#2563EB] hover:text-blue-700" onclick="navigator.clipboard.writeText('{{ url('/forms/' . $form->slug) }}')">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-[#0B0B0B]/20 border-b border-gray-100 dark:border-[#272B30]">
                                <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest w-20">ID</th>
                                @foreach($form->fields->take(4) as $field)
                                    @if(!in_array($field->type, ['section', 'divider', 'html']))
                                    <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest whitespace-nowrap">{{ Str::limit($field->label, 20) }}</th>
                                    @endif
                                @endforeach
                                <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest whitespace-nowrap">Submitted</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-[#6F767E] uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#272B30]/30 text-sm">
                            @foreach($entries as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#272B30] transition-colors">
                                <td class="px-6 py-4 font-mono font-medium text-[#6F767E]">
                                    #{{ $entry->id }}
                                </td>
                                @foreach($form->fields->take(4) as $field)
                                    @if(!in_array($field->type, ['section', 'divider', 'html']))
                                    <td class="px-6 py-4 text-[#111827] dark:text-[#FCFCFC]">
                                        @php
                                            $value = $entry->getFieldValue($field->field_id);
                                            // Handle arrays (checkboxes/multiple uploads)
                                            if (is_array($value)) {
                                                $displayValue = implode(', ', $value);
                                            } else {
                                                $displayValue = $value;
                                            }
                                            
                                            // Handle file uploads - showing generic file link/icon
                                            if (in_array($field->type, ['file', 'image']) && $value) {
                                                $isImage = $field->type === 'image';
                                                $displayValue = '<span class="inline-flex items-center gap-1 text-xs bg-gray-100 dark:bg-[#272B30] px-2 py-1 rounded text-[#2563EB]"><span class="material-symbols-outlined text-[14px]">'.($isImage ? 'image' : 'description').'</span> File</span>';
                                            }
                                        @endphp
                                        
                                        @if(in_array($field->type, ['file', 'image']) && $value)
                                            {!! $displayValue !!}
                                        @else
                                            <span class="line-clamp-1 block max-w-xs">{{ $displayValue }}</span>
                                        @endif
                                    </td>
                                    @endif
                                @endforeach
                                <td class="px-6 py-4 whitespace-nowrap text-[#6F767E]">
                                    {{ $entry->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="viewEntry({{ $entry->id }})" 
                                        class="text-[#2563EB] hover:text-blue-700 font-bold text-xs">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-100 dark:border-[#272B30]">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
    
    {{-- Entry Detail Modal --}}
    <div id="entryModal" class="fixed inset-0 z-50 hidden" x-data x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEntryModal()"></div>
        <div class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white dark:bg-[#1A1A1A] shadow-2xl overflow-hidden transform transition-transform duration-300 translate-x-full" id="entryModalContent">
            {{-- Modal Header --}}
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-[#272B30]">
                <div>
                    <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Entry Details</h3>
                    <p class="text-sm text-[#6F767E]" id="entryId"></p>
                </div>
                <button onclick="closeEntryModal()" class="h-10 w-10 rounded-xl bg-gray-100 dark:bg-[#272B30] text-[#6F767E] hover:text-[#111827] dark:hover:text-white flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            {{-- Modal Content --}}
            <div id="entryContent" class="p-6 overflow-y-auto" style="max-height: calc(100vh - 82px);">
                {{-- Content loaded via JS --}}
            </div>
        </div>
    </div>

    @php
        $entriesDataForModal = $entries->keyBy('id')->map(function($entry) use ($form) {
            $data = [];
            foreach ($form->fields as $field) {
                if (!in_array($field->type, ['section', 'divider', 'html'])) {
                    $value = $entry->getFieldValue($field->field_id);
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $data[] = [
                        'label' => $field->label,
                        'value' => $value ?? '-',
                        'type' => $field->type
                    ];
                }
            }
            return [
                'id' => $entry->id,
                'submitted_at' => $entry->created_at->format('M d, Y \a\t H:i'),
                'ip_address' => $entry->ip_address,
                'fields' => $data
            ];
        });
    @endphp

    <script>
        // Store entries data for modal
        const entriesData = @json($entriesDataForModal);

        function viewEntry(id) {
            const entry = entriesData[id];
            if (!entry) return;

            // Update header
            document.getElementById('entryId').textContent = '#' + entry.id + ' • ' + entry.submitted_at;

            // Build content
            let html = '<div class="space-y-4">';
            
            entry.fields.forEach(field => {
                let valueHtml = '';
                if (field.type === 'file' || field.type === 'image') {
                    if (field.value && field.value !== '-') {
                        valueHtml = `<a href="${field.value}" target="_blank" class="inline-flex items-center gap-2 text-[#2563EB] hover:underline">
                            <span class="material-symbols-outlined text-[18px]">${field.type === 'image' ? 'image' : 'description'}</span>
                            View File
                        </a>`;
                    } else {
                        valueHtml = '<span class="text-[#6F767E]">-</span>';
                    }
                } else if (field.type === 'textarea') {
                    valueHtml = `<div class="whitespace-pre-wrap text-[#111827] dark:text-[#FCFCFC]">${field.value || '-'}</div>`;
                } else {
                    valueHtml = `<div class="text-[#111827] dark:text-[#FCFCFC]">${field.value || '-'}</div>`;
                }

                html += `
                    <div class="bg-gray-50 dark:bg-[#0B0B0B] rounded-xl p-4">
                        <div class="text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">${field.label}</div>
                        ${valueHtml}
                    </div>
                `;
            });

            // Add metadata
            html += `
                <div class="border-t border-gray-200 dark:border-[#272B30] pt-4 mt-6">
                    <div class="text-xs font-bold text-[#6F767E] uppercase tracking-wider mb-2">Submission Info</div>
                    <div class="text-sm text-[#6F767E]">
                        <div class="flex justify-between py-1">
                            <span>IP Address</span>
                            <span class="font-mono">${entry.ip_address || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span>Submitted</span>
                            <span>${entry.submitted_at}</span>
                        </div>
                    </div>
                </div>
            `;

            html += '</div>';
            document.getElementById('entryContent').innerHTML = html;

            // Show modal
            document.getElementById('entryModal').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('entryModalContent').classList.remove('translate-x-full');
            }, 10);
        }

        function closeEntryModal() {
            document.getElementById('entryModalContent').classList.add('translate-x-full');
            setTimeout(() => {
                document.getElementById('entryModal').classList.add('hidden');
            }, 300);
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeEntryModal();
        });
    </script>
@endsection
