@extends('layouts.admin')

@section('title', 'Events')
@section('page-title', 'Events Management')

@section('content')
<div class="space-y-6">
    @livewire('plugins.events-table')
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

