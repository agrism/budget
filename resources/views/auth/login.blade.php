@extends('layouts.app')

@section('title', __('Login') . ' — Budget Tracker')

@section('content')
<div class="py-6 px-2 flex flex-col justify-center min-h-[75vh]" x-data="{ videoModal: false }">
    <!-- App Logo / Title -->
    <div class="text-center mb-6">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center mx-auto mb-3 glow-btn shadow-xl border border-brand-400/30">
            <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Welcome back') }}</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Smart mobile-first personal finance tracking</p>
    </div>

    <!-- Watch Video Tutorial Button -->
    <div class="mb-5 text-center">
        <button type="button" 
                @click="videoModal = true; $nextTick(() => { const v = $refs.tutorialVideo; if(v) { v.currentTime = 0; v.play(); } })"
                class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-brand-500/10 hover:bg-brand-500/20 text-brand-600 dark:text-brand-400 border border-brand-500/30 transition-all active:scale-95 text-xs font-bold shadow-sm group">
            <span class="w-6 h-6 rounded-full bg-gradient-to-r from-brand-600 to-indigo-500 text-white flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <svg class="w-3 h-3 ml-0.5 fill-current" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </span>
            <span>{{ __('Watch Video Tutorial (1 min)') }}</span>
        </button>
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

            <!-- Remember Me -->
            <div class="flex items-center justify-between py-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded text-brand-600 bg-slate-50 dark:bg-[#141926] border-slate-300 dark:border-[#232d42] focus:ring-brand-500 focus:ring-offset-0">
                    <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">{{ __('Remember me') }}</span>
                </label>
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

    <!-- Video Tutorial Modal -->
    <div x-show="videoModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-3 bg-slate-950/80 backdrop-blur-md">
        
        <div @click.away="videoModal = false; $refs.tutorialVideo.pause()"
             x-show="videoModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="w-full max-w-[400px] bg-slate-900 border border-slate-700/80 rounded-3xl overflow-hidden shadow-2xl flex flex-col relative text-white">
            
            <!-- Header -->
            <div class="px-4 py-3 flex items-center justify-between border-b border-slate-800 bg-slate-900/90">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-pulse"></span>
                    <span class="text-xs font-bold">{{ __('App Tutorial') }}</span>
                    <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded-full bg-brand-500/20 text-brand-400">LV</span>
                </div>
                <button type="button" 
                        @click="videoModal = false; $refs.tutorialVideo.pause()"
                        class="p-1.5 rounded-xl hover:bg-slate-800 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Video Player -->
            <div class="relative bg-black aspect-[9/16] max-h-[72vh] flex items-center justify-center">
                <video x-ref="tutorialVideo" 
                       src="{{ asset('videos/tutorial.mp4') }}" 
                       poster="{{ asset('videos/tutorial_poster.jpg') }}"
                       controls 
                       playsinline 
                       preload="metadata"
                       class="w-full h-full object-contain"></video>
            </div>

            <!-- Footer -->
            <div class="p-3 bg-slate-900/90 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                    </svg>
                    {{ __('Voiceover: Nils') }}
                </span>
                <button type="button" 
                        @click="videoModal = false; $refs.tutorialVideo.pause()"
                        class="font-semibold text-brand-400 hover:text-brand-300">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
