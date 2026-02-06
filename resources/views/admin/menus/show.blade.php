@extends('layouts.admin')

@section('title', 'Menu Item Details')
@section('page-title', 'Menu Item Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="{{ route('admin.menus.index') }}" wire:navigate class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Menus
    </a>

    <!-- Menu Profile Card -->
    <div class="glass-card rounded-3xl p-8">
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg">
                    @if($menu->icon)
                        <i class="{{ $menu->icon }} text-white text-2xl"></i>
                    @else
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    @endif
                </div>
                <div class="ml-6">
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $menu->title }}</h2>
                        @if($menu->is_active)
                        <span class="px-3 py-1 text-xs font-bold bg-green-100 text-green-700 rounded-full">Active</span>
                        @else
                        <span class="px-3 py-1 text-xs font-bold bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                        @endif
                    </div>
                    @if($menu->parent)
                    <p class="text-gray-600 mt-1">Sub-item of: {{ $menu->parent->title }}</p>
                    @else
                    <p class="text-gray-600 mt-1">Top-level menu item</p>
                    @endif
                </div>
            </div>
            
            <div class="flex gap-2">
                @can('menus.edit')
                <a href="{{ route('admin.menus.edit', $menu) }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition">
                    Edit Menu
                </a>
                @endcan
                
                @can('menus.delete')
                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this menu item?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition">
                        Delete Menu
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
            <!-- Menu Information -->
            <div>
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Menu Information</h3>
                <div class="space-y-3">
                    <div class="p-4 bg-white/50 rounded-2xl">
                        <p class="text-xs text-gray-500 mb-1">Title</p>
                        <p class="text-sm font-bold text-gray-900">{{ $menu->title }}</p>
                    </div>
                    <div class="p-4 bg-white/50 rounded-2xl">
                        <p class="text-xs text-gray-500 mb-1">Route</p>
                        <p class="text-sm font-bold text-gray-900">{{ $menu->route ?: 'Not set' }}</p>
                    </div>
                    <div class="p-4 bg-white/50 rounded-2xl">
                        <p class="text-xs text-gray-500 mb-1">Icon Class</p>
                        <p class="text-sm font-bold text-gray-900">{{ $menu->icon ?: 'Not set' }}</p>
                    </div>
                    <div class="p-4 bg-white/50 rounded-2xl">
                        <p class="text-xs text-gray-500 mb-1">Display Order</p>
                        <p class="text-sm font-bold text-gray-900">{{ $menu->order }}</p>
                    </div>
                </div>
            </div>

            <!-- Access Control -->
            <div>
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Access Control</h3>
                <div class="space-y-3">
                    <div class="p-4 bg-white/50 rounded-2xl">
                        <p class="text-xs text-gray-500 mb-1">Required Permission</p>
                        @if($menu->permission)
                        <span class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-700">{{ $menu->permission }}</span>
                        @else
                        <p class="text-sm text-gray-600">No permission required (visible to all)</p>
                        @endif
                    </div>
                    <div class="p-4 bg-white/50 rounded-2xl">
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        @if($menu->is_active)
                        <span class="inline-flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></span>
                            <span class="text-sm font-medium text-gray-900">Active</span>
                        </span>
                        @else
                        <span class="inline-flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-400 mr-2"></span>
                            <span class="text-sm font-medium text-gray-900">Inactive</span>
                        </span>
                        @endif
                    </div>
                    <div class="p-4 bg-white/50 rounded-2xl">
                        <p class="text-xs text-gray-500 mb-1">Created</p>
                        <p class="text-sm font-bold text-gray-900">{{ $menu->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="p-4 bg-white/50 rounded-2xl">
                        <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                        <p class="text-sm font-bold text-gray-900">{{ $menu->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Child Menu Items -->
    @if($menu->children->isNotEmpty())
    <div class="glass-card rounded-3xl p-8">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Sub-Menu Items ({{ $menu->children->count() }})</h3>
        <div class="space-y-2">
            @foreach($menu->children as $child)
            <div class="flex items-center p-4 bg-white/50 rounded-2xl border border-gray-100 hover:bg-white transition">
                <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-xl flex items-center justify-center mr-4">
                    @if($child->icon)
                        <i class="{{ $child->icon }} text-white"></i>
                    @else
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-gray-900">{{ $child->title }}</h4>
                        @if(!$child->is_active)
                        <span class="px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-600">{{ $child->route ?: 'No route' }} • Order: {{ $child->order }}</p>
                </div>
                @can('menus.view')
                <a href="{{ route('admin.menus.show', $child) }}" wire:navigate class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @endcan
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
