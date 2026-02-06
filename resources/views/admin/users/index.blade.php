@extends('layouts.admin')

@section('title', 'Users Management')
@section('page-title', 'All Users')

@section('content')
<div class="space-y-6">
    <!-- Livewire Users Table Component -->
    @livewire('admin.users-table')
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
