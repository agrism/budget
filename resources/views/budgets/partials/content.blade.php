@php
    $sym = $data['currency_symbol'] ?? '€';
@endphp
<div>
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('Budgets & Analytics') }}</h1>
        <div class="flex items-center gap-2">
            <span class="text-xs text-brand-700 dark:text-brand-300 font-bold px-2 py-0.5 rounded-lg bg-brand-50 dark:bg-brand-500/10 border border-brand-200 dark:border-brand-500/20">
                {{ $data['currency'] ?? 'EUR' }} ({{ $sym }})
            </span>
        </div>
    </div>

    <!-- Month Navigation Bar -->
    <div class="app-card rounded-2xl p-1.5 flex items-center justify-between mb-5 border border-slate-200 dark:border-slate-800">
        <button hx-get="{{ route('budgets.index', ['month' => $data['prev_month']]) }}"
                hx-target="#app-content"
                class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <span class="text-xs font-bold tracking-wide px-3 py-1 bg-brand-50 dark:bg-brand-500/10 border border-brand-200 dark:border-brand-500/20 rounded-xl text-brand-700 dark:text-brand-300">
            {{ $data['formatted_month'] }}
        </span>

        <button hx-get="{{ route('budgets.index', ['month' => $data['next_month']]) }}"
                hx-target="#app-content"
                class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Budget Overview & Circular Segment Donut Card -->
    <div class="app-card-glow rounded-3xl p-5 mb-5 relative overflow-hidden flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Monthly Budget') }}</span>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ $sym }}{{ number_format($data['total_limit'], 2) }}
            </div>
            <div class="text-xs text-slate-600 dark:text-slate-300 pt-1">
                {{ __('Spent') }}: <strong class="text-brand-700 dark:text-brand-300">{{ $sym }}{{ number_format($data['total_spent'], 2) }}</strong> 
                <span class="text-slate-500 dark:text-slate-400">({{ $data['overall_percentage'] }}%)</span>
            </div>
        </div>

        <!-- Donut Progress Ring -->
        <div class="relative w-24 h-24 flex-shrink-0 flex items-center justify-center">
            <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                <path class="text-slate-200 dark:text-slate-800"
                      stroke-width="3.5"
                      stroke="currentColor"
                      fill="none"
                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="text-brand-400 transition-all duration-1000"
                      stroke-dasharray="{{ min(100, $data['overall_percentage']) }}, 100"
                      stroke-width="3.5"
                      stroke-linecap="round"
                      stroke="url(#budgetGradient)"
                      fill="none"
                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <defs>
                    <linearGradient id="budgetGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#10b981" />
                        <stop offset="50%" stop-color="#6366f1" />
                        <stop offset="100%" stop-color="#ec4899" />
                    </linearGradient>
                </defs>
            </svg>
            <div class="absolute text-center flex flex-col items-center">
                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $sym }}{{ number_format($data['total_spent'], 0) }}</span>
                <span class="text-[9px] text-slate-500 dark:text-slate-400 uppercase tracking-tight">{{ __('Spent') }}</span>
            </div>
        </div>
    </div>

    <!-- Daily Allowance & Savings Target Banner -->
    <div class="app-card rounded-2xl p-3.5 mb-5 border border-emerald-500/20 bg-gradient-to-r from-emerald-50/30 to-teal-50/20 dark:from-[#0f172a] dark:to-[#0c1322] flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">{{ __('Daily Limit') }}</span>
                <span class="text-sm font-black text-slate-900 dark:text-white">{{ $sym }}{{ number_format($data['daily_spending_limit'], 2) }} / {{ __('day') }}</span>
            </div>
        </div>

        <button type="button"
                @click="modalOpen = true"
                hx-get="{{ route('savings.modal') }}"
                hx-target="#modal-body"
                class="px-2.5 py-1 rounded-xl bg-emerald-100/80 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-[11px] font-bold hover:scale-105 transition-all flex items-center gap-1 shadow-sm">
            <span>🎯 {{ $data['savings_target_percentage'] }}% {{ __('Savings') }}</span>
            <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
        </button>
    </div>

    <!-- Category Budget Cards List -->
    <div class="space-y-3"
         hx-get="{{ route('budgets.index', ['month' => $data['month']]) }}"
         hx-trigger="budgetUpdated from:window, categoryCreated from:window, categoryUpdated from:window, categoryDeleted from:window, savingsTargetUpdated from:window, transactionCreated from:window, transactionUpdated from:window, transactionDeleted from:window"
         hx-swap="innerHTML">
        
        <div class="flex items-center justify-between px-1">
            <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Category Budgets') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('categories.index') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                    {{ __('Manage') }}
                </a>
                <button @click="modalOpen = true"
                        hx-get="{{ route('categories.create', ['type' => 'expense']) }}"
                        hx-target="#modal-body"
                        hx-swap="innerHTML"
                        class="px-2.5 py-1 rounded-lg bg-brand-50 dark:bg-brand-500/10 border border-brand-200 dark:border-brand-500/20 text-brand-700 dark:text-brand-300 text-[11px] font-bold flex items-center gap-1 hover:bg-brand-100 dark:hover:bg-brand-500/20 transition-all">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ __('Add Category') }}</span>
                </button>
            </div>
        </div>

        @foreach($data['category_budgets'] as $cat)
            <div class="app-card rounded-2xl p-4 transition-all hover:border-slate-300 dark:hover:border-slate-700/80"
                 x-data="{ editing: false, newLimit: '{{ $cat['limit'] }}' }">
                
                <div class="flex items-center justify-between mb-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }};">
                            <x-category-icon :icon="$cat['icon']" class="w-4 h-4" />
                        </div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $cat['name'] }}</span>
                    </div>

                    <div>
                        @if($cat['is_over'])
                            <span class="px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30 text-[10px] font-bold">
                                {{ __('Over budget') }}
                            </span>
                        @else
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $sym }}{{ number_format($cat['spent'], 0) }} / {{ $sym }}{{ number_format($cat['limit'], 0) }} 
                                <span class="text-slate-500 dark:text-slate-400">({{ $cat['pct_of_budget'] }}%)</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-slate-200 dark:bg-slate-800/80 rounded-full h-2 overflow-hidden mb-2">
                    <div class="h-full rounded-full transition-all duration-500 {{ $cat['is_over'] ? 'bg-rose-500' : 'bg-gradient-to-r from-emerald-400 to-teal-400' }}"
                         style="width: {{ min(100, $cat['pct_of_budget']) }}%;">
                    </div>
                </div>

                <!-- Footer info & edit button -->
                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                    <span>
                        @if($cat['is_over'])
                            <span class="text-rose-600 dark:text-rose-400 font-semibold">{{ $sym }}{{ number_format($cat['spent'] - $cat['limit'], 2) }} {{ __('over limit') }}</span>
                        @else
                            <span>{{ $sym }}{{ number_format($cat['remaining'], 2) }} {{ __('left') }}</span>
                        @endif
                    </span>

                    <div class="flex items-center gap-3">
                        <button @click="modalOpen = true"
                                hx-get="{{ route('categories.edit', $cat['id']) }}"
                                hx-target="#modal-body"
                                hx-swap="innerHTML"
                                class="text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 font-medium">
                            {{ __('Edit') }}
                        </button>
                        <button @click="editing = !editing" class="text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium">
                            <span x-text="editing ? '{{ __('Cancel') }}' : '{{ __('Edit Limit') }}'"></span>
                        </button>
                    </div>
                </div>

                <!-- Inline Quick Edit Form -->
                <div x-show="editing" class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-800" style="display: none;">
                    <form hx-post="{{ route('budgets.update') }}"
                          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                          class="flex items-center gap-2">
                        <input type="hidden" name="category_id" value="{{ $cat['id'] }}">
                        <input type="hidden" name="period_month" value="{{ $data['month'] }}">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-2 text-xs text-slate-400">{{ $sym }}</span>
                            <input type="number" 
                                   step="1" 
                                   min="0" 
                                   name="monthly_limit" 
                                   x-model="newLimit" 
                                   class="w-full bg-slate-50 dark:bg-[#181f2e] border border-slate-200 dark:border-[#2a3449] rounded-xl py-1.5 pl-6 pr-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                        <button type="submit" 
                                @click="editing = false"
                                class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold">
                            {{ __('Save') }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
