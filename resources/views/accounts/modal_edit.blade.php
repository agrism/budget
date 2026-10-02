<div x-data="{
    type: '{{ $account->type }}',
    currency: '{{ $account->currency }}',
    color: '{{ $account->color }}'
}">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <button type="button" 
                    @click="modalOpen = false"
                    class="p-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
                    title="{{ __('Back') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Edit Account') }}</h2>
        </div>
        <button type="button" @click="modalOpen = false" class="p-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <form hx-put="{{ route('accounts.update', $account) }}"
          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
          class="space-y-4">

        <!-- Account Name -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Account Name') }}</label>
            <input type="text" 
                   name="name" 
                   value="{{ $account->name }}"
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

        <!-- Balance & Currency -->
        <div class="grid grid-cols-2 gap-2.5">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1">{{ __('Account Balance') }}</label>
                <input type="number" 
                       step="0.01" 
                       name="balance" 
                       value="{{ $account->balance }}" 
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

        <!-- Accent Color -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-400 mb-1.5">{{ __('Choose Color') }}</label>
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach(['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', '#8b5cf6', '#14b8a6', '#ef4444', '#64748b'] as $c)
                    <button type="button" 
                            @click="color = '{{ $c }}'"
                            class="w-7 h-7 rounded-full transition-transform flex-shrink-0 flex items-center justify-center shadow-xs"
                            :class="color === '{{ $c }}' ? 'scale-125 ring-2 ring-offset-2 ring-brand-500 dark:ring-offset-[#121622]' : 'opacity-80 hover:opacity-100'"
                            style="background-color: {{ $c }};">
                        <svg x-show="color === '{{ $c }}'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="color" :value="color">
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2 pt-3">
            <button type="button" 
                    @click="modalOpen = false"
                    class="py-3 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold transition-all">
                {{ __('Cancel') }}
            </button>

            <button type="button" 
                    hx-delete="{{ route('accounts.destroy', $account) }}"
                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                    hx-confirm="{{ __('Delete this account?') }}"
                    class="py-3 px-3.5 rounded-xl border border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-xs font-bold transition-all">
                {{ __('Delete') }}
            </button>

            <button type="submit"
                    class="flex-1 py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs tracking-wide glow-btn transition-all active:scale-98 shadow-md">
                {{ __('Save Changes') }}
            </button>
        </div>
    </form>
</div>
