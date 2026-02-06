@extends('layouts.admin')

@section('title', 'Edit Custom Post Type')
@section('hide-header', true)

@section('content')
    <livewire:admin.cpt.cpt-form :id="$id" />
@endsection
