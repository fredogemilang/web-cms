@extends('layouts.admin')

@section('title', 'Menu Management')
@section('page-title', 'Menu Management')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="glass rounded-3xl shadow-lg p-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Navigation Menus</h2>
            <p class="text-sm text-gray-600 mt-1">Manage sidebar navigation structure</p>
        </div>
        @can('menus.create')
        <a href="{{ route('admin.menus.create') }}" wire:navigate class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Menu Item
        </a>
        @endcan
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="glass-card rounded-2xl p-4 border-l-4 border-green-500 bg-green-50/50">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
    <div class="glass-card rounded-2xl p-4 border-l-4 border-red-500 bg-red-50/50">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Menu Items -->
    <div class="glass-card rounded-3xl p-6">
        <div class="space-y-3" id="menu-list">
            @forelse($menus as $menu)
            <div class="bg-white/50 rounded-2xl border border-gray-200 overflow-hidden">
                <!-- Parent Menu -->
                <div class="flex items-center p-4 hover:bg-white transition">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mr-4">
                        @if($menu->icon)
                            <i class="{{ $menu->icon }} text-white"></i>
                        @else
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        @endif
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-900">{{ $menu->title }}</h3>
                            @if(!$menu->is_active)
                            <span class="px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                            @endif
                            @if($menu->permission)
                            <span class="px-2 py-0.5 text-xs font-bold bg-purple-100 text-purple-700 rounded-full">{{ $menu->permission }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600">
                            @if($menu->route)
                                Route: {{ $menu->route }}
                            @else
                                <span class="text-gray-400">No route</span>
                            @endif
                            <span class="mx-2">•</span>
                            Order: {{ $menu->order }}
                            @if($menu->children->isNotEmpty())
                                <span class="mx-2">•</span>
                                {{ $menu->children->count() }} sub-items
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @can('menus.view')
                        <a href="{{ route('admin.menus.show', $menu) }}" wire:navigate class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        @endcan
                        
                        @can('menus.edit')
                        <a href="{{ route('admin.menus.edit', $menu) }}" wire:navigate class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        @endcan
                        
                        @can('menus.delete')
                        <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this menu item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>

                <!-- Child Menus -->
                @if($menu->children->isNotEmpty())
                <div class="bg-gray-50/50 border-t border-gray-200">
                    @foreach($menu->children as $child)
                    <div class="flex items-center p-4 pl-16 hover:bg-white/50 transition border-b border-gray-100 last:border-0">
                        <div class="w-8 h-8 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-lg flex items-center justify-center mr-3">
                            @if($child->icon)
                                <i class="{{ $child->icon }} text-white text-sm"></i>
                            @else
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            @endif
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-medium text-gray-900">{{ $child->title }}</h4>
                                @if(!$child->is_active)
                                <span class="px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                                @endif
                                @if($child->permission)
                                <span class="px-2 py-0.5 text-xs font-bold bg-purple-100 text-purple-700 rounded-full">{{ $child->permission }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600">
                                @if($child->route)
                                    Route: {{ $child->route }}
                                @else
                                    <span class="text-gray-400">No route</span>
                                @endif
                                <span class="mx-2">•</span>
                                Order: {{ $child->order }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @can('menus.view')
                            <a href="{{ route('admin.menus.show', $child) }}" wire:navigate class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            @endcan
                            
                            @can('menus.edit')
                            <a href="{{ route('admin.menus.edit', $child) }}" wire:navigate class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @endcan
                            
                            @can('menus.delete')
                            <form action="{{ route('admin.menus.destroy', $child) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this menu item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <p class="text-gray-500 font-medium">No menu items found</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
