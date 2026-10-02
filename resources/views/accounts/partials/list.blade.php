<div>
    <!-- Top Bar -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('Manage Accounts') }}</h1>
        <button @click="modalOpen = true"
                hx-get="{{ route('accounts.create') }}"
                hx-target="#modal-body"
                hx-swap="innerHTML"
                class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold flex items-center gap-1 transition-all shadow-md">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ __('Add Account') }}</span>
        </button>
    </div>

    <!-- Accounts List Cards -->
    <div class="space-y-3">
        @forelse($accounts as $acc)
            <a href="{{ route('accounts.show', $acc) }}"
               class="block app-card-glow rounded-3xl p-4 transition-all hover:border-slate-300 dark:hover:border-slate-600/60 relative overflow-hidden cursor-pointer active:scale-98 group">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-md flex-shrink-0"
                             style="background-color: {{ $acc->color }}25; color: {{ $acc->color }};">
                            @if($acc->type === 'cash')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            @elseif($acc->type === 'savings')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($acc->type === 'credit')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ $acc->name }}</span>
                            </h3>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 capitalize">{{ $acc->type }} • {{ $acc->transactions_count }} {{ __('Recent Transactions') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <span class="text-base font-extrabold text-slate-900 dark:text-white">
                                {{ $acc->currency === 'USD' ? '$' : '€' }}{{ number_format($acc->balance, 2) }}
                            </span>
                        </div>

                        <!-- Open Chevron -->
                        <div class="text-slate-400 dark:text-slate-500 group-hover:text-brand-500 dark:group-hover:text-brand-400 transition-colors pl-1">
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="app-card rounded-2xl p-6 text-center text-slate-500 dark:text-slate-400">
                <p class="text-xs">No accounts found.</p>
            </div>
        @endforelse
    </div>
</div>
