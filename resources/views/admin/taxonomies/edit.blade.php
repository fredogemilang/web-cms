@extends('layouts.admin')

@section('title', 'Edit Taxonomy')
@section('hide-header', true)

@section('content')
    <livewire:admin.taxonomies.taxonomy-form :id="$id" />
@endsection
