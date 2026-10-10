@php
    $sym = $summary['currency_symbol'] ?? '€';
@endphp
<!-- Total Balance & Budget Summary Glowing Card -->
<div class="app-card-glow rounded-3xl p-5 mb-5 relative overflow-hidden transition-all duration-200">
    <!-- Ambient back glow -->
    <div class="absolute -right-10 -top-10 w-36 h-36 bg-brand-500/15 dark:bg-brand-500/20 rounded-full blur-2xl pointer-events-none"></div>

    <div class="flex justify-between items-start mb-4">
        <div>
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Balance') }}</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1 tracking-tight">
                {{ $sym }}{{ number_format($summary['total_balance'], 2) }}
            </h1>
            <div class="flex items-center gap-1 mt-1">
                <span class="inline-flex items-center text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/20">
                    <svg class="w-3 h-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    +2.1%
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('vs last month') }}</span>
            </div>
        </div>

        <div class="text-right">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Target Savings') }}</span>
            <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center justify-end gap-1">
                <span>{{ $summary['savings_target_percentage'] }}%</span>
                <span class="text-xs font-bold text-emerald-700/80 dark:text-emerald-400/80">({{ $sym }}{{ number_format($summary['target_savings_amount'], 0) }})</span>
            </div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">
                +{{ $sym }}{{ number_format($summary['monthly_income'], 0) }} in
            </span>
        </div>
    </div>

    <!-- Monthly Budget Status Bar -->
    <div class="pt-3 border-t border-slate-200/80 dark:border-slate-700/40">
        <div class="flex justify-between items-center text-xs mb-1.5">
            <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ __('Monthly Budget Status') }}</span>
            <span class="text-slate-500 dark:text-slate-400">
                {{ __('Spent') }} <strong class="text-slate-900 dark:text-white">{{ $sym }}{{ number_format($summary['total_budget_spent'], 2) }}</strong> / {{ $sym }}{{ number_format($summary['total_budget_limit'], 0) }}
            </span>
        </div>

        <!-- Progress bar with gradient -->
        <div class="w-full bg-slate-200/90 dark:bg-slate-800/80 rounded-full h-2.5 overflow-hidden p-0.5 border border-slate-300/40 dark:border-slate-700/50">
            <div class="h-full rounded-full transition-all duration-700 {{ $summary['budget_percentage'] > 90 ? 'bg-gradient-to-r from-amber-500 to-rose-500' : 'bg-gradient-to-r from-emerald-500 to-brand-600' }}"
                 style="width: {{ min(100, $summary['budget_percentage']) }}%;">
            </div>
        </div>

        <div class="flex justify-between items-center mt-2 text-[11px]">
            <span class="text-emerald-700 dark:text-emerald-400 font-semibold bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800/30">
                {{ __('Spent') }}: {{ $sym }}{{ number_format($summary['total_budget_spent'], 2) }}
            </span>
            <span class="text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/40 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-700/30">
                {{ __('Remaining') }}: {{ $sym }}{{ number_format($summary['remaining_budget'], 2) }}
            </span>
        </div>
    </div>
</div>
