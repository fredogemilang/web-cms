@extends('events::admin.console.layout', ['currentTab' => 'referrals'])

@section('console-content')
    <livewire:plugins.event-console-referrals :event="$event" />
@endsection
