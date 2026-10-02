@php
    $currency = auth()->user()?->currency ?? session('currency', 'EUR');
    $currencySymbol = $currency === 'USD' ? '$' : '€';
    $availableIcons = [
        'shopping-cart' => 'Shopping',
        'cake' => 'Food & Drinks',
        'home' => 'Housing',
        'briefcase' => 'Work / Salary',
        'car' => 'Transport',
        'heart' => 'Health',
        'tv' => 'Entertainment',
        'academic-cap' => 'Education',
        'sparkles' => 'Personal Care',
        'plane' => 'Travel',
        'coffee' => 'Cafes & Bars',
        'gift' => 'Gifts',
        'phone' => 'Bills & Utilities',
        'wallet' => 'Savings',
        'tag' => 'Other',
    ];
    $availableColors = [
        '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', 
        '#8b5cf6', '#06b6d4', '#ef4444', '#14b8a6', '#84cc16'
    ];
@endphp
<div x-data="{
    name: '{{ addslashes($category->name) }}',
    type: '{{ $category->type }}',
    icon: '{{ $category->icon }}',
    color: '{{ $category->color }}',
    monthly_limit: '{{ $monthlyLimit !== null ? $monthlyLimit : '' }}'
}">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                 :style="'background-color: ' + color + '25; color: ' + color">
                <x-category-icon :icon="$category->icon" class="w-4 h-4" />
            </div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Edit Category') }}</h2>
        </div>
        <button type="button" @click="modalOpen = false" class="p-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <form hx-put="{{ route('categories.update', $category->id) }}"
          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
          class="space-y-4">

        <!-- Type Pill Selector (Expense / Income) -->
        <div class="bg-slate-100 dark:bg-[#181f2e] p-1 rounded-2xl flex items-center gap-1 border border-slate-200 dark:border-[#232d42]">
            <button type="button" 
                    @click="type = 'expense'"
                    :class="type === 'expense' ? 'bg-gradient-to-r from-rose-500 to-amber-500 text-white font-semibold shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
                {{ __('Expense') }}
            </button>
            <button type="button" 
                    @click="type = 'income'"
                    :class="type === 'income' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-semibold shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
                {{ __('Income') }}
            </button>
        </div>
        <input type="hidden" name="type" :value="type">

        <!-- Category Name -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Category Name') }}</label>
            <input type="text" 
                   name="name" 
                   x-model="name"
                   required
                   class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 px-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500">
        </div>

        <!-- Monthly Budget Limit (For expense categories) -->
        <div x-show="type === 'expense'">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Monthly Budget Limit') }} ({{ $currencySymbol }})</label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">{{ $currencySymbol }}</span>
                <input type="number" 
                       step="1" 
                       min="0"
                       name="monthly_limit" 
                       x-model="monthly_limit"
                       placeholder="e.g. 250" 
                       class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 pl-8 pr-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500">
            </div>
        </div>

        <!-- Icon Picker Grid -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1.5">{{ __('Choose Icon') }}</label>
            <div class="grid grid-cols-5 gap-2 max-h-36 overflow-y-auto no-scrollbar p-1">
                @foreach($availableIcons as $iKey => $iLabel)
                    <button type="button"
                            @click="icon = '{{ $iKey }}'"
                            :class="icon === '{{ $iKey }}' ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10 ring-2 ring-brand-500/30' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#161b29] opacity-75 hover:opacity-100'"
                            class="flex flex-col items-center justify-center p-2 rounded-xl border transition-all text-center"
                            title="{{ $iLabel }}">
                        <div class="w-6 h-6 flex items-center justify-center"
                             :style="icon === '{{ $iKey }}' ? 'color: ' + color : 'color: inherit'">
                            <x-category-icon :icon="$iKey" class="w-4 h-4" />
                        </div>
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="icon" :value="icon">
        </div>

        <!-- Color Selection -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1.5">{{ __('Choose Color') }}</label>
            <div class="flex items-center gap-2.5 flex-wrap">
                @foreach($availableColors as $c)
                    <button type="button" 
                            @click="color = '{{ $c }}'"
                            :class="color === '{{ $c }}' ? 'ring-2 ring-brand-500 scale-110' : 'opacity-80 hover:opacity-100'"
                            class="w-7 h-7 rounded-full transition-all"
                            style="background-color: {{ $c }};">
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="color" :value="color">
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2 pt-2">
            <!-- Delete Category button -->
            <button type="button"
                    hx-delete="{{ route('categories.destroy', $category->id) }}"
                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                    hx-confirm="{{ __('Delete this category?') }}"
                    @click="modalOpen = false"
                    class="py-3.5 px-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-600 dark:text-rose-400 font-bold text-xs hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-all flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>{{ __('Delete') }}</span>
            </button>

            <!-- Save Changes button -->
            <button type="submit" 
                    class="flex-1 py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-500 text-white font-bold text-sm tracking-wide glow-btn transition-all active:scale-98 flex items-center justify-center gap-2 shadow-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</div>
