<div x-data="{ filterType: 'all' }">
    <!-- Top Header & Add Button -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('Categories') }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Manage expense & income categories') }}</p>
        </div>
        <button @click="modalOpen = true"
                hx-get="{{ route('categories.create') }}"
                hx-target="#modal-body"
                hx-swap="innerHTML"
                class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold flex items-center gap-1.5 transition-all shadow-md active:scale-95">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ __('Add Category') }}</span>
        </button>
    </div>

    <!-- Filter Pills (All / Expenses / Income) -->
    <div class="flex gap-2 mb-4 bg-slate-100 dark:bg-[#141926] p-1 rounded-2xl border border-slate-200 dark:border-[#232d42]">
        <button type="button" 
                @click="filterType = 'all'"
                :class="filterType === 'all' ? 'bg-white dark:bg-[#1e2638] text-slate-900 dark:text-white font-bold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
            {{ __('All') }} ({{ $categories->count() }})
        </button>
        <button type="button" 
                @click="filterType = 'expense'"
                :class="filterType === 'expense' ? 'bg-white dark:bg-[#1e2638] text-rose-600 dark:text-rose-400 font-bold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
            {{ __('Expenses') }} ({{ $categories->where('type', 'expense')->count() }})
        </button>
        <button type="button" 
                @click="filterType = 'income'"
                :class="filterType === 'income' ? 'bg-white dark:bg-[#1e2638] text-emerald-600 dark:text-emerald-400 font-bold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
            {{ __('Income') }} ({{ $categories->where('type', 'income')->count() }})
        </button>
    </div>

    <!-- Category Grid / List -->
    <div class="space-y-2.5"
         hx-get="{{ route('categories.index') }}"
         hx-trigger="categoryCreated from:window, categoryUpdated from:window, categoryDeleted from:window"
         hx-swap="innerHTML">
        @forelse($categories as $cat)
            @php
                $budget = $cat->budgets->first();
                $limit = $budget ? (float) $budget->monthly_limit : null;
            @endphp
            <div x-show="filterType === 'all' || filterType === '{{ $cat->type }}'"
                 class="app-card rounded-2xl p-3.5 flex items-center justify-between transition-all hover:border-slate-300 dark:hover:border-slate-700 group">
                
                <a href="{{ route('categories.show', $cat) }}" class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer">
                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm transition-transform group-hover:scale-105"
                         style="background-color: {{ $cat->color }}20; color: {{ $cat->color }};">
                        <x-category-icon :icon="$cat->icon" class="w-5 h-5" />
                    </div>

                    <!-- Details -->
                    <div class="truncate">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $cat->name }}</h3>
                            <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md {{ $cat->type === 'income' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20' }}">
                                {{ $cat->type === 'income' ? __('Income') : __('Expense') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                            <span>{{ $cat->transactions_count }} {{ __('Recent Transactions') }}</span>
                            @if($limit !== null && $cat->type === 'expense')
                                <span>•</span>
                                <span class="font-medium text-brand-600 dark:text-brand-400">{{ __('Budget') }}: {{ $currencySymbol }}{{ number_format($limit, 0) }}/mo</span>
                            @endif
                        </div>
                    </div>
                </a>

                <!-- Edit & Open Actions -->
                <div class="flex items-center gap-1 pl-2">
                    <button @click="modalOpen = true"
                            hx-get="{{ route('categories.edit', $cat->id) }}"
                            hx-target="#modal-body"
                            hx-swap="innerHTML"
                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors"
                            title="{{ __('Edit Category') }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <a href="{{ route('categories.show', $cat) }}"
                       class="p-2 rounded-xl text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors"
                       title="{{ __('Category Transactions') }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="app-card rounded-2xl p-8 text-center text-slate-500 dark:text-slate-400">
                <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <p class="text-sm font-medium">{{ __('No categories found') }}</p>
                <button @click="modalOpen = true"
                        hx-get="{{ route('categories.create') }}"
                        hx-target="#modal-body"
                        hx-swap="innerHTML"
                        class="mt-3 inline-flex items-center text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    + {{ __('Add your first category') }}
                </button>
            </div>
        @endforelse
    </div>
</div>
