@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'Profile')

@section('content')
    <div class="px-6 pb-6 md:px-10 md:pb-10">
        @livewire('admin.profile.profile-form')
    </div>
@endsection
