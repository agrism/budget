@extends('layouts.app')

@section('title', 'Analytics — Budget Tracker')

@section('content')
    @include('analytics.partials.content', ['data' => $data])
@endsection
