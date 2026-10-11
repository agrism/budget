<div class="space-y-2.5">
    @forelse($transactions as $tx)
        @php
            $txCurrency = $tx->account?->currency ?? 'EUR';
            $txSymbol = $txCurrency === 'USD' ? '$' : '€';
        @endphp
        <div id="tx-row-{{ $tx->id }}" 
             @click="modalOpen = true"
             hx-get="{{ route('transactions.edit', $tx->id) }}"
             hx-target="#modal-body"
             hx-swap="innerHTML"
             class="app-card rounded-2xl p-3.5 flex items-center justify-between transition-all hover:border-slate-300 dark:hover:border-slate-700 group cursor-pointer">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Icon -->
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                     style="background-color: {{ $tx->category ? $tx->category->color : '#6366f1' }}20; color: {{ $tx->category ? $tx->category->color : '#4f46e5' }};">
                    @if($tx->type === 'income')
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8l-8-8-8 8" />
                        </svg>
                    @elseif($tx->category)
                        <x-category-icon :icon="$tx->category->icon" class="w-5 h-5" />
                    @else
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    @endif
                </div>

                <!-- Info -->
                <div class="truncate">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                        {{ $tx->note ?: ($tx->category ? $tx->category->name : 'Transaction') }}
                    </h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        @if($tx->category)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                  style="background-color: {{ $tx->category->color }}18; color: {{ $tx->category->color }};">
                                {{ $tx->category->name }}
                            </span>
                        @endif
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $tx->formatted_date }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Amount & Arrow Chevron -->
            <div class="flex items-center gap-2 text-right flex-shrink-0">
                <div>
                    <span class="text-sm font-black {{ $tx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-slate-100' }}">
                        {{ $tx->type === 'income' ? '+' : '-' }}{{ $txSymbol }}{{ number_format($tx->amount, 2) }}
                    </span>
                    <div class="text-[10px] text-slate-500 dark:text-slate-400">
                        {{ $tx->account ? $tx->account->name : 'Account' }}
                    </div>
                </div>

                <!-- Open / Detail chevron arrow -->
                <div class="text-slate-400 dark:text-slate-500 group-hover:text-brand-500 dark:group-hover:text-brand-400 transition-colors pl-1">
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </div>
    @empty
        <div class="app-card rounded-2xl p-8 text-center text-slate-500 dark:text-slate-400">
            <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            @if(isset($type) && $type === 'income')
                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ __('No income recorded in this period') }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">{{ __('You have not recorded any income transactions yet.') }}</p>
                <button @click="modalOpen = true"
                        hx-get="{{ route('transactions.create', ['type' => 'income']) }}"
                        hx-target="#modal-body"
                        hx-swap="innerHTML"
                        class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-xs font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 hover:bg-emerald-100 transition-colors">
                    + {{ __('Add income') }}
                </button>
            @elseif(isset($type) && $type === 'expense')
                <p class="text-sm font-bold text-slate-800 dark:text-white">{{ __('No transactions found') }}</p>
                <button @click="modalOpen = true"
                        hx-get="{{ route('transactions.create', ['type' => 'expense']) }}"
                        hx-target="#modal-body"
                        hx-swap="innerHTML"
                        class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-rose-50 dark:bg-rose-500/10 text-xs font-bold text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 hover:bg-rose-100 transition-colors">
                    + {{ __('Add expense') }}
                </button>
            @else
                <p class="text-sm font-medium">{{ __('No transactions found') }}</p>
                <button @click="modalOpen = true"
                        hx-get="{{ route('transactions.create') }}"
                        hx-target="#modal-body"
                        hx-swap="innerHTML"
                        class="mt-3 inline-flex items-center text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    + {{ __('Add your first transaction') }}
                </button>
            @endif
        </div>
    @endforelse
</div>
