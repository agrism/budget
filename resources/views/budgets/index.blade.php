@extends('layouts.app')

@section('title', 'Budgets — Budget Tracker')

@section('content')
    @include('budgets.partials.content', ['data' => $data])
@endsection
