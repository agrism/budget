<div class="space-y-4 pb-6"
     x-data="{
        activeTab: '{{ $type ?? 'all' }}',
        selectedAccount: '{{ $accountId ?? '' }}',
        selectedCategory: '{{ $categoryId ?? '' }}',
        searchQuery: '{{ $search ?? '' }}'
     }">

    <!-- Top Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                {{ __('All Transactions') }}
            </h1>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                {{ $counts['all'] ?? $transactions->total() }} {{ __('transactions recorded') }}
            </span>
        </div>

        <button type="button"
                @click="modalOpen = true"
                hx-get="{{ route('transactions.create') }}"
                hx-target="#modal-body"
                hx-swap="innerHTML"
                class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm active:scale-95 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ __('Add Transaction') }}</span>
        </button>
    </div>

    <!-- Financial Stats Hero Bar (Income, Expense, Net) -->
    <div class="app-card-glow rounded-3xl p-4 border border-slate-200 dark:border-[#232d42] bg-gradient-to-br from-white via-slate-50 to-slate-100/60 dark:from-[#141926] dark:via-[#10141f] dark:to-[#0c1017] shadow-sm">
        <div class="grid grid-cols-3 gap-2 text-center divide-x divide-slate-200 dark:divide-slate-800">
            <!-- Income -->
            <div class="px-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block truncate">
                    {{ __('Income') }}
                </span>
                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 block truncate mt-0.5">
                    +{{ $currencySymbol }}{{ number_format($totalIncome ?? 0, 2) }}
                </span>
            </div>

            <!-- Expense -->
            <div class="px-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block truncate">
                    {{ __('Expenses') }}
                </span>
                <span class="text-sm font-black text-slate-900 dark:text-white block truncate mt-0.5">
                    -{{ $currencySymbol }}{{ number_format($totalExpense ?? 0, 2) }}
                </span>
            </div>

            <!-- Net Balance -->
            <div class="px-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block truncate">
                    {{ __('Net Balance') }}
                </span>
                <span class="text-sm font-black {{ ($netSavings ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} block truncate mt-0.5">
                    {{ ($netSavings ?? 0) >= 0 ? '+' : '' }}{{ $currencySymbol }}{{ number_format($netSavings ?? 0, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Search & Filters Container -->
    <div class="space-y-2.5">
        <!-- Live Search Bar -->
        <div class="relative">
            <input type="text"
                   name="search"
                   placeholder="{{ __('Search transactions, notes, categories...') }}"
                   value="{{ $search ?? '' }}"
                   hx-get="{{ route('budgets.index') }}"
                   hx-trigger="keyup changed delay:300ms, search"
                   hx-target="#all-transactions-feed"
                   hx-swap="innerHTML"
                   hx-include="#all-tx-type-input, #all-tx-account-input, #all-tx-category-input"
                   hx-indicator="#all-transactions-feed"
                   class="w-full bg-slate-50 dark:bg-[#141926] border border-slate-200 dark:border-[#232d42] rounded-2xl py-2.5 pl-10 pr-4 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-xs">
            <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <input type="hidden" id="all-tx-type-input" name="type" :value="activeTab === 'all' ? '' : activeTab">
        <input type="hidden" id="all-tx-account-input" name="account_id" :value="selectedAccount">
        <input type="hidden" id="all-tx-category-input" name="category_id" :value="selectedCategory">

        <!-- Type Filter Pills -->
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-0.5 text-xs">
            <button type="button"
                    @click="activeTab = 'all'"
                    hx-get="{{ route('budgets.index', ['account_id' => $accountId ?? '', 'category_id' => $categoryId ?? '', 'search' => $search ?? '']) }}"
                    hx-target="#all-transactions-feed"
                    hx-swap="innerHTML"
                    :class="activeTab === 'all' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white'"
                    class="px-3.5 py-1.5 rounded-full transition-all text-xs whitespace-nowrap">
                {{ __('All') }} ({{ $counts['all'] ?? $transactions->total() }})
            </button>
            <button type="button"
                    @click="activeTab = 'expense'"
                    hx-get="{{ route('budgets.index', ['type' => 'expense', 'account_id' => $accountId ?? '', 'category_id' => $categoryId ?? '', 'search' => $search ?? '']) }}"
                    hx-target="#all-transactions-feed"
                    hx-swap="innerHTML"
                    :class="activeTab === 'expense' ? 'bg-gradient-to-r from-rose-500 to-amber-500 text-white font-bold shadow-xs' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white'"
                    class="px-3.5 py-1.5 rounded-full transition-all text-xs whitespace-nowrap">
                {{ __('Expenses') }} ({{ $counts['expense'] ?? 0 }})
            </button>
            <button type="button"
                    @click="activeTab = 'income'"
                    hx-get="{{ route('budgets.index', ['type' => 'income', 'account_id' => $accountId ?? '', 'category_id' => $categoryId ?? '', 'search' => $search ?? '']) }}"
                    hx-target="#all-transactions-feed"
                    hx-swap="innerHTML"
                    :class="activeTab === 'income' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold shadow-xs' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white'"
                    class="px-3.5 py-1.5 rounded-full transition-all text-xs whitespace-nowrap">
                {{ __('Income') }} ({{ $counts['income'] ?? 0 }})
            </button>
            @if(!empty($counts['transfer']))
                <button type="button"
                        @click="activeTab = 'transfer'"
                        hx-get="{{ route('budgets.index', ['type' => 'transfer', 'account_id' => $accountId ?? '', 'category_id' => $categoryId ?? '', 'search' => $search ?? '']) }}"
                        hx-target="#all-transactions-feed"
                        hx-swap="innerHTML"
                        :class="activeTab === 'transfer' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-white dark:bg-[#141926] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#232d42] font-semibold hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-full transition-all text-xs whitespace-nowrap">
                    {{ __('Transfers') }} ({{ $counts['transfer'] }})
                </button>
            @endif
        </div>

        <!-- Accounts Horizontal Filter Pills -->
        @if(isset($accounts) && $accounts->count() > 1)
            <div class="flex gap-1.5 overflow-x-auto no-scrollbar pb-0.5 text-[11px] items-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 mr-1 flex-shrink-0">{{ __('Accounts') }}:</span>
                <button type="button"
                        @click="selectedAccount = ''"
                        hx-get="{{ route('budgets.index', ['type' => $type ?? '', 'category_id' => $categoryId ?? '', 'search' => $search ?? '']) }}"
                        hx-target="#all-transactions-feed"
                        hx-swap="innerHTML"
                        :class="selectedAccount === '' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-2.5 py-1 rounded-xl transition-all whitespace-nowrap flex-shrink-0">
                    {{ __('All Accounts') }}
                </button>
                @foreach($accounts as $acc)
                    <button type="button"
                            @click="selectedAccount = '{{ $acc->id }}'"
                            hx-get="{{ route('budgets.index', ['account_id' => $acc->id, 'type' => $type ?? '', 'category_id' => $categoryId ?? '', 'search' => $search ?? '']) }}"
                            hx-target="#all-transactions-feed"
                            hx-swap="innerHTML"
                            :class="selectedAccount === '{{ $acc->id }}' ? 'border-2 border-brand-500 font-bold' : 'border border-slate-200 dark:border-slate-800'"
                            class="px-2.5 py-1 rounded-xl bg-white dark:bg-[#141926] text-slate-700 dark:text-slate-300 transition-all whitespace-nowrap flex items-center gap-1.5 flex-shrink-0">
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $acc->color }};"></span>
                        <span>{{ $acc->name }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <!-- Categories Horizontal Filter Pills -->
        @if(isset($categories) && $categories->count() > 0)
            <div class="flex gap-1.5 overflow-x-auto no-scrollbar pb-1 text-[11px] items-center">
                <span class="text-[10px] uppercase font-bold text-slate-400 mr-1 flex-shrink-0">{{ __('Categories') }}:</span>
                <button type="button"
                        @click="selectedCategory = ''"
                        hx-get="{{ route('budgets.index', ['type' => $type ?? '', 'account_id' => $accountId ?? '', 'search' => $search ?? '']) }}"
                        hx-target="#all-transactions-feed"
                        hx-swap="innerHTML"
                        :class="selectedCategory === '' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-2.5 py-1 rounded-xl transition-all whitespace-nowrap flex-shrink-0">
                    {{ __('All Categories') }}
                </button>
                @foreach($categories as $cat)
                    <button type="button"
                            @click="selectedCategory = '{{ $cat->id }}'"
                            hx-get="{{ route('budgets.index', ['category_id' => $cat->id, 'account_id' => $accountId ?? '', 'type' => $type ?? '', 'search' => $search ?? '']) }}"
                            hx-target="#all-transactions-feed"
                            hx-swap="innerHTML"
                            :class="selectedCategory === '{{ $cat->id }}' ? 'border-2 border-brand-500 font-bold bg-brand-50/50 dark:bg-brand-500/10' : 'border border-slate-200 dark:border-slate-800'"
                            class="px-2.5 py-1 rounded-xl bg-white dark:bg-[#141926] text-slate-700 dark:text-slate-300 transition-all whitespace-nowrap flex items-center gap-1.5 flex-shrink-0">
                        <div class="w-3.5 h-3.5 rounded flex items-center justify-center flex-shrink-0"
                             style="color: {{ $cat->color }};">
                            <x-category-icon :icon="$cat->icon" class="w-3.5 h-3.5" />
                        </div>
                        <span>{{ $cat->name }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Feed Container -->
    <div id="all-transactions-feed"
         class="space-y-2.5 transition-opacity duration-150 [&.htmx-request]:opacity-50"
         hx-get="{{ route('budgets.index', ['feed_only' => 1, 'type' => $type ?? '', 'account_id' => $accountId ?? '', 'category_id' => $categoryId ?? '', 'search' => $search ?? '']) }}"
         hx-trigger="transactionCreated from:window, transactionUpdated from:window, transactionDeleted from:window"
         hx-swap="innerHTML">
        @include('budgets.partials.transactions_feed', ['transactions' => $transactions, 'currencySymbol' => $currencySymbol, 'accountId' => $accountId ?? null, 'categoryId' => $categoryId ?? null])
    </div>
</div>
