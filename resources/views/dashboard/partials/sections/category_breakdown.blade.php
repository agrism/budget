@php
    $sym = $summary['currency_symbol'] ?? '€';
    $breakdownCategories = collect($summary['categories'] ?? [])
        ->sortByDesc(fn ($c) => $c['spent'])
        ->values();
@endphp

<!-- Category Breakdown Section -->
<div class="mb-5">
    <!-- Header with Title, Manage link, Add Category button -->
    <div class="flex items-center justify-between mb-3 px-1">
        <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
            {{ __('Category Breakdown') }}
        </h2>
        <div class="flex items-center gap-2.5 sm:gap-3">
            <a href="{{ route('categories.index') }}" 
               onclick="window.showPageLoader('{{ __('Categories') }}')"
               class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 hover:underline transition-colors">
                {{ __('Manage') }}
            </a>
            <button type="button"
                    @click="modalOpen = true"
                    hx-get="{{ route('categories.create', ['type' => 'expense']) }}"
                    hx-target="#modal-body"
                    hx-swap="innerHTML"
                    class="px-2.5 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 dark:bg-brand-500/10 dark:hover:bg-brand-500/20 text-brand-700 dark:text-brand-300 text-xs font-semibold flex items-center gap-1 transition-all active:scale-95 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Add Category') }}</span>
            </button>
        </div>
    </div>

    <!-- Category Cards List -->
    <div class="space-y-2.5">
        @forelse($breakdownCategories as $cat)
            @php
                $barWidth = $cat['limit'] > 0 
                    ? max(0, min(100, $cat['percentage']))
                    : max(0, min(100, $cat['share_percentage']));
            @endphp
            <a href="{{ route('categories.show', $cat['id']) }}"
               onclick="window.showPageLoader('{{ addslashes($cat['name']) }}')"
               data-loader-title="{{ $cat['name'] }}"
               class="app-card rounded-2xl p-3.5 block transition-all active:scale-[0.99] hover:border-slate-300 dark:hover:border-slate-700 cursor-pointer shadow-xs">
                
                <div class="flex items-center justify-between gap-3">
                    <!-- Left: Category Icon + Name & Optional Budget -->
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-xs"
                             style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }};">
                            <x-category-icon :icon="$cat['icon']" class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm font-bold text-slate-900 dark:text-white truncate block">
                                {{ $cat['name'] }}
                            </span>
                            @if(!empty($cat['limit']) && $cat['limit'] > 0)
                                <span class="text-xs text-slate-400 dark:text-slate-500 block truncate mt-0.5">
                                    {{ __('Budget') }}: {{ $sym }}{{ $cat['limit'] == round($cat['limit']) ? number_format($cat['limit'], 0) : number_format($cat['limit'], 2) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Amount + Percentage Badge -->
                    <div class="flex items-center gap-2 flex-shrink-0 text-right">
                        <span class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                            {{ $sym }}{{ number_format($cat['spent'], 2) }}
                        </span>
                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold">
                            {{ $cat['share_percentage'] > 0 ? number_format($cat['share_percentage'], 1) : '0' }}%
                        </span>
                    </div>
                </div>

                <!-- Progress Bar Track -->
                <div class="mt-3 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500"
                         style="width: {{ $barWidth }}%; background-color: {{ $cat['color'] }};">
                    </div>
                </div>
            </a>
        @empty
            <div class="app-card rounded-2xl p-6 text-center text-slate-500 dark:text-slate-400">
                <p class="text-xs font-medium">{{ __('No categories found') }}</p>
                <button type="button"
                        @click="modalOpen = true"
                        hx-get="{{ route('categories.create', ['type' => 'expense']) }}"
                        hx-target="#modal-body"
                        hx-swap="innerHTML"
                        class="mt-2 inline-flex items-center text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    + {{ __('Add your first category') }}
                </button>
            </div>
        @endforelse
    </div>
</div>
