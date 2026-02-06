@extends('layouts.admin')

@section('title', 'Tags')
@section('page-title', 'Post Tags')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">Tags</h2>
            <p class="text-[#6F767E] mt-1">Manage post tags for better discoverability</p>
        </div>
        @can('posts.tags.create')
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-[#2563EB] text-white rounded-xl font-semibold hover:bg-[#1D4ED8] transition-all shadow-lg shadow-blue-500/20">
            <span class="material-symbols-outlined text-xl">add</span>
            New Tag
        </button>
        @endcan
    </div>

    <div class="rounded-3xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#272B30] shadow-sm overflow-hidden">
        <div class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-[#272B30] mb-6">
                <span class="material-symbols-outlined text-4xl text-[#6F767E]">label</span>
            </div>
            <h3 class="text-xl font-bold text-[#111827] dark:text-[#FCFCFC] mb-2">No tags yet</h3>
            <p class="text-[#6F767E] max-w-md mx-auto">Create tags to help readers find related content.</p>
        </div>
    </div>
</div>
@endsection
