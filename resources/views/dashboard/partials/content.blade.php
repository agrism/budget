@php
    $dashboardSections = $dashboardSections ?? auth()->user()?->getDashboardSections() ?? \App\Models\User::DEFAULT_DASHBOARD_SECTIONS;
@endphp

<div x-data="{ activeTab: 'all' }"
     id="dashboard-content-container"
     hx-get="{{ route('dashboard') }}"
     hx-trigger="dashboardSectionsUpdated from:window, transactionCreated from:window, transactionUpdated from:window, transactionDeleted from:window, savingsTargetUpdated from:window"
     hx-swap="outerHTML">

    @foreach($dashboardSections as $section)
        @if(!empty($section['enabled']))
            @if($section['id'] === 'balance_card')
                @include('dashboard.partials.sections.balance_card', ['summary' => $summary])
            @elseif($section['id'] === 'daily_limit')
                @include('dashboard.partials.sections.daily_limit', ['summary' => $summary])
            @elseif($section['id'] === 'top_categories')
                @include('dashboard.partials.sections.top_categories', ['summary' => $summary])
            @elseif($section['id'] === 'recent_transactions')
                @include('dashboard.partials.sections.recent_transactions', ['summary' => $summary])
            @endif
        @endif
    @endforeach
</div>
