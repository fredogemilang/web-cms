@extends('layouts.admin')

@section('title', 'Event Categories')
@section('page-title', 'Event Categories')

@section('content')
<div class="space-y-6">
    @livewire('plugins.event-categories')
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
