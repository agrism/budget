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
                   name="search"
                   placeholder="{{ __('Search transactions, notes, categories...') }}"
                   hx-get="{{ route('dashboard.recent_transactions') }}"
                   hx-trigger="keyup changed delay:300ms, search"
                   hx-target="#transaction-feed-container"
                   class="w-full bg-slate-50 dark:bg-[#141926] border border-slate-200 dark:border-[#232d42] rounded-2xl py-2.5 pl-10 pr-4 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-sm">
            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <!-- Filter Pill Tabs with Dynamic Visual Active State -->
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1 text-xs">
            <button type="button"
                    @click="activeTab = 'all'"
                    hx-get="{{ route('dashboard.recent_transactions') }}"
                    hx-target="#transaction-feed-container"
                    :class="activeTab === 'all' ? 'bg-brand-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600'"
                    class="px-4 py-1.5 rounded-full transition-all text-xs">
                {{ __('All') }}
            </button>
            <button type="button"
                    @click="activeTab = 'expense'"
                    hx-get="{{ route('dashboard.recent_transactions', ['type' => 'expense']) }}"
                    hx-target="#transaction-feed-container"
                    :class="activeTab === 'expense' ? 'bg-gradient-to-r from-rose-500 to-amber-500 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600'"
                    class="px-4 py-1.5 rounded-full transition-all text-xs">
                {{ __('Expenses') }}
            </button>
            <button type="button"
                    @click="activeTab = 'income'"
                    hx-get="{{ route('dashboard.recent_transactions', ['type' => 'income']) }}"
                    hx-target="#transaction-feed-container"
                    :class="activeTab === 'income' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600'"
                    class="px-4 py-1.5 rounded-full transition-all text-xs">
                {{ __('Income') }}
            </button>
        </div>
    </div>

    <!-- Reactive Transaction Feed (Auto-refreshes on transaction change) -->
    <div id="transaction-feed-container"
         hx-get="{{ route('dashboard.recent_transactions') }}"
         hx-trigger="transactionCreated from:window, transactionUpdated from:window, transactionDeleted from:window"
         hx-swap="innerHTML">
        @include('dashboard.partials.transaction_list', ['transactions' => $summary['recent_transactions']])
    </div>
</div>
