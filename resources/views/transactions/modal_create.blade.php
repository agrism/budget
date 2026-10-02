@php
    $currency = auth()->user()?->currency ?? session('currency', 'EUR');
    $currencySymbol = $currency === 'USD' ? '$' : '€';
@endphp
<div x-data="{ 
    type: '{{ $defaultType }}', 
    amount: '', 
    categories: {{ Js::from($categories) }},
    selectedCategory: '{{ $categories->where('type', $defaultType)->first()?->id ?? $categories->first()?->id }}',
    setType(newType) {
        this.type = newType;
        const matchingCat = this.categories.find(c => c.type === newType);
        if (matchingCat) {
            this.selectedCategory = matchingCat.id;
        }
    },
    selectedAccount: '{{ $accounts->first()?->id }}',
    note: '',
    date: '{{ now()->format('Y-m-d') }}'
}">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Add Transaction') }}</h2>
        <button type="button" @click="modalOpen = false" class="p-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <form hx-post="{{ route('transactions.store') }}"
          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
          class="space-y-4">
        
        <!-- Type Pill Selector (Expense / Income / Transfer) -->
        <div class="bg-slate-100 dark:bg-[#181f2e] p-1 rounded-2xl flex items-center gap-1 border border-slate-200 dark:border-[#232d42]">
            <button type="button" 
                    @click="setType('expense')"
                    :class="type === 'expense' ? 'bg-gradient-to-r from-rose-500 to-amber-500 text-white font-semibold shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
                {{ __('Expense') }}
            </button>
            <button type="button" 
                    @click="setType('income')"
                    :class="type === 'income' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-semibold shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
                {{ __('Income') }}
            </button>
            <button type="button" 
                    @click="setType('transfer')"
                    :class="type === 'transfer' ? 'bg-gradient-to-r from-brand-600 to-indigo-500 text-white font-semibold shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
                {{ __('Transfer') }}
            </button>
        </div>
        <input type="hidden" name="type" :value="type">

        <!-- Amount Display Input -->
        <div class="app-card rounded-2xl p-4 text-center border border-brand-500/25 shadow-inner">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Enter Amount') }}</span>
            <div class="flex items-center justify-center gap-1 mt-1">
                <span class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $currencySymbol }}</span>
                <input type="number" 
                       step="0.01" 
                       min="0.01" 
                       name="amount" 
                       x-model="amount" 
                       placeholder="0.00" 
                       required
                       autofocus
                       class="w-44 text-3xl font-black text-slate-900 dark:text-white bg-transparent text-center focus:outline-none placeholder-slate-400 dark:placeholder-slate-600 tracking-tight">
            </div>
        </div>

        <!-- Category Grid Picker (For Expenses & Income) -->
        <div x-show="type !== 'transfer'">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400">{{ __('Select Category') }}</label>
                <button type="button" 
                        hx-get="{{ route('categories.create') }}" 
                        hx-target="#modal-body" 
                        class="text-[11px] font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    + {{ __('New') }}
                </button>
            </div>
            <div class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto no-scrollbar p-1">
                @foreach($categories as $cat)
                    <button type="button"
                            x-show="type === '{{ $cat->type }}'"
                            @click="selectedCategory = '{{ $cat->id }}'"
                            :class="selectedCategory == '{{ $cat->id }}' ? 'border-brand-500 ring-2 ring-brand-500/30 bg-brand-50 dark:bg-brand-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#161b29] opacity-80 hover:opacity-100'"
                            class="flex flex-col items-center justify-center p-2 rounded-xl border transition-all">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mb-1"
                             style="background-color: {{ $cat->color }}25; color: {{ $cat->color }};">
                            <x-category-icon :icon="$cat->icon" class="w-4 h-4" />
                        </div>
                        <span class="text-[10px] font-medium text-slate-700 dark:text-slate-300 truncate w-full text-center">{{ $cat->name }}</span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="category_id" :value="selectedCategory">
        </div>

        <!-- Account & Date Selectors -->
        <div class="grid grid-cols-2 gap-2.5">
            <!-- Account -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Account') }}</label>
                <select name="account_id" 
                        x-model="selectedAccount"
                        class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2 px-3 text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency === 'USD' ? '$' : '€' }}{{ number_format($acc->balance, 0) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Date') }}</label>
                <input type="date" 
                       name="transacted_at" 
                       x-model="date"
                       class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2 px-3 text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
            </div>
        </div>

        <!-- Destination Account (Transfer only) -->
        <div x-show="type === 'transfer'" style="display: none;">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('To Account') }}</label>
            <select name="destination_account_id" 
                    class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2 px-3 text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-500">
                <option value="">{{ __('Select Destination Account') }}</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Note / Title -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Note (Optional)') }}</label>
            <div class="relative">
                <input type="text" 
                       name="note" 
                       x-model="note" 
                       placeholder="{{ __('What was this for?') }}" 
                       class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 pl-9 pr-3 text-xs text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-500 text-white font-bold text-sm tracking-wide glow-btn transition-all active:scale-98 flex items-center justify-center gap-2 shadow-lg mt-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ __('Save Transaction') }}</span>
        </button>
    </form>
</div>
