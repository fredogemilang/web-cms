@extends('events::admin.console.layout', ['currentTab' => 'general'])

@section('console-content')
    <livewire:plugins.event-console-general :eventId="$event->id" />
@endsection
