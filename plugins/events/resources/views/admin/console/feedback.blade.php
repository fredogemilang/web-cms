@extends('events::admin.console.layout', ['currentTab' => 'feedback'])

@section('console-content')
    <livewire:plugins.event-console-feedback :event="$event" />
@endsection
