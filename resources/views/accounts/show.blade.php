@extends('layouts.app')

@section('title', $account->name . ' — ' . __('Account Transactions'))

@section('content')
@php
    $currencySymbol = ($account->currency ?? 'EUR') === 'USD' ? '$' : '€';
@endphp
<div class="space-y-4"
     x-data="{}"
     @account-updated.window="window.location.reload()"
     @account-deleted.window="window.location.href = '{{ route('accounts.index') }}'">
    <!-- Top Navigation Bar (Back, Account Title, Edit Button) -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('accounts.index') }}" 
               class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors shadow-xs"
               title="{{ __('Back') }}">
                <svg class="w-4 h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shadow-xs flex-shrink-0"
                     style="background-color: {{ $account->color }}25; color: {{ $account->color }};">
                    @if($account->type === 'cash')
                        <svg class="w-4 h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    @elseif($account->type === 'savings')
                        <svg class="w-4 h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($account->type === 'credit')
                        <svg class="w-4 h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    @else
                        <svg class="w-4 h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @endif
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">
                        {{ $account->name }}
                    </h1>
                    <span class="text-[10px] text-slate-400 capitalize">{{ $account->type }} • {{ $account->currency }}</span>
                </div>
            </div>
        </div>

        <!-- Edit Account Button -->
        <button type="button" 
                @click="modalOpen = true"
                hx-get="{{ route('accounts.edit', $account) }}"
                hx-target="#modal-body"
                hx-swap="innerHTML"
                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs">
            <svg class="w-3.5 h-3.5" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>{{ __('Edit') }}</span>
        </button>
    </div>

    <!-- Account Balance Hero Card -->
    <div class="app-card-glow rounded-3xl p-5 border border-slate-200 dark:border-[#232d42] bg-gradient-to-br from-white via-slate-50 to-slate-100/60 dark:from-[#141926] dark:via-[#10141f] dark:to-[#0c1017] shadow-lg flex items-center justify-between">
        <div>
            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">{{ __('Account Balance') }}</span>
            <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
                {{ $currencySymbol }}{{ number_format($account->balance, 2) }}
            </div>
        </div>
        <div class="text-right">
            <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-500/20">
                {{ $transactions->total() }} {{ __('Recent Transactions') }}
            </span>
        </div>
    </div>

    <!-- Transactions Feed Section -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                {{ __('Account Transactions') }}
            </h2>
            <span class="text-xs text-slate-400">{{ __('Newest first') }}</span>
        </div>

        <!-- Infinite Scroll Transactions Container -->
        <div id="account-transactions-list"
             class="space-y-2.5"
             hx-get="{{ route('accounts.show', ['account' => $account, 'feed_only' => 1]) }}"
             hx-trigger="transactionUpdated from:window, transactionDeleted from:window, transactionCreated from:window"
             hx-swap="innerHTML">
            @include('accounts.partials.transactions_feed', ['account' => $account, 'transactions' => $transactions])
        </div>
    </div>
</div>
@endsection
