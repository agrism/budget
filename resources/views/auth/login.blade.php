@extends('layouts.app')

@section('title', __('Login') . ' — Budget Tracker')

@section('content')
<div class="py-6 px-2 flex flex-col justify-center min-h-[75vh]">
    <!-- App Logo / Title -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center mx-auto mb-3 glow-btn shadow-xl border border-brand-400/30">
            <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Welcome back') }}</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Smart mobile-first personal finance tracking</p>
    </div>

    <!-- Login Card -->
    <div class="app-card-glow rounded-3xl p-6 border border-slate-200 dark:border-slate-700/60 shadow-xl">
        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Email') }}</label>
                <div class="relative">
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="you@example.com"
                           required 
                           autocomplete="email"
                           class="w-full bg-slate-50 dark:bg-[#141926] border {{ $errors->has('email') ? 'border-rose-500' : 'border-slate-200 dark:border-[#232d42]' }} rounded-xl py-3 pl-10 pr-4 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                @error('email')
                    <span class="text-[11px] text-rose-500 dark:text-rose-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Password') }}</label>
                <div class="relative">
                    <input type="password" 
                           name="password" 
                           value="" 
                           placeholder="••••••••"
                           required 
                           class="w-full bg-slate-50 dark:bg-[#141926] border {{ $errors->has('password') ? 'border-rose-500' : 'border-slate-200 dark:border-[#232d42]' }} rounded-xl py-3 pl-10 pr-4 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                @error('password')
                    <span class="text-[11px] text-rose-500 dark:text-rose-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-500 text-white font-bold text-sm tracking-wide glow-btn transition-all active:scale-98 flex items-center justify-center gap-2 shadow-lg mt-2">
                <span>{{ __('Login') }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>

        <!-- Register Link -->
        <div class="text-center mt-6 pt-4 border-t border-slate-200 dark:border-slate-800">
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ __("Don't have an account?") }}</span>
            <a href="{{ route('register') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 font-bold ml-1">
                {{ __('Register') }}
            </a>
        </div>
    </div>
</div>
@endsection
