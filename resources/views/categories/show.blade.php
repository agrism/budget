@extends('layouts.app')

@section('title', $category->name . ' — ' . __('Category Transactions'))

@section('content')
<div class="space-y-4"
     x-data="{}"
     @category-updated.window="window.location.reload()"
     @category-deleted.window="window.location.href = '{{ route('categories.index') }}'">
    <!-- Top Navigation Bar (Back, Category Title, Edit Button) -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('categories.index') }}" 
               class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors shadow-xs"
               title="{{ __('Back') }}">
                <svg class="w-4 h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shadow-xs flex-shrink-0"
                     style="background-color: {{ $category->color }}25; color: {{ $category->color }};">
                    <x-category-icon :icon="$category->icon" class="w-4 h-4" />
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">
                        {{ $category->name }}
                    </h1>
                    <span class="text-[10px] text-slate-400 capitalize">
                        {{ $category->type === 'income' ? __('Income') : __('Expense') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Edit Category Button -->
        <button type="button" 
                @click="modalOpen = true"
                hx-get="{{ route('categories.edit', $category) }}"
                hx-target="#modal-body"
                hx-swap="innerHTML"
                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold flex items-center gap-1.5 transition-colors shadow-xs">
            <svg class="w-3.5 h-3.5" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>{{ __('Edit') }}</span>
        </button>
    </div>

    <!-- Category Spending / Budget Hero Card -->
    <div class="app-card-glow rounded-3xl p-5 border border-slate-200 dark:border-[#232d42] bg-gradient-to-br from-white via-slate-50 to-slate-100/60 dark:from-[#141926] dark:via-[#10141f] dark:to-[#0c1017] shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                    {{ $category->type === 'income' ? __('Income this month') : __('Spent this month') }}
                </span>
                <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
                    {{ $currencySymbol }}{{ number_format($thisMonthSpent, 2) }}
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-500/20">
                    {{ $transactions->total() }} {{ __('Recent Transactions') }}
                </span>
            </div>
        </div>

        @if($monthlyLimit !== null && $monthlyLimit > 0 && $category->type === 'expense')
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">
                        {{ __('Budget') }}: {{ $currencySymbol }}{{ number_format($monthlyLimit, 0) }}
                    </span>
                    @if($thisMonthSpent > $monthlyLimit)
                        <span class="text-rose-600 dark:text-rose-400 font-bold">
                            {{ __('Over budget') }} (+{{ $currencySymbol }}{{ number_format($thisMonthSpent - $monthlyLimit, 2) }})
                        </span>
                    @else
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">
                            {{ __('Remaining') }}: {{ $currencySymbol }}{{ number_format($monthlyLimit - $thisMonthSpent, 2) }}
                        </span>
                    @endif
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $thisMonthSpent > $monthlyLimit ? 'bg-rose-500' : 'bg-gradient-to-r from-emerald-500 to-teal-400' }}"
                         style="width: {{ min(100, round(($thisMonthSpent / $monthlyLimit) * 100)) }}%;">
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Transactions Feed Section -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                {{ __('Category Transactions') }}
            </h2>
            <span class="text-xs text-slate-400">{{ __('Newest first') }}</span>
        </div>

        <!-- Infinite Scroll Transactions Container -->
        <div id="category-transactions-list"
             class="space-y-2.5"
             hx-get="{{ route('categories.show', ['category' => $category, 'feed_only' => 1]) }}"
             hx-trigger="transactionUpdated from:window, transactionDeleted from:window, transactionCreated from:window"
             hx-swap="innerHTML">
            @include('categories.partials.transactions_feed', ['category' => $category, 'transactions' => $transactions, 'currencySymbol' => $currencySymbol])
        </div>
    </div>
</div>
@endsection
