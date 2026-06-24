@extends('events::admin.console.layout', ['currentTab' => 'attendees'])

@section('console-content')
    <livewire:plugins.event-guests-table
        :event="$event"
        :approvalTypes="$approvalTypes"
        :key="'guests-'.$event->id" />

    <script>window._guestEventId = {{ $event->id }};</script>
@endsection
