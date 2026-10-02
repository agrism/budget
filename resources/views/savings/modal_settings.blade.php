@php
    $effectiveIncome = (float) $summary['effective_income'];
    $currentPercentage = (float) ($user->savings_target_percentage ?? 20.0);
    $daysRemaining = (int) $summary['days_remaining'];
    $daysInMonth = (int) $summary['days_in_month'];
@endphp
<div x-data="{
    percentage: {{ $currentPercentage }},
    income: {{ $effectiveIncome }},
    daysRemaining: {{ $daysRemaining }},
    daysInMonth: {{ $daysInMonth }},
    get targetSavings() {
        return Math.round(this.income * (this.percentage / 100) * 100) / 100;
    },
    get spendableMonthly() {
        return Math.max(0, Math.round((this.income - this.targetSavings) * 100) / 100);
    },
    get dailyLimit() {
        return this.daysInMonth > 0 ? Math.round((this.spendableMonthly / this.daysInMonth) * 100) / 100 : 0;
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Savings Target & Daily Limit') }}</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('Set your income savings % and get daily allowance') }}</p>
            </div>
        </div>
        <button type="button" @click="modalOpen = false" class="p-1.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <form hx-post="{{ route('savings.update') }}"
          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
          class="space-y-4">

        <!-- Live Calculation Highlight Card -->
        <div class="app-card-glow rounded-2xl p-4 border border-brand-500/20 shadow-sm relative overflow-hidden">
            <div class="grid grid-cols-2 gap-3 text-center">
                <!-- Target Savings -->
                <div class="p-2 rounded-xl bg-white/60 dark:bg-[#161b29]/60 border border-slate-200/60 dark:border-slate-800">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Target Savings') }}</span>
                    <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 mt-0.5">
                        {{ $currencySymbol }}<span x-text="targetSavings.toFixed(2)"></span>
                    </div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400"><span x-text="percentage"></span>% {{ __('of income') }}</span>
                </div>

                <!-- Suggested Daily Limit -->
                <div class="p-2 rounded-xl bg-white/60 dark:bg-[#161b29]/60 border border-brand-200 dark:border-brand-500/30">
                    <span class="text-[10px] font-bold text-brand-600 dark:text-brand-400 uppercase tracking-wider">{{ __('Daily Limit') }}</span>
                    <div class="text-lg font-black text-slate-900 dark:text-white mt-0.5">
                        {{ $currencySymbol }}<span x-text="dailyLimit.toFixed(2)"></span>
                    </div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">/ {{ __('day') }} (<span x-text="daysInMonth"></span> {{ __('days') }})</span>
                </div>
            </div>

            <!-- Spendable monthly total subtext -->
            <div class="mt-3 pt-2.5 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400 px-1">
                <span>{{ __('Safe to Spend This Month') }}:</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ $currencySymbol }}<span x-text="spendableMonthly.toFixed(2)"></span></span>
            </div>
        </div>

        <!-- Savings Percentage Slider & Quick Buttons -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Savings Percentage from Income') }}</label>
                <span class="text-sm font-black text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10 px-2.5 py-0.5 rounded-lg border border-brand-200 dark:border-brand-500/20">
                    <span x-text="percentage"></span>%
                </span>
            </div>

            <!-- Range Slider -->
            <input type="range" 
                   name="savings_target_percentage" 
                   min="0" 
                   max="80" 
                   step="1"
                   x-model="percentage"
                   class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-brand-600">

            <!-- Quick Preset Buttons -->
            <div class="flex gap-1.5 mt-3">
                @foreach([10, 15, 20, 25, 30, 50] as $preset)
                    <button type="button" 
                            @click="percentage = {{ $preset }}"
                            :class="percentage == {{ $preset }} ? 'bg-brand-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            class="flex-1 py-1.5 rounded-xl text-xs transition-all text-center">
                        {{ $preset }}%
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Monthly Income Reference Input (Optional fixed salary baseline) -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Monthly Income Baseline') }} ({{ $currencySymbol }})</label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">{{ $currencySymbol }}</span>
                <input type="number" 
                       step="1" 
                       min="1"
                       name="expected_monthly_income" 
                       x-model="income"
                       placeholder="e.g. 2500" 
                       class="w-full bg-slate-50 dark:bg-[#161b29] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 pl-8 pr-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500">
            </div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 block">{{ __('Used to calculate your target savings and daily spending allowance.') }}</span>
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-500 text-white font-bold text-sm tracking-wide glow-btn transition-all active:scale-98 flex items-center justify-center gap-2 shadow-lg mt-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ __('Save Savings Target') }}</span>
        </button>
    </form>
</div>
