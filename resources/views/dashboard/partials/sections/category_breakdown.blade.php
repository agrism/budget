@php
    $sym = $summary['currency_symbol'] ?? '€';
    $breakdownCategories = collect($summary['categories'] ?? []);
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

    <!-- Category Budget Cards List -->
    <div class="space-y-3">
        @forelse($breakdownCategories as $cat)
            <div class="app-card rounded-2xl p-4 transition-all hover:border-slate-300 dark:hover:border-slate-700/80 shadow-xs"
                 x-data="{ editing: false, newLimit: '{{ $cat['limit'] }}' }">
                
                <a href="{{ route('categories.show', $cat['id']) }}"
                   onclick="window.showPageLoader('{{ addslashes($cat['name']) }}')"
                   data-loader-title="{{ $cat['name'] }}"
                   class="block cursor-pointer group">
                    <div class="flex items-center justify-between mb-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 shadow-xs transition-transform group-hover:scale-105"
                                 style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }};">
                                <x-category-icon :icon="$cat['icon']" class="w-4 h-4" />
                            </div>
                            <span class="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                                {{ $cat['name'] }}
                            </span>
                        </div>

                        <div class="flex-shrink-0">
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
                </a>

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
                        <button type="button"
                                @click="modalOpen = true"
                                hx-get="{{ route('categories.edit', $cat['id']) }}"
                                hx-target="#modal-body"
                                hx-swap="innerHTML"
                                class="text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 font-medium transition-colors">
                            {{ __('Edit') }}
                        </button>
                        <button type="button"
                                @click="editing = !editing" 
                                class="text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium transition-colors">
                            <span x-text="editing ? '{{ __('Cancel') }}' : '{{ __('Edit Limit') }}'"></span>
                        </button>
                    </div>
                </div>

                <!-- Inline Quick Edit Form -->
                <div x-show="editing" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-800" 
                     style="display: none;">
                    <form hx-post="{{ route('budgets.update') }}"
                          hx-swap="none"
                          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                          class="flex items-center gap-2">
                        <input type="hidden" name="category_id" value="{{ $cat['id'] }}">
                        <input type="hidden" name="period_month" value="{{ $summary['month'] ?? now()->format('Y-m') }}">
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
                                class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-xs">
                            {{ __('Save') }}
                        </button>
                    </form>
                </div>
            </div>
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
