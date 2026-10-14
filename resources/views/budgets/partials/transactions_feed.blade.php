@php
    $txSymbol = $currencySymbol ?? '€';
@endphp

@forelse($transactions as $tx)
    @php
        $isIncomingTransfer = ($tx->type === 'transfer' && isset($accountId) && $tx->destination_account_id == $accountId);
        $isOutgoingTransfer = ($tx->type === 'transfer' && isset($accountId) && $tx->account_id == $accountId);
        $isIncome = ($tx->type === 'income' || $isIncomingTransfer);
        
        $title = $tx->note;
        if (!$title) {
            if ($tx->type === 'transfer') {
                $src = $tx->account?->name ?? __('Account');
                $dst = $tx->destinationAccount?->name ?? __('Account');
                $title = $src . ' → ' . $dst;
            } elseif ($tx->category) {
                $title = $tx->category->name;
            } else {
                $title = __('Transaction');
            }
        }

        $itemCurrency = $tx->account?->currency ?? 'EUR';
        $itemSymbol = $itemCurrency === 'USD' ? '$' : '€';
    @endphp
    <div id="all-tx-row-{{ $tx->id }}" 
         @click="modalOpen = true"
         hx-get="{{ route('transactions.edit', $tx->id) }}"
         hx-target="#modal-body"
         hx-swap="innerHTML"
         class="app-card rounded-2xl p-3.5 flex items-center justify-between transition-all hover:border-slate-300 dark:hover:border-slate-700 group cursor-pointer active:scale-[0.99] shadow-xs">
        
        <div class="flex items-center gap-3 min-w-0">
            <!-- Category / Type Icon -->
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-xs"
                 style="background-color: {{ $tx->category ? $tx->category->color : ($tx->type === 'transfer' ? '#6366f1' : '#10b981') }}20; color: {{ $tx->category ? $tx->category->color : ($tx->type === 'transfer' ? '#6366f1' : '#10b981') }};">
                @if($isIncome)
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8l-8-8-8 8" />
                    </svg>
                @elseif($tx->type === 'transfer')
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                @elseif($tx->category)
                    <x-category-icon :icon="$tx->category->icon" class="w-5 h-5" />
                @else
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                @endif
            </div>

            <!-- Title & Info -->
            <div class="truncate">
                <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                    {{ $title }}
                </h4>
                <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                    @if($tx->category && $tx->type !== 'transfer')
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full"
                              style="background-color: {{ $tx->category->color }}18; color: {{ $tx->category->color }};">
                            {{ $tx->category->name }}
                        </span>
                    @endif
                    <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        @if($tx->type === 'transfer')
                            {{ $tx->account?->name ?? __('Account') }} → {{ $tx->destinationAccount?->name ?? __('Account') }}
                        @else
                            {{ $tx->account?->name ?? __('Account') }}
                        @endif
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">
                        {{ $tx->formatted_date }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Amount & Chevron Indicator -->
        <div class="flex items-center gap-2 text-right flex-shrink-0">
            <div>
                <span class="text-xs font-black {{ $tx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : ($tx->type === 'transfer' ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-900 dark:text-slate-100') }}">
                    {{ $tx->type === 'income' ? '+' : ($tx->type === 'transfer' ? '⇄ ' : '-') }}{{ $itemSymbol }}{{ number_format($tx->amount, 2) }}
                </span>
                <div class="text-[9px] text-slate-400 dark:text-slate-500 capitalize">
                    {{ __($tx->type) }}
                </div>
            </div>

            <div class="text-slate-400 dark:text-slate-500 group-hover:text-brand-500 dark:group-hover:text-brand-400 transition-colors pl-0.5">
                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </div>
@empty
    @if($transactions->currentPage() === 1)
        <div class="app-card rounded-2xl p-8 text-center text-slate-500 dark:text-slate-400 shadow-xs">
            <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-xs font-medium">{{ __('No transactions found') }}</p>
            <button type="button"
                    @click="modalOpen = true"
                    hx-get="{{ route('transactions.create') }}"
                    hx-target="#modal-body"
                    hx-swap="innerHTML"
                    class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-brand-50 hover:bg-brand-100 dark:bg-brand-500/10 dark:hover:bg-brand-500/20 text-xs font-bold text-brand-700 dark:text-brand-300 transition-colors">
                + {{ __('Add Transaction') }}
            </button>
        </div>
    @endif
@endforelse

<!-- Pagination / Infinite Scroll Trigger -->
@if($transactions->hasMorePages())
    <div id="all-tx-loader-{{ $transactions->currentPage() + 1 }}"
         hx-get="{{ route('budgets.index', array_merge(request()->query(), ['page' => $transactions->currentPage() + 1, 'feed_only' => 1])) }}"
         hx-trigger="revealed"
         hx-swap="outerHTML"
         class="py-3 text-center">
        <div class="inline-flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 font-medium">
            <svg class="animate-spin h-3.5 w-3.5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>{{ __('Loading more...') }}</span>
        </div>
    </div>
@elseif($transactions->total() > 10)
    <div class="py-2.5 text-center text-[10px] text-slate-400 dark:text-slate-500">
        {{ __('All transactions loaded') }} ({{ $transactions->total() }})
    </div>
@endif
