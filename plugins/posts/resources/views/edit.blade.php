@extends('layouts.admin')

@section('title', 'Edit Post')
@section('hide-header', true)

@section('content')
    @livewire('plugins.post-form', ['postId' => $id])
@endsection
