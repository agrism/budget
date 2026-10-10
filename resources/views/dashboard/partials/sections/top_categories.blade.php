@php
    $sym = $summary['currency_symbol'] ?? '€';
@endphp
<!-- Horizontal Categories Spending Scroll -->
<div class="mb-5">
    <div class="flex justify-between items-center mb-2 px-1">
        <h2 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Top Categories') }}</h2>
        <a href="{{ route('categories.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-bold">{{ __('Manage') }}</a>
    </div>

    <div class="flex gap-2.5 overflow-x-auto no-scrollbar pb-1">
        @foreach($summary['categories'] as $cat)
            <a href="{{ route('categories.show', $cat['id']) }}" 
               class="app-card flex-shrink-0 w-28 rounded-2xl p-3 flex flex-col items-center text-center transition-transform active:scale-95 hover:border-slate-300 dark:hover:border-slate-700 block cursor-pointer">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-2 shadow-sm"
                     style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }};">
                    <x-category-icon :icon="$cat['icon']" class="w-5 h-5" />
                </div>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate w-full">{{ $cat['name'] }}</span>
                <span class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ $sym }}{{ number_format($cat['spent'], 0) }}</span>
            </a>
        @endforeach
    </div>
</div>
