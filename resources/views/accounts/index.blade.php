@extends('layouts.app')

@section('title', __('Accounts') . ' — Budget Tracker')

@section('content')
<div id="accounts-container"
     hx-get="{{ route('accounts.index') }}"
     hx-trigger="accountCreated from:window, accountUpdated from:window, accountDeleted from:window"
     hx-swap="innerHTML">
    @include('accounts.partials.list', ['accounts' => $accounts, 'totalBalance' => $totalBalance])
</div>
@endsection
