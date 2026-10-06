@php
    $currency = auth()->user()?->currency ?? session('currency', 'EUR');
    $currencySymbol = $currency === 'USD' ? '$' : '€';
    $currentAccount = $accounts->firstWhere('id', $transaction->account_id);
    $destAccount = $transaction->destination_account_id ? $accounts->firstWhere('id', $transaction->destination_account_id) : null;
    $currentCategory = $transaction->category;
@endphp
<div x-data="{ 
    isEditing: false,
    type: '{{ $transaction->type }}', 
    amount: '{{ number_format($transaction->amount, 2, '.', '') }}', 
    categories: {{ Js::from($categories) }},
    selectedCategory: '{{ $transaction->category_id ?? ($categories->where('type', $transaction->type)->first()?->id ?? '') }}',
    selectedAccount: '{{ $transaction->account_id }}',
    destinationAccount: '{{ $transaction->destination_account_id ?? '' }}',
    note: '{{ addslashes($transaction->note ?? '') }}',
    date: '{{ \Carbon\Carbon::parse($transaction->transacted_at)->format('Y-m-d') }}',
    initial: {
        type: '{{ $transaction->type }}',
        amount: '{{ number_format($transaction->amount, 2, '.', '') }}',
        selectedCategory: '{{ $transaction->category_id ?? ($categories->where('type', $transaction->type)->first()?->id ?? '') }}',
        selectedAccount: '{{ $transaction->account_id }}',
        destinationAccount: '{{ $transaction->destination_account_id ?? '' }}',
        note: '{{ addslashes($transaction->note ?? '') }}',
        date: '{{ \Carbon\Carbon::parse($transaction->transacted_at)->format('Y-m-d') }}'
    },
    setType(newType) {
        this.type = newType;
        const matchingCat = this.categories.find(c => c.type === newType);
        if (matchingCat) {
            this.selectedCategory = matchingCat.id;
        }
    },
    get hasChanges() {
        return this.type !== this.initial.type ||
               parseFloat(this.amount || 0) !== parseFloat(this.initial.amount || 0) ||
               (this.type !== 'transfer' && this.selectedCategory != this.initial.selectedCategory) ||
               this.selectedAccount != this.initial.selectedAccount ||
               (this.type === 'transfer' && this.destinationAccount != this.initial.destinationAccount) ||
               this.note !== this.initial.note ||
               this.date !== this.initial.date;
    },
    resetForm() {
        this.type = this.initial.type;
        this.amount = this.initial.amount;
        this.selectedCategory = this.initial.selectedCategory;
        this.selectedAccount = this.initial.selectedAccount;
        this.destinationAccount = this.initial.destinationAccount;
        this.note = this.initial.note;
        this.date = this.initial.date;
        this.isEditing = false;
    }
}">

    <!-- 1. VIEW MODE (Default when opening transaction) -->
    <div x-show="!isEditing">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Transaction Details') }}</h2>
            <button type="button" @click="modalOpen = false" class="p-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Amount Banner Hero -->
        <div class="app-card rounded-2xl p-5 text-center mb-4 border border-slate-200 dark:border-[#232d42] bg-gradient-to-b from-slate-50 to-slate-100/50 dark:from-[#161d2b] dark:to-[#111723]">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-2
                @if($transaction->type === 'income') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                @elseif($transaction->type === 'transfer') bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20
                @else bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 @endif">
                @if($transaction->type === 'income') {{ __('Income') }}
                @elseif($transaction->type === 'transfer') {{ __('Transfer') }}
                @else {{ __('Expense') }}
                @endif
            </div>

            <div class="text-3xl font-black tracking-tight {{ $transaction->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}{{ $currencySymbol }}{{ number_format($transaction->amount, 2) }}
            </div>
            
            @if($transaction->note)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 font-medium">{{ $transaction->note }}</p>
            @endif
        </div>

        <!-- Details List -->
        <div class="space-y-2 mb-5">
            <!-- Category (if not transfer) -->
            @if($transaction->type !== 'transfer')
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-[#141926] border border-slate-100 dark:border-[#20293d]">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Category') }}</span>
                    <div class="flex items-center gap-2">
                        @if($currentCategory)
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs"
                                 style="background-color: {{ $currentCategory->color }}20; color: {{ $currentCategory->color }};">
                                <x-category-icon :icon="$currentCategory->icon" class="w-3.5 h-3.5" />
                            </div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $currentCategory->name }}</span>
                        @else
                            <span class="text-xs text-slate-400 font-medium">{{ __('Uncategorized') }}</span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Account -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-[#141926] border border-slate-100 dark:border-[#20293d]">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Account') }}</span>
                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                    <span>{{ $currentAccount?->name ?? '—' }}</span>
                    @if($transaction->type === 'transfer' && $destAccount)
                        <span class="text-slate-400 font-normal">→</span>
                        <span class="text-brand-600 dark:text-brand-400">{{ $destAccount->name }}</span>
                    @endif
                </div>
            </div>

            <!-- Date -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-[#141926] border border-slate-100 dark:border-[#20293d]">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Date') }}</span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                    {{ $transaction->formatted_date }}
                </span>
            </div>

            <!-- Note (if exists) -->
            @if($transaction->note)
                <div class="flex items-start justify-between p-3 rounded-xl bg-slate-50 dark:bg-[#141926] border border-slate-100 dark:border-[#20293d]">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('Note') }}</span>
                    <span class="text-xs text-slate-800 dark:text-slate-200 text-right max-w-[220px] font-medium">{{ $transaction->note }}</span>
                </div>
            @endif
        </div>

        <!-- View Mode Actions (Cancel, Delete, Edit) -->
        <div class="flex items-center gap-2 pt-1">
            <!-- Cancel Button -->
            <button type="button" 
                    @click="modalOpen = false"
                    class="py-3 px-4 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs transition-colors">
                {{ __('Cancel') }}
            </button>

            <!-- Delete Button -->
            <button type="button"
                    hx-delete="{{ route('transactions.destroy', $transaction->id) }}"
                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                    hx-confirm="{{ __('Delete this transaction?') }}"
                    class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-colors font-semibold text-xs flex items-center justify-center"
                    title="{{ __('Delete') }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>

            <!-- Edit Button -->
            <button type="button" 
                    @click="isEditing = true"
                    class="flex-1 py-3 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-500 hover:to-indigo-400 text-white font-bold text-xs tracking-wide shadow-md flex items-center justify-center gap-2 transition-all active:scale-98">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>{{ __('Edit') }}</span>
            </button>
        </div>
    </div>

    <!-- 2. EDIT MODE (Active after clicking "Edit") -->
    <div x-show="isEditing" style="display: none;">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Edit Transaction') }}</h2>
            <button type="button" @click="resetForm(); modalOpen = false" class="p-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form hx-put="{{ route('transactions.update', $transaction->id) }}"
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
                        x-model="destinationAccount"
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

            <!-- Edit Mode Actions: Cancel & Save Changes -->
            <div class="flex items-center gap-2 pt-2">
                <!-- Cancel Button -->
                <button type="button"
                        @click="resetForm()"
                        class="py-3.5 px-4 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs transition-colors">
                    {{ __('Cancel') }}
                </button>

                <!-- Save Changes Button -->
                <button type="submit" 
                        :disabled="!hasChanges"
                        :class="hasChanges ? 'bg-gradient-to-r from-brand-600 to-indigo-500 text-white hover:from-brand-500 hover:to-indigo-400 shadow-lg glow-btn cursor-pointer' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed'"
                        class="flex-1 py-3.5 px-4 rounded-2xl font-bold text-xs tracking-wide transition-all active:scale-98 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="hasChanges ? '{{ __('Save Changes') }}' : '{{ __('No Changes') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
