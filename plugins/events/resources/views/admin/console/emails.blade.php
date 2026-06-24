@extends('events::admin.console.layout', ['currentTab' => 'emails'])

@section('console-content')
    <livewire:plugins.event-console-emails :eventId="$event->id" />
@endsection
