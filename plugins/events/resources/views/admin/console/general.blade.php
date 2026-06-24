@extends('events::admin.console.layout', ['currentTab' => 'general'])

@section('console-content')
    <livewire:plugins.event-form :eventId="$event->id" :consoleMode="true" />
@endsection
