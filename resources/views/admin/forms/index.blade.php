@extends('layouts.admin')

@section('title', 'Forms Management')
@section('page-title', 'All Forms')

@section('content')
<div class="space-y-6">
    @livewire('admin.forms-table')
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
