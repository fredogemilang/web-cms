@extends('layouts.admin')

@section('title', 'Edit Menu Item')
@section('page-title', 'Edit Menu Item')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="{{ route('admin.menus.index') }}" wire:navigate class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Menus
    </a>

    <!-- Form Card -->
    <div class="glass-card rounded-3xl p-8">
        <form action="{{ route('admin.menus.update', $menu) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-bold text-gray-700 mb-2">Menu Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $menu->title) }}" required
                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Parent Menu -->
            <div>
                <label for="parent_id" class="block text-sm font-bold text-gray-700 mb-2">Parent Menu (Optional)</label>
                <select name="parent_id" id="parent_id"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('parent_id') border-red-500 @enderror">
                    <option value="">None (Top Level)</option>
                    @foreach($parentMenus as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $menu->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->title }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Icon -->
            <div>
                <label for="icon" class="block text-sm font-bold text-gray-700 mb-2">Icon Class (Optional)</label>
                <input type="text" name="icon" id="icon" value="{{ old('icon', $menu->icon) }}"
                    placeholder="e.g., fas fa-home, heroicon-o-home"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('icon') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Font Awesome or Heroicon class name</p>
                @error('icon')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Route -->
            <div>
                <label for="route" class="block text-sm font-bold text-gray-700 mb-2">Route Name (Optional)</label>
                <input type="text" name="route" id="route" value="{{ old('route', $menu->route) }}"
                    placeholder="e.g., admin.users.index"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('route') border-red-500 @enderror">
                @error('route')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Permission -->
            <div>
                <label for="permission" class="block text-sm font-bold text-gray-700 mb-2">Required Permission (Optional)</label>
                <select name="permission" id="permission"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('permission') border-red-500 @enderror">
                    <option value="">None (Visible to all)</option>
                    @foreach($permissions as $perm)
                        <option value="{{ $perm->name }}" {{ old('permission', $menu->permission) == $perm->name ? 'selected' : '' }}>
                            {{ $perm->name }} - {{ $perm->description }}
                        </option>
                    @endforeach
                </select>
                @error('permission')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Order -->
            <div>
                <label for="order" class="block text-sm font-bold text-gray-700 mb-2">Display Order</label>
                <input type="number" name="order" id="order" value="{{ old('order', $menu->order) }}" required min="0"
                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition @error('order') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                @error('order')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Is Active -->
            <div>
                <label class="flex items-center p-4 bg-white/50 rounded-2xl hover:bg-white transition cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }}
                        class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="text-sm font-bold text-gray-900">Active</span>
                        <p class="text-xs text-gray-500">Show this menu item in navigation</p>
                    </div>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.menus.index') }}" wire:navigate class="px-6 py-3 text-gray-700 font-bold rounded-2xl hover:bg-gray-100 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                    Update Menu Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
