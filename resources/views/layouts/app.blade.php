<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%230A773A%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><rect x=%223%22 y=%224%22 width=%2218%22 height=%2218%22 rx=%222%22 ry=%222%22></rect><line x1=%2216%22 y1=%222%22 x2=%2216%22 y2=%226%22></line><line x1=%228%22 y1=%222%22 x2=%228%22 y2=%226%22></line><line x1=%223%22 y1=%2210%22 x2=%2221%22 y2=%2210%22></line></svg>">
        
        <!-- PWA Web App Manifest & Mobile Meta Tags -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#0A773A">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="e-Cuti">
        <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

        <title>{{ isset($header) ? trim(strip_tags($header)) . ' - ' : '' }}{{ config('app.name', 'Sistem Manajemen Cuti') }}</title>

        <!-- Scripts & Styles (Local Fonts Bundled in app.css) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Prevent Alpine.js Flash of Uncloaked Content (FOUC) -->
        <style>
            [x-cloak] { display: none !important; }
        </style>

        <!-- Authenticated User Context for Client-Side Scripts (Echo & WebPush) -->
        @auth
        <script>
            window.authUser = {
                id: {{ Auth::id() }},
                name: @json(Auth::user()->name),
                role: @json(Auth::user()->role)
            };
        </script>
        @endauth

        <!-- Theme Initialization Script (Default: Light Mode) -->
        <script>
            if (localStorage.theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body class="font-sans antialiased text-slate-800 dark:text-slate-100">
        <!-- Main Application Wrapper with Sidebar Navigation -->
        <div x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false" class="flex h-screen bg-gray-100 dark:bg-slate-900 overflow-hidden">
            <!-- Sidebar Component (Left) -->
            @include('layouts.navigation')

            <!-- Main Container (Right) -->
            <div class="flex-1 flex flex-col overflow-hidden min-w-0">
                <!-- Header (Top Bar) -->
                @isset($header)
                    <header class="relative z-30 bg-white dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700 shrink-0">
                        <div class="px-3 sm:px-6 lg:px-8 py-2 sm:py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                <!-- Mobile Hamburger Toggle -->
                                <button type="button" 
                                    @click="sidebarOpen = true" 
                                    class="md:hidden p-1.5 sm:p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none transition"
                                    title="Buka Menu Navigasi"
                                    aria-label="Buka Menu Navigasi">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                    </svg>
                                </button>
                                <div class="flex-1 min-w-0">
                                    {{ $header }}
                                </div>
                            </div>
                            
                            <!-- Header Action Tools (Push Notification Bell) -->
                            <div class="flex items-center gap-1.5 sm:gap-2 pl-2 sm:pl-3 shrink-0">
                                <x-push-notification-bell />
                            </div>
                        </div>
                    </header>
                @else
                    <!-- Fallback Mobile Header if page has no $header slot -->
                    <div class="md:hidden relative z-30 bg-white dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700 px-4 py-3 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <button type="button" 
                                @click="sidebarOpen = true" 
                                class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none transition"
                                title="Buka Menu Navigasi"
                                aria-label="Buka Menu Navigasi">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            <span class="font-bold text-sm text-slate-800 dark:text-white">Sistem Manajemen Cuti</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-push-notification-bell />
                        </div>
                    </div>
                @endisset

                <!-- Main Content Area -->
                <main class="flex-1 overflow-y-auto px-4 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Global Custom Confirmation Modal & Toast Notification -->
        <x-confirm-modal />
        <x-toast-notification />

        @if($errors->any())
            <!-- Picu komponen Toast Error di sini -->
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" class="fixed bottom-4 right-4 bg-red-600 text-white px-4 py-3 rounded shadow-lg flex items-center gap-3 z-50 transition-opacity">
                <!-- Ikon Warning -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="font-bold text-sm">Gagal Menyimpan!</p>
                    <p class="text-xs">Silakan periksa kembali isian form yang ditandai merah.</p>
                </div>
                <button @click="show = false" class="ml-auto text-white hover:text-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        <!-- Theme Toggle Script -->
        <script>
            function toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        </script>

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').then((reg) => {
                        console.log('PWA ServiceWorker registered with scope: ', reg.scope);
                    }).catch((err) => {
                        console.warn('PWA ServiceWorker registration failed: ', err);
                    });
                });
            }
        </script>
    </body>
</html>
