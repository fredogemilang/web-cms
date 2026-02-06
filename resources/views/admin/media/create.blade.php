@extends('layouts.admin')

@section('title', 'Add Media')
@section('page-title', 'Add Media')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">Add Media</h1>
            <p class="text-sm text-[#6F767E] mt-1">Upload new media files to your library</p>
        </div>
        <a wire:navigate
            href="{{ route('admin.media.index') }}"
            class="flex items-center gap-2 px-6 py-3 bg-white dark:bg-[#1A1A1A] border border-gray-300 dark:border-[#272B30] text-[#111827] dark:text-[#FCFCFC] rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-[#272B30] transition-all shadow-sm">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
            <span>Back to Library</span>
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
            <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
            <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Upload Card --}}
    <div class="bg-white dark:bg-[#1A1A1A] rounded-3xl shadow-sm border border-gray-200 dark:border-[#272B30] p-8">
        <livewire:admin.media-uploader :is-modal="false" />
    </div>

    {{-- Info Card --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-6">
        <div class="flex gap-4">
            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-3xl">info</span>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Upload Guidelines</h3>
                <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                    <li>• Maximum file size: {{ config('media.max_file_size') / 1024 }}MB</li>
                    <li>• Allowed formats: {{ implode(', ', config('media.allowed_extensions')) }}</li>
                    <li>• Images will be automatically optimized and converted to WebP format</li>
                    <li>• You can upload multiple files at once</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
