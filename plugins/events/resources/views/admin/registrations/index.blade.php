@extends('layouts.admin')

@section('title', 'Event Registrations')
@section('page-title', 'Event Registrations')

@section('content')
<div class="space-y-6">
    @livewire('plugins.event-registrations')
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
