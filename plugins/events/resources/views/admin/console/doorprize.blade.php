@extends('events::admin.console.layout', ['currentTab' => 'doorprize'])

@section('console-content')
    <livewire:plugins.event-console-doorprize :event="$event" />
@endsection
