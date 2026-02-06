@extends('layouts.admin')

@section('title', 'Theme Manager')
@section('page-title', 'Theme Manager')

@section('content')
<div class="flex flex-col gap-8">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">Themes</h2>
            <p class="text-[#6F767E] mt-1">Manage and customize your frontend appearance</p>
        </div>
        <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-[#272B30] text-[#6F767E] text-xs font-medium">
            {{ $themes->count() }} Total
        </span>
    </div>

    {{-- Livewire Theme Manager Component --}}
    <livewire:admin.themes.theme-manager />
</div>
@endsection
