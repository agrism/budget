@php
    $sym = $data['currency_symbol'] ?? '€';
    $type = $data['type'] ?? 'expense';
@endphp
<div hx-get="{{ route('analytics.index', ['month' => $data['month'], 'type' => $type]) }}"
     hx-trigger="transactionCreated from:window, transactionUpdated from:window, transactionDeleted from:window, categoryCreated from:window, categoryUpdated from:window, categoryDeleted from:window, budgetUpdated from:window"
     hx-swap="innerHTML">
    
    <!-- Top Header & Type Switcher -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('Spending Analytics') }}</h1>
        
        <!-- Type Pill Selector (Expenses / Income) -->
        <div class="flex items-center bg-slate-200/80 dark:bg-[#141926] p-0.5 rounded-xl border border-slate-300 dark:border-[#232d42]">
            <button hx-get="{{ route('analytics.index', ['month' => $data['month'], 'type' => 'expense']) }}"
                    hx-target="#app-content"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $type === 'expense' ? 'bg-gradient-to-r from-rose-500 to-amber-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                {{ __('Expenses') }}
            </button>
            <button hx-get="{{ route('analytics.index', ['month' => $data['month'], 'type' => 'income']) }}"
                    hx-target="#app-content"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $type === 'income' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                {{ __('Income') }}
            </button>
        </div>
    </div>

    <!-- Month Navigation Bar -->
    <div class="app-card rounded-2xl p-1.5 flex items-center justify-between mb-5 border border-slate-200 dark:border-slate-800">
        <button hx-get="{{ route('analytics.index', ['month' => $data['prev_month'], 'type' => $type]) }}"
                hx-target="#app-content"
                class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <span class="text-xs font-bold tracking-wide px-3 py-1 bg-brand-50 dark:bg-brand-500/10 border border-brand-200 dark:border-brand-500/20 rounded-xl text-brand-700 dark:text-brand-300">
            {{ $data['formatted_month'] }}
        </span>

        <button hx-get="{{ route('analytics.index', ['month' => $data['next_month'], 'type' => $type]) }}"
                hx-target="#app-content"
                class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Category Distribution Visual Donut Card (Multi-Category Color Ring) -->
    <div class="app-card-glow rounded-3xl p-5 mb-5 relative overflow-hidden"
         x-data="{ activeSlice: null }">
        <div class="flex justify-between items-center mb-3">
            <div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
                    {{ $type === 'expense' ? __('Expense Categories') : __('Income Categories') }}
                </span>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-0.5">{{ __('Category Distribution') }}</h3>
            </div>
            <div class="text-right">
                <span class="text-xs font-black text-slate-900 dark:text-white">{{ $sym }}{{ number_format($data['total_spent'], 2) }}</span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ $data['active_category_count'] }} {{ __('Categories with activity') }}</span>
            </div>
        </div>

        @if($data['total_spent'] > 0 && count($data['donut_segments']) > 0)
            <!-- Multi-Segment Animated SVG Donut Chart -->
            <div class="flex flex-col sm:flex-row items-center justify-around gap-4 my-2">
                <div class="relative w-36 h-36 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 40 40">
                        <!-- Background Base Ring -->
                        <circle cx="20" cy="20" r="15.9155"
                                class="text-slate-100 dark:text-slate-800/80"
                                stroke-width="5"
                                stroke="currentColor"
                                fill="none" />

                        <!-- Segment Slices for Every Category (including manual ones) -->
                        @foreach($data['donut_segments'] as $seg)
                            <circle cx="20" cy="20" r="15.9155"
                                    stroke="{{ $seg['color'] }}"
                                    stroke-width="5"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ $seg['dasharray'] }}"
                                    stroke-dashoffset="{{ $seg['dashoffset'] }}"
                                    fill="none"
                                    class="transition-all duration-700 hover:stroke-[6] cursor-pointer"
                                    @mouseenter="activeSlice = '{{ $seg['name'] }}: {{ $sym }}{{ number_format($seg['amount'], 2) }} ({{ $seg['percentage'] }}%)'"
                                    @mouseleave="activeSlice = null"
                                    @click="activeSlice = '{{ $seg['name'] }}: {{ $sym }}{{ number_format($seg['amount'], 2) }} ({{ $seg['percentage'] }}%)'">
                                <title>{{ $seg['name'] }}: {{ $sym }}{{ number_format($seg['amount'], 2) }} ({{ $seg['percentage'] }}%)</title>
                            </circle>
                        @endforeach
                    </svg>

                    <!-- Center Stats / Interactive Hover Info -->
                    <div class="absolute text-center flex flex-col items-center justify-center px-2 pointer-events-none">
                        <template x-if="activeSlice">
                            <span class="text-[10px] font-bold text-brand-600 dark:text-brand-400 max-w-[90px] leading-tight" x-text="activeSlice"></span>
                        </template>
                        <template x-if="!activeSlice">
                            <div>
                                <span class="text-xs font-black text-slate-900 dark:text-white block">{{ $sym }}{{ number_format($data['total_spent'], 0) }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight block">{{ __('Total') }}</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Color Legend Pills -->
                <div class="grid grid-cols-2 gap-1.5 w-full sm:w-auto">
                    @foreach(array_slice($data['donut_segments'], 0, 6) as $seg)
                        <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 text-[10px]">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $seg['color'] }};"></span>
                            <span class="font-medium text-slate-700 dark:text-slate-300 truncate max-w-[70px]">{{ $seg['name'] }}</span>
                            <span class="font-bold text-slate-900 dark:text-white ml-auto">{{ $seg['percentage'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Empty state for selected month/type -->
            <div class="py-8 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800/60 text-slate-400 mx-auto flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                    </svg>
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    {{ $type === 'expense' ? __('No spending recorded in this period') : __('No income recorded in this period') }}
                </p>
            </div>
        @endif
    </div>

    <!-- 6-Month Income vs Expense Trend Card -->
    <div class="app-card-glow rounded-3xl p-5 mb-5">
        <div class="flex justify-between items-center mb-4">
            <div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">{{ __('6-Month Trend') }}</span>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-0.5">{{ __('Cash Flow History') }}</h3>
            </div>
            <div class="flex items-center gap-3 text-[10px]">
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ __('Income') }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ __('Expenses') }}</span>
                </div>
            </div>
        </div>

        <!-- Trend Bar Chart -->
        <div class="flex items-end justify-between gap-2 h-36 pt-4 px-1">
            @php
                $maxVal = 1;
                foreach($data['monthly_trends'] as $t) {
                    $maxVal = max($maxVal, $t['income'], $t['expense']);
                }
            @endphp
            @foreach($data['monthly_trends'] as $trend)
                <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                    <div class="w-full flex items-end justify-center gap-1 h-28">
                        <!-- Income bar -->
                        <div class="w-2.5 bg-gradient-to-t from-emerald-600 to-emerald-400 rounded-t-md transition-all duration-700 shadow-sm"
                             style="height: {{ max(6, round(($trend['income'] / $maxVal) * 100)) }}%;"
                             title="{{ __('Income') }}: {{ $sym }}{{ number_format($trend['income'], 0) }}">
                        </div>
                        <!-- Expense bar -->
                        <div class="w-2.5 bg-gradient-to-t from-rose-600 to-rose-400 rounded-t-md transition-all duration-700 shadow-sm"
                             style="height: {{ max(6, round(($trend['expense'] / $maxVal) * 100)) }}%;"
                             title="{{ __('Expenses') }}: {{ $sym }}{{ number_format($trend['expense'], 0) }}">
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 {{ $trend['month'] === $data['month'] ? 'text-brand-600 dark:text-brand-400 underline font-black' : '' }}">{{ $trend['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Spending Breakdown by Category (All Categories: Seeded & Manually Created) -->
    <div class="app-card rounded-3xl p-5 mb-4 border border-slate-200 dark:border-slate-800">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Category Breakdown') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('categories.index') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                    {{ __('Manage') }}
                </a>
                <button @click="modalOpen = true"
                        hx-get="{{ route('categories.create', ['type' => $type]) }}"
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

        <div class="space-y-4">
            @forelse($data['category_breakdown'] as $cat)
                <div class="p-3 rounded-2xl bg-slate-50/50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 transition-all hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="flex justify-between items-center text-xs mb-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                                 style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }};">
                                <x-category-icon :icon="$cat['icon']" class="w-4 h-4" />
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block">{{ $cat['name'] }}</span>
                                @if($cat['limit'] > 0)
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                        {{ __('Budget') }}: {{ $sym }}{{ number_format($cat['limit'], 0) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="flex items-center gap-1.5 justify-end">
                                <span class="font-extrabold text-slate-900 dark:text-white">{{ $sym }}{{ number_format($cat['total'], 2) }}</span>
                                <span class="text-[11px] font-bold px-1.5 py-0.5 rounded-md text-slate-600 dark:text-slate-300 bg-slate-200/60 dark:bg-slate-800">
                                    {{ $cat['pct_of_total'] }}%
                                </span>
                            </div>

                            @if($cat['is_over'])
                                <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400">
                                    {{ __('Over budget') }} (+{{ $sym }}{{ number_format($cat['total'] - $cat['limit'], 2) }})
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Visual Segment Progress Bar (Colored by category color) -->
                    <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700 {{ $cat['is_over'] ? 'bg-rose-500' : '' }}"
                             style="width: {{ $cat['limit'] > 0 ? min(100, $cat['pct_of_limit']) : min(100, $cat['pct_of_total']) }}%; background-color: {{ $cat['is_over'] ? '' : $cat['color'] }};">
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-500 text-center py-4">{{ __('No categories found') }}</p>
            @endforelse
        </div>
    </div>
</div>
