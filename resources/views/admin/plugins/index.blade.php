@extends('layouts.admin')

@section('title', 'Plugin Manager')
@section('page-title', 'Plugin Manager')

@section('content')
<div class="flex flex-col gap-8">
    <!-- Upload Section -->
    <div x-data="{ 
            isDropping: false, 
            file: null,
            handleDrop(e) {
                this.isDropping = false;
                if (e.dataTransfer.files.length > 0) {
                    this.file = e.dataTransfer.files[0];
                    // Update the file input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(this.file);
                    document.getElementById('plugin_zip').files = dataTransfer.files;
                }
            },
            handleFileSelect(e) {
                if (e.target.files.length > 0) {
                    this.file = e.target.files[0];
                }
            }
        }" 
        class="relative rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] shadow-sm overflow-hidden transition-all duration-300"
        :class="{ 'ring-2 ring-[#2563EB] border-[#2563EB] bg-[#2563EB]/5': isDropping }">
        
        <div class="p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Install New Plugin</h3>
                    <p class="text-[#6F767E] mt-1">Upload a plugin ZIP file to extend functionality.</p>
                </div>
                <div class="hidden md:block">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 text-xs font-medium">
                        <span class="material-symbols-outlined text-base">info</span>
                        Supports .zip files only
                    </span>
                </div>
            </div>

            <form action="{{ route('admin.plugins.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div 
                    @dragover.prevent="isDropping = true"
                    @dragleave.prevent="isDropping = false"
                    @drop.prevent="handleDrop($event)"
                    class="relative group cursor-pointer"
                >
                    <input type="file" name="plugin_zip" id="plugin_zip" accept=".zip" required
                        @change="handleFileSelect($event)"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    
                    <div class="border-2 border-dashed border-gray-300 dark:border-[#272B30] rounded-2xl p-8 text-center transition-all duration-300 group-hover:border-[#2563EB] group-hover:bg-gray-50 dark:group-hover:bg-[#272B30]/50"
                        :class="{ 'border-[#2563EB] bg-[#2563EB]/5': isDropping, 'border-[#2563EB] bg-blue-50/50 dark:bg-blue-900/10': file }">
                        
                        <!-- Default State -->
                        <div x-show="!file" class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-[#272B30] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <span class="material-symbols-outlined text-3xl text-[#6F767E] group-hover:text-[#2563EB]">cloud_upload</span>
                            </div>
                            <div>
                                <p class="text-base font-semibold text-[#111827] dark:text-[#FCFCFC]">
                                    <span class="text-[#2563EB]">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-sm text-[#6F767E] mt-1">ZIP file (max. 10MB)</p>
                            </div>
                        </div>

                        <!-- File Selected State -->
                        <div x-show="file" x-cloak class="flex flex-col items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-[#2563EB]/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-3xl text-[#2563EB]">folder_zip</span>
                            </div>
                            <div>
                                <p class="text-base font-semibold text-[#111827] dark:text-[#FCFCFC]" x-text="file ? file.name : ''"></p>
                                <p class="text-sm text-[#6F767E] mt-1" x-text="file ? (file.size / 1024 / 1024).toFixed(2) + ' MB' : ''"></p>
                            </div>
                            <button type="submit" class="mt-2 px-6 py-2.5 rounded-xl bg-[#2563EB] text-white font-semibold hover:bg-[#1D4ED8] transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2 relative z-20">
                                <span class="material-symbols-outlined text-xl">rocket_launch</span>
                                Install Now
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Plugins List -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC]">Installed Plugins</h3>
            <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-[#272B30] text-[#6F767E] text-xs font-medium">
                {{ $plugins->count() }} Total
            </span>
        </div>

        @if($plugins->isEmpty())
            <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] shadow-sm p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-[#272B30] mb-6">
                    <span class="material-symbols-outlined text-4xl text-[#6F767E]">extension_off</span>
                </div>
                <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">No plugins installed</h3>
                <p class="text-[#6F767E] max-w-md mx-auto">Your plugin library is empty. Upload a plugin ZIP file above to add new features to your CMS.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($plugins as $plugin)
                    <div class="group relative flex flex-col bg-white dark:bg-[#1A1A1A] rounded-3xl border transition-all duration-300 hover:shadow-xl hover:-translate-y-1
                        {{ $plugin->is_active 
                            ? 'border-[#2563EB]/30 ring-1 ring-[#2563EB]/30 shadow-[0_4px_20px_rgba(37,99,235,0.08)]' 
                            : 'border-gray-200 dark:border-[#272B30] hover:border-gray-300 dark:hover:border-[#3F444D]' 
                        }}">
                        
                        <div class="p-6 flex-1">
                            <div class="flex items-start justify-between mb-6">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm transition-colors duration-300
                                    {{ $plugin->is_active 
                                        ? 'bg-gradient-to-br from-[#2563EB] to-[#1D4ED8] text-white shadow-blue-500/20' 
                                        : 'bg-gray-100 dark:bg-[#272B30] text-[#6F767E]' 
                                    }}">
                                    <span class="material-symbols-outlined text-3xl">extension</span>
                                </div>
                                
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border transition-colors duration-300
                                    {{ $plugin->is_active 
                                        ? 'bg-green-50 text-green-600 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20' 
                                        : 'bg-gray-50 text-gray-600 border-gray-200 dark:bg-[#272B30] dark:text-[#6F767E] dark:border-[#3F444D]' 
                                    }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $plugin->is_active ? 'bg-green-500 animate-pulse' : 'bg-gray-500' }}"></span>
                                    {{ $plugin->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="mb-4">
                                <h4 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC] leading-tight mb-2 group-hover:text-[#2563EB] transition-colors">{{ $plugin->name }}</h4>
                                <div class="flex items-center gap-3 text-xs text-[#6F767E]">
                                    <span class="flex items-center gap-1 bg-gray-100 dark:bg-[#272B30] px-2 py-1 rounded-md">
                                        <span class="material-symbols-outlined text-[14px]">tag</span>
                                        v{{ $plugin->version }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">person</span>
                                        {{ $plugin->author ?? 'Unknown' }}
                                    </span>
                                </div>
                            </div>

                            <p class="text-sm text-[#6F767E] line-clamp-2 leading-relaxed">
                                {{ $plugin->description }}
                            </p>
                        </div>

                        <div class="px-6 py-4 border-t border-gray-100 dark:border-[#272B30] bg-gray-50/50 dark:bg-[#111315]/50 rounded-b-3xl flex items-center gap-3">
                            @if($plugin->is_active)
                                <form action="{{ route('admin.plugins.deactivate', $plugin->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 hover:border-amber-300 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 dark:hover:bg-amber-500/20 rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-lg">power_settings_new</span>
                                        Deactivate
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.plugins.activate', $plugin->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-green-700 bg-green-50 border border-green-200 hover:bg-green-100 hover:border-green-300 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20 dark:hover:bg-green-500/20 rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-lg">play_arrow</span>
                                        Activate
                                    </button>
                                </form>
                                
                                <!-- Uninstall Button with Modal -->
                                <div x-data="{ showModal: false }">
                                    <button @click.stop="showModal = true" type="button" class="p-2 text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400 rounded-xl transition-colors" title="Uninstall">
                                        <span class="material-symbols-outlined text-xl">delete</span>
                                    </button>
                                    
                                    <!-- Uninstall Modal -->
                                    <template x-teleport="body">
                                        <div x-show="showModal" x-cloak
                                            @keydown.escape.window="showModal = false"
                                            class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0">
                                            
                                            <!-- Backdrop -->
                                            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
                                        
                                        <!-- Modal Content -->
                                        <div class="relative bg-white dark:bg-[#1A1A1A] rounded-3xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-[#272B30]"
                                            @click.stop
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100">
                                            
                                            <div class="p-6">
                                                <div class="flex items-center gap-4 mb-4">
                                                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-500/10 flex items-center justify-center">
                                                        <span class="material-symbols-outlined text-2xl text-red-600 dark:text-red-400">warning</span>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-lg font-bold text-[#111827] dark:text-[#FCFCFC]">Uninstall Plugin</h3>
                                                        <p class="text-sm text-[#6F767E]">{{ $plugin->name }}</p>
                                                    </div>
                                                </div>
                                                
                                                <p class="text-[#6F767E] mb-6">
                                                    Pilih bagaimana Anda ingin menghapus plugin ini:
                                                </p>
                                                
                                                <div class="space-y-3">
                                                    <!-- Option 1: Keep Data -->
                                                    <form action="{{ route('admin.plugins.destroy', $plugin->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="delete_data" value="0">
                                                        <button type="submit" class="w-full p-4 text-left rounded-xl border border-gray-200 dark:border-[#272B30] hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all group">
                                                            <div class="flex items-start gap-3">
                                                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 mt-0.5">folder_special</span>
                                                                <div>
                                                                    <p class="font-semibold text-[#111827] dark:text-[#FCFCFC] group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Pertahankan Data</p>
                                                                    <p class="text-sm text-[#6F767E]">Hapus file plugin, tetapi pertahankan permissions untuk reinstall nanti.</p>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    </form>
                                                    
                                                    <!-- Option 2: Delete All -->
                                                    <form action="{{ route('admin.plugins.destroy', $plugin->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="delete_data" value="1">
                                                        <button type="submit" class="w-full p-4 text-left rounded-xl border border-gray-200 dark:border-[#272B30] hover:border-red-300 dark:hover:border-red-500/30 hover:bg-red-50/50 dark:hover:bg-red-500/5 transition-all group">
                                                            <div class="flex items-start gap-3">
                                                                <span class="material-symbols-outlined text-red-600 dark:text-red-400 mt-0.5">delete_forever</span>
                                                                <div>
                                                                    <p class="font-semibold text-[#111827] dark:text-[#FCFCFC] group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Hapus Semua Data</p>
                                                                    <p class="text-sm text-[#6F767E]">Hapus semua file plugin dan permissions secara permanen.</p>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <div class="px-6 py-4 border-t border-gray-100 dark:border-[#272B30] bg-gray-50/50 dark:bg-[#111315]/50 rounded-b-3xl">
                                                <button @click="showModal = false" class="w-full px-4 py-2 text-sm font-semibold text-[#6F767E] hover:text-[#111827] dark:hover:text-[#FCFCFC] transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

