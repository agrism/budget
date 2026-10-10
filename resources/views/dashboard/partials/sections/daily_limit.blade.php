@php
    $sym = $summary['currency_symbol'] ?? '€';
@endphp
<!-- Daily Spending Limit & Target Savings Card -->
<div class="app-card rounded-3xl p-4 mb-5 border border-emerald-500/25 bg-gradient-to-br from-white via-emerald-50/20 to-teal-50/30 dark:from-[#0f172a] dark:via-[#0c1322] dark:to-[#09101d] shadow-md relative overflow-hidden">
    <!-- Top Row: Title & Savings Target Badge -->
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">{{ __('Daily Spending Limit') }}</span>
                <span class="text-xs font-extrabold text-slate-900 dark:text-white">{{ __('Safe to spend today') }}</span>
            </div>
        </div>

        <!-- Savings Target % Button -->
        <button type="button"
                @click="modalOpen = true"
                hx-get="{{ route('savings.modal') }}"
                hx-target="#modal-body"
                class="px-2.5 py-1 rounded-xl bg-emerald-100/80 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-[11px] font-bold hover:scale-105 transition-all flex items-center gap-1 shadow-sm">
            <span>🎯 {{ $summary['savings_target_percentage'] }}% {{ __('Savings') }}</span>
            <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
        </button>
    </div>

    <!-- Center Hero: Daily Allowance Amount -->
    <div class="flex items-baseline justify-between mb-3 bg-white/70 dark:bg-[#151c2d]/70 p-3 rounded-2xl border border-slate-200/70 dark:border-slate-800">
        <div>
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                {{ $sym }}{{ number_format($summary['daily_spending_limit'], 2) }}
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">/ {{ __('day') }}</span>
            </div>
            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                {{ __('Based on') }} {{ $summary['savings_target_percentage'] }}% {{ __('target savings') }} ({{ $summary['days_in_month'] }} {{ __('days in month') }})
            </div>
        </div>

        <div class="text-right">
            @if($summary['today_is_over'])
                <span class="inline-flex items-center text-[10px] font-extrabold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/15 px-2 py-0.5 rounded-full border border-rose-200 dark:border-rose-500/30">
                    {{ __('Exceeded daily limit') }}
                </span>
            @else
                <span class="inline-flex items-center text-[10px] font-extrabold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/30">
                    {{ __('On Track') }}
                </span>
            @endif
            <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 mt-0.5">
                {{ __('Left today') }}: <span class="{{ $summary['today_is_over'] ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $sym }}{{ number_format(max(0, $summary['today_remaining_daily']), 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Today's Spending Progress Bar -->
    <div>
        <div class="flex justify-between items-center text-[11px] mb-1">
            <span class="text-slate-600 dark:text-slate-400 font-medium">
                {{ __('Spent today') }}: <strong class="text-slate-900 dark:text-white">{{ $sym }}{{ number_format($summary['spent_today'], 2) }}</strong>
            </span>
            <span class="text-slate-500 dark:text-slate-400 text-[10px]">
                {{ $summary['days_remaining'] }} {{ __('days left in month') }}
            </span>
        </div>
        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-700 {{ $summary['today_is_over'] ? 'bg-rose-500' : 'bg-gradient-to-r from-emerald-500 to-teal-400' }}"
                 style="width: {{ min(100, $summary['today_percentage']) }}%;">
            </div>
        </div>
    </div>
</div>
