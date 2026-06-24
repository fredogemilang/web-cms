@extends('events::admin.console.layout', ['currentTab' => 'datetime'])

@section('console-content')
    <livewire:plugins.event-console-datetime :eventId="$event->id" />
@endsection
