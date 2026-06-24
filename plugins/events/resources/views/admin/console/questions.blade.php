@extends('events::admin.console.layout', ['currentTab' => 'questions'])

@section('console-content')
    <livewire:plugins.events-questions-manager :event="$event" />
@endsection
