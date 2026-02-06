@extends('layouts.admin')

@section('title', 'Speakers')

@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-[#111827] dark:text-[#FCFCFC]">Speakers</h1>
            <p class="text-sm text-[#6F767E] mt-1">Manage event speakers and guests</p>
        </div>
        
        <livewire:plugins.speakers-table />
    </div>
@endsection
