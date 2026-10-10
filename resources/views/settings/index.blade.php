@extends('layouts.app')

@section('title', __('Settings') . ' — Budget')

@section('content')
<div class="space-y-4 pb-6"
     x-data="{
        sections: {{ json_encode($sections) }},
        meta: {{ json_encode($sectionMeta) }},
        saving: false,
        saved: false,
        moveUp(index) {
            if (index > 0) {
                const item = this.sections.splice(index, 1)[0];
                this.sections.splice(index - 1, 0, item);
                this.save();
            }
        },
        moveDown(index) {
            if (index < this.sections.length - 1) {
                const item = this.sections.splice(index, 1)[0];
                this.sections.splice(index + 1, 0, item);
                this.save();
            }
        },
        toggle(index) {
            this.sections[index].enabled = !this.sections[index].enabled;
            this.save();
        },
        async save() {
            this.saving = true;
            try {
                const res = await fetch('{{ route('settings.dashboard_sections.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'HX-Request': 'true',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ sections: this.sections })
                });
                if (res.ok) {
                    this.saved = true;
                    setTimeout(() => { this.saved = false; }, 2500);
                    window.dispatchEvent(new CustomEvent('dashboardSectionsUpdated'));
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: '{{ __('Settings saved successfully!') }}' }));
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.saving = false;
            }
        }
     }">

    <!-- Top Header Bar (Back, Title, Reset) -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('dashboard') }}" 
               class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors shadow-xs"
               title="{{ __('Back') }}">
                <svg class="w-4 h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">
                    {{ __('Settings') }}
                </h1>
                <span class="text-[10px] text-slate-400">{{ __('Dashboard Sections') }}</span>
            </div>
        </div>

        <!-- Reset Button -->
        <form method="POST" action="{{ route('settings.dashboard_sections.reset') }}" 
              hx-post="{{ route('settings.dashboard_sections.reset') }}"
              hx-trigger="submit"
              hx-on::after-request="window.location.reload()">
            @csrf
            <button type="submit" 
                    title="{{ __('Reset to Default') }}"
                    class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>{{ __('Reset to Default') }}</span>
            </button>
        </form>
    </div>

    <!-- Description Hero Card -->
    <div class="app-card-glow rounded-3xl p-4 border border-slate-200 dark:border-[#232d42] bg-gradient-to-br from-white via-slate-50 to-slate-100/60 dark:from-[#141926] dark:via-[#10141f] dark:to-[#0c1017] shadow-sm">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-2xl bg-brand-500/15 text-brand-600 dark:text-brand-400 flex items-center justify-center flex-shrink-0 shadow-xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                    {{ __('Dashboard Sections') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                    {{ __('Enable or disable sections and adjust their order using the arrows.') }}
                </p>
            </div>
            
            <!-- Live Saved Indicator -->
            <div x-show="saved" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90"
                 class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold flex items-center gap-1"
                 style="display: none;">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ __('Saved') }}</span>
            </div>
        </div>
    </div>

    <!-- Section Blocks Reordering & Toggle List -->
    <div class="space-y-3">
        <template x-for="(sec, index) in sections" :key="sec.id">
            <div class="app-card rounded-2xl p-3.5 transition-all duration-200 border flex items-center justify-between gap-3 shadow-xs"
                 :class="sec.enabled 
                    ? 'border-slate-200 dark:border-[#232d42] bg-white dark:bg-[#121622] hover:border-slate-300 dark:hover:border-slate-700' 
                    : 'border-slate-200/60 dark:border-[#1a2233] bg-slate-50/70 dark:bg-[#0c1017]/60 opacity-65'">
                
                <!-- Left: Reorder controls + Index badge + Icon + Details -->
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <!-- Up / Down Reorder buttons -->
                    <div class="flex flex-col gap-1 flex-shrink-0">
                        <button type="button" 
                                @click="moveUp(index)"
                                :disabled="index === 0"
                                class="p-1 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-20 disabled:pointer-events-none transition-colors"
                                title="{{ __('Move Up') }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                        <button type="button" 
                                @click="moveDown(index)"
                                :disabled="index === sections.length - 1"
                                class="p-1 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-20 disabled:pointer-events-none transition-colors"
                                title="{{ __('Move Down') }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Order Number Badge -->
                    <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[10px] font-black flex items-center justify-center flex-shrink-0">
                        <span x-text="index + 1"></span>
                    </div>

                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-xs"
                         :style="'background-color: ' + (meta[sec.id]?.color || '#6366f1') + '20; color: ' + (meta[sec.id]?.color || '#6366f1') + ';'">
                        <template x-if="sec.id === 'balance_card'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </template>
                        <template x-if="sec.id === 'daily_limit'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <template x-if="sec.id === 'top_categories'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </template>
                        <template x-if="sec.id === 'recent_transactions'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                    </div>

                    <!-- Details -->
                    <div class="truncate">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white truncate"
                            x-text="meta[sec.id]?.title || sec.id">
                        </h3>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate mt-0.5"
                           x-text="meta[sec.id]?.description || ''">
                        </p>
                    </div>
                </div>

                <!-- Right: Toggle Switch -->
                <div class="flex items-center flex-shrink-0 pl-1">
                    <button type="button" 
                            @click="toggle(index)"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="sec.enabled ? 'bg-emerald-500 dark:bg-emerald-600' : 'bg-slate-300 dark:bg-slate-700'"
                            role="switch" 
                            :aria-checked="sec.enabled">
                        <span aria-hidden="true" 
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                              :class="sec.enabled ? 'translate-x-5' : 'translate-x-0'">
                        </span>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Fallback Manual Save Button (and form for standard POST) -->
    <form method="POST" action="{{ route('settings.dashboard_sections.update') }}" class="pt-2">
        @csrf
        <template x-for="(sec, i) in sections" :key="'input-' + sec.id">
            <div>
                <input type="hidden" :name="'sections[' + i + '][id]'" :value="sec.id">
                <input type="hidden" :name="'sections[' + i + '][enabled]'" :value="sec.enabled ? 1 : 0">
            </div>
        </template>

        <button type="button" 
                @click="save()"
                :disabled="saving"
                class="w-full py-3 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md hover:shadow-lg active:scale-98 transition-all flex items-center justify-center gap-2">
            <svg x-show="saving" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span x-text="saving ? '{{ __('Saving...') }}' : '{{ __('Save Changes') }}'"></span>
        </button>
    </form>

    <!-- Additional Quick Preferences -->
    <div class="pt-2">
        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2.5 px-1">
            {{ __('Other Preferences') }}
        </h3>

        <div class="space-y-2">
            <!-- Savings Target modal trigger -->
            <button type="button"
                    @click="modalOpen = true"
                    hx-get="{{ route('savings.modal') }}"
                    hx-target="#modal-body"
                    class="w-full app-card rounded-2xl p-3 flex items-center justify-between text-left hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">{{ __('Savings Target & Daily Limit') }}</div>
                        <div class="text-[10px] text-slate-400">{{ __('Set your income savings % and get daily allowance') }}</div>
                    </div>
                </div>
                <div class="text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </button>
        </div>
    </div>
</div>
@endsection
