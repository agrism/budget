<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ 
          darkMode: localStorage.getItem('budget_theme') === 'dark',
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('budget_theme', this.darkMode ? 'dark' : 'light');
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }"
      x-init="if (darkMode) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); }"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Budget — Personal Finance')</title>

    <!-- PWA & Mobile Web App Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Budget">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Play CDN for rich utilities) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                    },
                },
            },
        }
    </script>

    <!-- HTMX & Alpine.js -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <script defer src="https://unpkg.com/alpinejs@3.13.10/dist/cdn.min.js"></script>

    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #232d42;
        }
        /* Light / Dark Glassmorphism Cards */
        .app-card, .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
        }
        .dark .app-card, .dark .glass-card {
            background: linear-gradient(135deg, rgba(25, 31, 48, 0.75) 0%, rgba(18, 22, 34, 0.85) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: none;
        }
        .app-card-glow, .glass-card-glow {
            background: linear-gradient(135deg, #ffffff 0%, #f5f7ff 100%);
            border: 1px solid rgba(99, 102, 241, 0.25);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.1), 0 8px 10px -6px rgba(99, 102, 241, 0.05);
        }
        .dark .app-card-glow, .dark .glass-card-glow {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(20, 24, 38, 0.9) 60%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            box-shadow: 0 8px 32px 0 rgba(99, 102, 241, 0.12);
        }
        .glow-btn {
            box-shadow: 0 4px 14px 0 rgba(99, 102, 241, 0.4);
        }
        .glow-btn:hover {
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Global top progress bar animation */
        #global-progress-bar {
            transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease;
            background: linear-gradient(90deg, #6366f1, #a855f7, #06b6d4, #10b981);
            background-size: 200% 100%;
            animation: progressShimmer 1.8s infinite linear;
        }
        @keyframes progressShimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body class="flex justify-center items-start min-h-screen antialiased bg-slate-100 dark:bg-[#06080d] text-slate-800 dark:text-slate-100 selection:bg-brand-500 selection:text-white transition-colors duration-200"
      x-data="{ 
          modalOpen: false, 
          settingsOpen: false,
          toastMsg: '', 
          showToast(msg) { 
              this.toastMsg = msg; 
              setTimeout(() => { this.toastMsg = '' }, 3500); 
          } 
      }"
      @close-modal.window="modalOpen = false; settingsOpen = false"
      @show-toast.window="showToast($event.detail)"
      @htmx:after-request="if($event.detail.successful && $event.detail.headers['hx-trigger']) { 
          const triggers = JSON.parse($event.detail.headers['hx-trigger'] || '{}');
          if (triggers.closeModal) modalOpen = false;
          if (triggers.showToast) showToast(triggers.showToast);
      }">

    <!-- Mobile Frame Container -->
    <div class="w-full max-w-[430px] min-h-screen bg-white dark:bg-[#0c1017] border-x border-slate-200 dark:border-[#1a2233] flex flex-col relative shadow-xl overflow-x-hidden @auth pb-24 @else pb-6 @endauth transition-colors duration-200">
        
        <!-- Global Top Progress Bar -->
        <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[430px] h-[3px] z-[70] pointer-events-none overflow-hidden">
            <div id="global-progress-bar" class="h-full w-0 opacity-0 shadow-[0_0_10px_rgba(99,102,241,0.9)]"></div>
        </div>

        <!-- Global Floating Loading Badge -->
        <div id="global-page-loader" 
             class="fixed top-3.5 left-1/2 -translate-x-1/2 z-[70] pointer-events-none transition-all duration-200 opacity-0 -translate-y-4">
            <div class="px-3.5 py-1.5 rounded-full bg-slate-900/95 dark:bg-white/95 text-white dark:text-slate-900 text-xs font-bold shadow-2xl backdrop-blur-md flex items-center gap-2 border border-white/20 dark:border-black/10">
                <svg class="animate-spin h-3.5 w-3.5 text-brand-400 dark:text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span id="global-page-loader-text">{{ __('Loading...') }}</span>
            </div>
        </div>

        <!-- Toast Notification -->
        <div x-show="toastMsg" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-y-full opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-full opacity-0"
             class="fixed top-4 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 rounded-full bg-emerald-600 text-white text-xs font-semibold shadow-lg backdrop-blur-md flex items-center gap-2 border border-emerald-400/40 pointer-events-none"
             style="display: none;">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <span x-text="toastMsg"></span>
        </div>

        <!-- Top Phone Status & Language Bar -->
        <div class="w-full pt-3 pb-2 px-4 flex justify-between items-center text-xs select-none border-b border-slate-100 dark:border-[#141926] bg-slate-50/70 dark:bg-transparent">
            <!-- Clock & User Greeting -->
            <div class="flex items-center gap-2">
                <span class="font-bold tracking-wider text-slate-800 dark:text-slate-200">9:41</span>
                @auth
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-[110px]">• {{ auth()->user()->name }}</span>
                @endauth
            </div>

            <!-- Language, Currency & Theme Switcher -->
            <div class="flex items-center gap-1.5">
                <!-- Theme Toggle Button (Light / Dark) -->
                <button type="button" 
                        @click="toggleTheme()" 
                        class="p-1 rounded-lg bg-slate-200/80 dark:bg-[#141926] border border-slate-300 dark:border-[#232d42] text-slate-700 dark:text-amber-400 hover:scale-105 transition-all"
                        :title="darkMode ? 'Pārslēgt uz gaišo tēmu' : 'Pārslēgt uz tumšo tēmu'">
                    <!-- Sun icon when dark -->
                    <svg x-show="darkMode" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon icon when light -->
                    <svg x-show="!darkMode" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Currency Toggle -->
                <form method="POST" action="{{ route('currency.set') }}" class="inline-block">
                    @csrf
                    @php $curr = auth()->user()?->currency ?? session('currency', 'EUR'); @endphp
                    <select name="currency" 
                            onchange="this.form.submit()" 
                            class="bg-white dark:bg-[#141926] border border-slate-200 dark:border-[#232d42] text-[10px] font-bold text-slate-700 dark:text-slate-300 rounded-lg px-1.5 py-0.5 focus:outline-none focus:border-brand-500 cursor-pointer shadow-sm">
                        <option value="EUR" {{ $curr === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="USD" {{ $curr === 'USD' ? 'selected' : '' }}>USD ($)</option>
                    </select>
                </form>

                <!-- Language Selector Dropdown -->
                @php
                    $locales = [
                        'lv' => ['label' => 'Latviešu', 'code' => 'LV', 'flag' => 'lv'],
                        'en' => ['label' => 'English', 'code' => 'EN', 'flag' => 'en'],
                        'ru' => ['label' => 'Русский', 'code' => 'RU', 'flag' => 'ru'],
                    ];
                    $currentLocale = app()->getLocale();
                    $currentData = $locales[$currentLocale] ?? $locales['en'];
                @endphp
                <div class="relative" x-data="{ langOpen: false }" @click.outside="langOpen = false" @keydown.escape.window="langOpen = false">
                    <!-- Dropdown Trigger Button -->
                    <button type="button" 
                            @click="langOpen = !langOpen" 
                            class="flex items-center gap-1.5 bg-white dark:bg-[#141926] border border-emerald-500/70 dark:border-emerald-500/60 rounded-full px-2.5 py-1 text-[11px] font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#1a2233] transition-all shadow-xs select-none">
                        <!-- Globe Icon -->
                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>

                        <!-- Flag -->
                        @if($currentData['flag'] === 'lv')
                            <svg class="w-4 h-3 rounded-[2px] shadow-xs flex-shrink-0" viewBox="0 0 640 480">
                                <path fill="#9e3039" d="M0 0h640v480H0z"/>
                                <path fill="#fff" d="M0 192h640v96H0z"/>
                            </svg>
                        @elseif($currentData['flag'] === 'en')
                            <svg class="w-4 h-3 rounded-[2px] shadow-xs flex-shrink-0" viewBox="0 0 640 480">
                                <path fill="#012169" d="M0 0h640v480H0z"/>
                                <path fill="#fff" d="M75 0l245 184L565 0h75v45L435 240l205 154v86h-75L320 295 75 480H0v-45l205-155L0 125V0z"/>
                                <path fill="#C8102E" d="M424 281l216 163v36h-24L392 305l32-24zm-208-82L0 36V0h24l224 175-32 24zm208-36L640 0v36L456 175l-32-12zm-208 82L0 444v36h24l192-149-8-30z"/>
                                <path fill="#fff" d="M256 0h128v480H256zM0 176h640v128H0z"/>
                                <path fill="#C8102E" d="M280 0h80v480h-80zM0 200h640v80H0z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-3 rounded-[2px] shadow-xs flex-shrink-0" viewBox="0 0 640 480">
                                <path fill="#fff" d="M0 0h640v160H0z"/>
                                <path fill="#0039a6" d="M0 160h640v160H0z"/>
                                <path fill="#d52b1e" d="M0 320h640v160H0z"/>
                            </svg>
                        @endif

                        <!-- Code -->
                        <span class="tracking-wide">{{ $currentData['code'] }}</span>

                        <!-- Chevron Down -->
                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': langOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="langOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute right-0 mt-2 w-36 bg-white dark:bg-[#121622] rounded-2xl p-1.5 shadow-2xl border border-slate-200/80 dark:border-[#232d42] z-50"
                         style="display: none;">
                        @foreach($locales as $code => $data)
                            @php $isActive = $currentLocale === $code; @endphp
                            <a href="{{ route('locale.set', $code) }}" 
                               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs transition-all {{ $isActive ? 'bg-slate-100 dark:bg-[#1a2233] text-slate-900 dark:text-white font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 font-medium' }}">
                                <div class="flex items-center gap-2">
                                    @if($data['flag'] === 'lv')
                                        <svg class="w-4 h-3 rounded-[2px] shadow-xs flex-shrink-0" viewBox="0 0 640 480">
                                            <path fill="#9e3039" d="M0 0h640v480H0z"/>
                                            <path fill="#fff" d="M0 192h640v96H0z"/>
                                        </svg>
                                    @elseif($data['flag'] === 'en')
                                        <svg class="w-4 h-3 rounded-[2px] shadow-xs flex-shrink-0" viewBox="0 0 640 480">
                                            <path fill="#012169" d="M0 0h640v480H0z"/>
                                            <path fill="#fff" d="M75 0l245 184L565 0h75v45L435 240l205 154v86h-75L320 295 75 480H0v-45l205-155L0 125V0z"/>
                                            <path fill="#C8102E" d="M424 281l216 163v36h-24L392 305l32-24zm-208-82L0 36V0h24l224 175-32 24zm208-36L640 0v36L456 175l-32-12zm-208 82L0 444v36h24l192-149-8-30z"/>
                                            <path fill="#fff" d="M256 0h128v480H256zM0 176h640v128H0z"/>
                                            <path fill="#C8102E" d="M280 0h80v480h-80zM0 200h640v80H0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-3 rounded-[2px] shadow-xs flex-shrink-0" viewBox="0 0 640 480">
                                            <path fill="#fff" d="M0 0h640v160H0z"/>
                                            <path fill="#0039a6" d="M0 160h640v160H0z"/>
                                            <path fill="#d52b1e" d="M0 320h640v160H0z"/>
                                        </svg>
                                    @endif
                                    <span>{{ $data['label'] }}</span>
                                </div>
                                @if($isActive)
                                    <svg class="w-3.5 h-3.5 text-slate-900 dark:text-white flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                @auth
                    <!-- Settings button -->
                    <a href="{{ route('settings.index') }}" 
                       title="{{ __('Settings') }}" 
                       class="p-1 rounded-lg text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400 hover:bg-slate-200/60 dark:hover:bg-[#1a2233] transition-colors {{ request()->routeIs('settings.*') ? 'text-brand-600 dark:text-brand-400' : '' }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>

                    <!-- Logout button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline-block">
                        @csrf
                        <button type="submit" title="{{ __('Logout') }}" class="p-1 text-slate-500 dark:text-slate-400 hover:text-rose-500 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-[10px] font-bold text-brand-600 dark:text-brand-400 hover:text-brand-500 px-1">{{ __('Login') }}</a>
                @endauth
            </div>
        </div>

        <!-- Main Content -->
        <main id="app-content" class="flex-1 px-4 pt-3">
            @yield('content')
        </main>

        @auth
            <!-- Bottom Navigation Bar -->
            <nav class="fixed bottom-0 w-full max-w-[430px] bg-white/95 dark:bg-[#0c1017]/90 backdrop-blur-xl border-t border-slate-200 dark:border-[#1a2233] px-2 py-2 flex items-center justify-around z-40 shadow-lg transition-colors duration-200">
                <!-- Dashboard Tab -->
                <a href="{{ route('dashboard') }}" 
                   class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="text-[10px]">{{ __('Home') }}</span>
                </a>

                <!-- Budgets Tab -->
                <a href="{{ route('budgets.index') }}" 
                   class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('budgets.*') ? 'text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    <span class="text-[10px]">{{ __('Budgets') }}</span>
                </a>

                <!-- Floating Quick Add Button (+) -->
                <button @click="modalOpen = true"
                        hx-get="{{ route('transactions.create') }}"
                        hx-target="#modal-body"
                        hx-swap="innerHTML"
                        class="w-11 h-11 -mt-5 rounded-full bg-gradient-to-tr from-brand-600 to-indigo-500 text-white flex items-center justify-center glow-btn transition-transform active:scale-95 border-2 border-white dark:border-[#0c1017] shadow-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                </button>

                <!-- Accounts Tab -->
                <a href="{{ route('accounts.index') }}" 
                   class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('accounts.*') ? 'text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="text-[10px]">{{ __('Accounts') }}</span>
                </a>

                <!-- Analytics Tab -->
                <a href="{{ route('analytics.index') }}" 
                   class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('analytics.*') ? 'text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-[10px]">{{ __('Analytics') }}</span>
                </a>
            </nav>

            <!-- Bottom Sheet Modal Backdrop & Container -->
            <div x-show="modalOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 bg-black/60 dark:bg-black/75 backdrop-blur-sm flex justify-center items-end"
                 style="display: none;">

                <!-- Overlay click outside -->
                <div class="absolute inset-0" @click="modalOpen = false"></div>

                <!-- Modal Content Sheet -->
                <div x-show="modalOpen"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="translate-y-0"
                     x-transition:leave-end="translate-y-full"
                     class="relative w-full max-w-[430px] bg-white dark:bg-[#121622] rounded-t-3xl border-t border-slate-200 dark:border-[#232d42] p-5 shadow-2xl max-h-[90vh] overflow-y-auto z-10 transition-colors duration-200"
                     @click.stop>
                    
                    <div class="w-12 h-1 bg-slate-300 dark:bg-slate-600/60 rounded-full mx-auto mb-4"></div>

                    <div id="modal-body">
                        <div class="py-12 text-center text-slate-500 dark:text-slate-400 flex flex-col items-center justify-center">
                            <svg class="animate-spin h-6 w-6 text-brand-600 dark:text-brand-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span class="text-xs">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        @endauth

    </div>

    <!-- Global Page Loader JS -->
    <script>
        window.showPageLoader = function(label) {
            const bar = document.getElementById('global-progress-bar');
            const badge = document.getElementById('global-page-loader');
            const text = document.getElementById('global-page-loader-text');
            if (bar) {
                bar.style.width = '70%';
                bar.classList.remove('opacity-0');
                bar.classList.add('opacity-100');
            }
            if (badge) {
                if (text) {
                    text.textContent = label ? ('{{ __('Loading...') }} ' + label) : '{{ __('Loading...') }}';
                }
                badge.classList.remove('opacity-0', '-translate-y-4');
                badge.classList.add('opacity-100', 'translate-y-0');
            }
        };

        window.hidePageLoader = function() {
            const bar = document.getElementById('global-progress-bar');
            const badge = document.getElementById('global-page-loader');
            if (bar) {
                bar.style.width = '100%';
                setTimeout(() => {
                    bar.classList.remove('opacity-100');
                    bar.classList.add('opacity-0');
                    setTimeout(() => { bar.style.width = '0%'; }, 250);
                }, 150);
            }
            if (badge) {
                badge.classList.remove('opacity-100', 'translate-y-0');
                badge.classList.add('opacity-0', '-translate-y-4');
            }
        };

        // Intercept standard link clicks for immediate tactile feedback
        document.addEventListener('click', function(e) {
            const a = e.target.closest('a');
            if (!a) return;
            const href = a.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || a.target === '_blank' || a.hasAttribute('download')) {
                return;
            }
            // If already handled by custom click or HTMX modal, skip
            if (a.hasAttribute('hx-get') || a.hasAttribute('hx-post') || a.getAttribute('@click')) {
                return;
            }
            const label = a.getAttribute('data-loader-title') || a.innerText?.trim().slice(0, 20) || '';
            window.showPageLoader(label);
        });

        // HTMX Loading lifecycle
        document.addEventListener('htmx:beforeRequest', function(e) {
            const bar = document.getElementById('global-progress-bar');
            if (bar) {
                bar.style.width = '65%';
                bar.classList.remove('opacity-0');
                bar.classList.add('opacity-100');
            }
        });

        document.addEventListener('htmx:afterRequest', function(e) {
            window.hidePageLoader();
        });

        window.addEventListener('pageshow', function() {
            window.hidePageLoader();
        });
    </script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.log('SW registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
