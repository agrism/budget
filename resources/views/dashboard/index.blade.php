@extends('layouts.app')

@section('title', 'Dashboard — Budget Tracker')

@section('content')
    @include('dashboard.partials.content', ['summary' => $summary, 'dashboardSections' => $dashboardSections])
@endsection
