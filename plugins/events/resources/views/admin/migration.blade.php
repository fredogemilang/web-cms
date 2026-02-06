@extends('layouts.admin')

@section('title', 'Import Events from WordPress')

@section('content')
    <div class="max-w-5xl mx-auto py-8">
        <livewire:plugins.events.livewire.word-press-event-migration />
    </div>
@endsection
