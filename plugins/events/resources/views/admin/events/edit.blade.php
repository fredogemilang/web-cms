@extends('layouts.admin')

@section('title', 'Edit Event')

@section('content')
    <livewire:plugins.event-form :eventId="$event->id" />
@endsection
