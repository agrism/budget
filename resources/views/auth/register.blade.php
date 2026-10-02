@extends('layouts.app')

@section('title', __('Register') . ' — Budget Tracker')

@section('content')
<div class="py-4 px-2 flex flex-col justify-center min-h-[85vh]">
    <!-- Title -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Create your account') }}</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Start tracking your personal finances with ease</p>
    </div>

    <!-- Register Card -->
    <div class="app-card-glow rounded-3xl p-6 border border-slate-200 dark:border-slate-700/60 shadow-xl">
        <form method="POST" action="{{ route('register.post') }}" class="space-y-3.5">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Name') }}</label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       placeholder="e.g. Alex Rivera"
                       class="w-full bg-slate-50 dark:bg-[#141926] border {{ $errors->has('name') ? 'border-rose-500' : 'border-slate-200 dark:border-[#232d42]' }} rounded-xl py-2.5 px-3.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 transition-all">
                @error('name')
                    <span class="text-[11px] text-rose-500 dark:text-rose-400 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Email') }}</label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       placeholder="you@example.com"
                       class="w-full bg-slate-50 dark:bg-[#141926] border {{ $errors->has('email') ? 'border-rose-500' : 'border-slate-200 dark:border-[#232d42]' }} rounded-xl py-2.5 px-3.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 transition-all">
                @error('email')
                    <span class="text-[11px] text-rose-500 dark:text-rose-400 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Currency & Language Preferences -->
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Currency') }}</label>
                    <select name="currency" class="w-full bg-slate-50 dark:bg-[#141926] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 px-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="EUR" selected>EUR (€)</option>
                        <option value="USD">USD ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Language') }}</label>
                    <select name="locale" class="w-full bg-slate-50 dark:bg-[#141926] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 px-3 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
                        <option value="lv" {{ app()->getLocale() === 'lv' ? 'selected' : '' }}>Latviešu</option>
                        <option value="ru" {{ app()->getLocale() === 'ru' ? 'selected' : '' }}>Русский</option>
                    </select>
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Password') }}</label>
                <input type="password" 
                       name="password" 
                       required 
                       placeholder="••••••••"
                       class="w-full bg-slate-50 dark:bg-[#141926] border {{ $errors->has('password') ? 'border-rose-500' : 'border-slate-200 dark:border-[#232d42]' }} rounded-xl py-2.5 px-3.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 transition-all">
                @error('password')
                    <span class="text-[11px] text-rose-500 dark:text-rose-400 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">{{ __('Confirm Password') }}</label>
                <input type="password" 
                       name="password_confirmation" 
                       required 
                       placeholder="••••••••"
                       class="w-full bg-slate-50 dark:bg-[#141926] border border-slate-200 dark:border-[#232d42] rounded-xl py-2.5 px-3.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 transition-all">
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-500 text-white font-bold text-sm tracking-wide glow-btn transition-all active:scale-98 flex items-center justify-center gap-2 shadow-lg mt-3">
                <span>{{ __('Register') }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>

        <!-- Login Link -->
        <div class="text-center mt-5 pt-3.5 border-t border-slate-200 dark:border-slate-800">
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('Already have an account?') }}</span>
            <a href="{{ route('login') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 font-bold ml-1">
                {{ __('Login') }}
            </a>
        </div>
    </div>
</div>
@endsection
