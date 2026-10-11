<div class="mb-5">
    <!-- Recent Transactions Header & Filter Section -->
    <div class="mb-3">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ __('Recent Transactions') }}</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('Showing latest') }}</span>
        </div>

        <!-- Search Bar with Live HTMX Filtering -->
        <div class="relative mb-3">
            <input type="text"
                   id="transaction-search-input"
                   name="search"
                   placeholder="{{ __('Search transactions, notes, categories...') }}"
                   hx-get="{{ route('dashboard.recent_transactions') }}"
                   hx-trigger="keyup changed delay:300ms, search"
                   hx-target="#transaction-feed-container"
                   hx-swap="innerHTML"
                   hx-include="#recent-tx-type-input"
                   hx-indicator="#transaction-feed-container"
                   class="w-full bg-slate-50 dark:bg-[#141926] border border-slate-200 dark:border-[#232d42] rounded-2xl py-2.5 pl-10 pr-4 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-sm">
            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <input type="hidden" id="recent-tx-type-input" name="type" :value="activeTab === 'all' ? '' : activeTab">

        <!-- Filter Pill Tabs with Dynamic Visual Active State & Counts -->
        @php
            $counts = $summary['transaction_counts'] ?? null;
        @endphp
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 text-xs">
            <button type="button"
                    @click="activeTab = 'all'"
                    hx-get="{{ route('dashboard.recent_transactions') }}"
                    hx-include="#transaction-search-input"
                    hx-indicator="#transaction-feed-container"
                    hx-target="#transaction-feed-container"
                    hx-swap="innerHTML"
                    :class="activeTab === 'all' ? 'bg-brand-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600'"
                    class="px-4 py-1.5 rounded-full transition-all text-xs whitespace-nowrap">
                {{ __('All') }}@if($counts) ({{ $counts['all'] }})@endif
            </button>
            <button type="button"
                    @click="activeTab = 'expense'"
                    hx-get="{{ route('dashboard.recent_transactions', ['type' => 'expense']) }}"
                    hx-include="#transaction-search-input"
                    hx-indicator="#transaction-feed-container"
                    hx-target="#transaction-feed-container"
                    hx-swap="innerHTML"
                    :class="activeTab === 'expense' ? 'bg-gradient-to-r from-rose-500 to-amber-500 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600'"
                    class="px-4 py-1.5 rounded-full transition-all text-xs whitespace-nowrap">
                {{ __('Expenses') }}@if($counts) ({{ $counts['expense'] }})@endif
            </button>
            <button type="button"
                    @click="activeTab = 'income'"
                    hx-get="{{ route('dashboard.recent_transactions', ['type' => 'income']) }}"
                    hx-include="#transaction-search-input"
                    hx-indicator="#transaction-feed-container"
                    hx-target="#transaction-feed-container"
                    hx-swap="innerHTML"
                    :class="activeTab === 'income' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600'"
                    class="px-4 py-1.5 rounded-full transition-all text-xs whitespace-nowrap">
                {{ __('Income') }}@if($counts) ({{ $counts['income'] }})@endif
            </button>
        </div>
    </div>

    <!-- Reactive Transaction Feed -->
    <div id="transaction-feed-container"
         class="transition-opacity duration-150 [&.htmx-request]:opacity-50">
        @include('dashboard.partials.transaction_list', ['transactions' => $summary['recent_transactions'], 'type' => 'all'])
    </div>
</div>
