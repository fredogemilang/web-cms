@extends('layouts.admin')

@section('title', 'Add New Post')
@section('hide-header', true)

@section('content')
    @livewire('plugins.post-form')
@endsection
