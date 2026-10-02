<div x-data="{
    type: 'bank',
    currency: '{{ auth()->user()?->currency ?? 'EUR' }}',
    color: '#6366f1'
}">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Add Account') }}</h2>
        <button type="button" @click="modalOpen = false" class="p-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <form hx-post="{{ route('accounts.store') }}"
          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
          class="space-y-4">

        <!-- Account Name -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Account Name') }}</label>
            <input type="text" 
                   name="name" 
                   placeholder="e.g. Revolut Card, Cash Vault, Crypto..." 
                   required
                   class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 px-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500">
        </div>

        <!-- Account Type -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1.5">{{ __('Account Type') }}</label>
            <div class="grid grid-cols-4 gap-2">
                @foreach([
                    'bank' => ['label' => 'Bank', 'icon' => 'building-library'],
                    'cash' => ['label' => 'Cash', 'icon' => 'wallet'],
                    'savings' => ['label' => 'Savings', 'icon' => 'banknotes'],
                    'credit' => ['label' => 'Credit', 'icon' => 'credit-card'],
                ] as $tKey => $tInfo)
                    <button type="button"
                            @click="type = '{{ $tKey }}'"
                            :class="type === '{{ $tKey }}' ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10 ring-2 ring-brand-500/30 text-brand-700 dark:text-white' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#161b29] text-slate-700 dark:text-slate-300 opacity-80'"
                            class="flex flex-col items-center justify-center p-2 rounded-xl border text-center transition-all">
                        <span class="text-xs font-bold">{{ $tInfo['label'] }}</span>
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="type" :value="type">
        </div>

        <!-- Initial Balance & Currency -->
        <div class="grid grid-cols-2 gap-2.5">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Initial Balance') }}</label>
                <input type="number" 
                       step="0.01" 
                       name="balance" 
                       value="0.00" 
                       required
                       class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 px-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Currency') }}</label>
                <select name="currency" 
                        x-model="currency"
                        class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 px-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="EUR">EUR (€)</option>
                    <option value="USD">USD ($)</option>
                </select>
            </div>
        </div>

        <!-- Color Selection -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1.5">Accent Color</label>
            <div class="flex items-center gap-3">
                @foreach(['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', '#8b5cf6', '#06b6d4'] as $c)
                    <button type="button" 
                            @click="color = '{{ $c }}'"
                            :class="color === '{{ $c }}' ? 'ring-2 ring-brand-500 scale-110' : 'opacity-80'"
                            class="w-7 h-7 rounded-full transition-all"
                            style="background-color: {{ $c }};">
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="color" :value="color">
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-500 text-white font-bold text-sm tracking-wide glow-btn transition-all active:scale-98 flex items-center justify-center gap-2 shadow-lg mt-2">
            <span>{{ __('Add Account') }}</span>
        </button>
    </form>
</div>
