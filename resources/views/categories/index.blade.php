@extends('layouts.app')

@section('title', __('Categories') . ' — Budget')

@section('content')
    <div id="category-list-container">
        @include('categories.partials.list', [
            'categories' => $categories, 
            'currencySymbol' => $currencySymbol, 
            'currentMonth' => $currentMonth
        ])
    </div>
@endsection
