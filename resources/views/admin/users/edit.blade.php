@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <div class="px-6 pb-6 md:px-10 md:pb-10">
        <!-- Back Button -->
        <a href="{{ route('admin.users.index') }}" wire:navigate class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition mb-6">
            <span class="material-symbols-outlined mr-2 text-xl">arrow_back</span>
            Back to Users
        </a>

        @livewire('admin.users.edit-user', ['user' => $user])
    </div>
@endsection
