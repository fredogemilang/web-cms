@extends('layouts.admin')

@section('title', 'Media Library')
@section('page-title', 'Media Library')

@section('content')
<div class="space-y-6">
    {{-- Header with Upload Button --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">Media Library</h1>
            <p class="text-sm text-[#6F767E] mt-1">Manage your media files</p>
        </div>
        @can('media.upload')
        <button 
            onclick="Livewire.dispatch('open-upload-modal')"
            class="flex items-center gap-2 px-6 py-3 bg-[#2563EB] text-white rounded-xl font-semibold hover:bg-[#1D4ED8] transition-all shadow-sm">
            <span class="material-symbols-outlined text-xl">add</span>
            <span>Upload Media</span>
        </button>
        @endcan
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

    {{-- Media Library Component --}}
    <livewire:admin.media-library />
</div>

{{-- Media Uploader Modal --}}
<livewire:admin.media-uploader />

{{-- Media Details Modal --}}
<livewire:admin.media-details />
@endsection
