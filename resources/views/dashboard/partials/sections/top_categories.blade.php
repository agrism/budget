@php
    $sym = $summary['currency_symbol'] ?? '€';
@endphp
<!-- Horizontal Categories Spending Scroll -->
<div class="mb-5">
    <div class="flex justify-between items-center mb-2 px-1">
        <h2 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Top Categories') }}</h2>
        <a href="{{ route('categories.index') }}" 
           onclick="window.showPageLoader('{{ __('Categories') }}')"
           class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-bold">{{ __('Manage') }}</a>
    </div>

    <div class="flex gap-2.5 overflow-x-auto no-scrollbar pb-1">
        @foreach($summary['categories'] as $cat)
            <a href="{{ route('categories.show', $cat['id']) }}" 
               x-data="{ loading: false }"
               @click="loading = true; window.showPageLoader('{{ addslashes($cat['name']) }}')"
               data-loader-title="{{ $cat['name'] }}"
               class="app-card flex-shrink-0 w-28 rounded-2xl p-3 flex flex-col items-center text-center transition-all active:scale-95 hover:border-slate-300 dark:hover:border-slate-700 block cursor-pointer relative"
               :class="{ 'border-brand-500/80 ring-2 ring-brand-500/30 dark:ring-brand-500/20 shadow-md': loading }">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-2 shadow-sm transition-transform"
                     :class="{ 'scale-105': loading }"
                     style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }};">
                    <template x-if="!loading">
                        <x-category-icon :icon="$cat['icon']" class="w-5 h-5" />
                    </template>
                    <template x-if="loading">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                </div>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate w-full">{{ $cat['name'] }}</span>
                <span class="text-xs font-bold text-slate-900 dark:text-white mt-0.5"
                      x-text="loading ? '{{ __('Loading...') }}' : '{{ $sym }}{{ number_format($cat['spent'], 0) }}'">
                    {{ $sym }}{{ number_format($cat['spent'], 0) }}
                </span>
            </a>
        @endforeach
    </div>
</div>
