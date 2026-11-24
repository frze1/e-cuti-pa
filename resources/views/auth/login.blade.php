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

    <title>Masuk - Sistem Manajemen Cuti</title>
    
    <!-- Memuat Tailwind CSS bawaan Laravel (Local Fonts Bundled in app.css) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-900 antialiased bg-slate-100">

    <!-- 1. Latar Belakang Halaman & Pemusatan (Body/Wrapper) -->
    <div class="min-h-screen bg-slate-100 flex items-center justify-center p-4 sm:p-8">
        
        <!-- 2. Pembuatan Wadah Card Utama (Centered Card Split-Screen) -->
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
            
            <!-- 3. Sisi Kiri: Panel Informasi & Branding (Hanya Desktop) -->
            <div class="hidden md:flex md:w-1/2 bg-dinas-green text-white flex-col justify-center items-center p-10 lg:p-12 relative overflow-hidden">
                <!-- Elemen Ornamen Estetik Latar Belakang -->
                <div class="absolute inset-0 bg-gradient-to-br from-dinas-green via-dinas-green to-dinas-greenhover opacity-95"></div>
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-dinas-gold/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-dinas-gold/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <!-- Pola Garis / Grid Halus -->
                <svg class="absolute inset-0 w-full h-full opacity-10 pointer-events-none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid-pattern)" />
                </svg>

                <!-- Isi Panel Kiri -->
                <div class="relative z-10 flex flex-col items-center text-center max-w-sm">
                    <!-- Logo Instansi -->
                    <img src="{{ asset('images/pa-logo.png') }}" alt="Logo Pengadilan Agama" class="w-20 h-20 md:w-24 md:h-24 object-contain drop-shadow-md mb-4">

                    <!-- Teks Judul -->
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">
                        Sistem Manajemen Cuti
                    </h1>

                    <!-- Ornamen Garis Bawah Emas -->
                    <div class="w-16 h-1 bg-dinas-gold rounded-full my-4"></div>

                    <!-- Teks Sub-judul -->
                    <p class="text-dinas-gold text-sm text-center font-medium leading-relaxed max-w-xs">
                        Layanan Administrasi Kepegawaian Internal Pengadilan Agama
                    </p>
                </div>
            </div>

            <!-- 4. Sisi Kanan: Panel Fungsional (Form Login) -->
            <div class="w-full md:w-1/2 p-8 sm:p-10 lg:p-12 flex flex-col justify-center">
                
                <!-- Logo Instansi Kecil di Layar Mobile (block md:hidden) -->
                <img src="{{ asset('images/pa-logo.png') }}" alt="Logo PA" class="block md:hidden w-16 h-16 object-contain mx-auto mb-6">

                <!-- Teks Selamat Datang (Tampil di Mobile dan Desktop) -->
                <div class="mb-6 text-center md:text-left">
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang</h2>
                    <p class="text-sm text-slate-500 mt-1">Silakan masuk ke akun kepegawaian Anda.</p>
                </div>

                <!-- Status Session (Misal: Pesan sukses reset password) -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Peringatan Kesalahan Login (Global Form Alert) -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-md flex items-start gap-3 text-rose-800 text-xs shadow-xs animate-shake">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-bold text-sm text-rose-900">Gagal Masuk!</p>
                            <p class="mt-0.5 text-rose-700">
                                {{ $errors->first('email') ?: $errors->first('password') ?: 'Silakan periksa kembali data isian login Anda.' }}
                            </p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Input Email -->
                    <div>
                        <label for="email" class="block font-medium text-sm text-slate-700">Alamat Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Masukkan alamat email" 
                            class="block mt-1 w-full px-4 py-2 border border-slate-300 focus:border-dinas-green focus:ring-dinas-green rounded-md shadow-xs bg-white text-sm text-slate-900 placeholder-slate-400 transition-colors @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-200 bg-rose-50/20 @enderror" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-600 font-medium" />
                    </div>

                    <!-- Input Password -->
                    <div>
                        <label for="password" class="block font-medium text-sm text-slate-700">Kata Sandi</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" 
                            class="block mt-1 w-full px-4 py-2 border border-slate-300 focus:border-dinas-green focus:ring-dinas-green rounded-md shadow-xs bg-white text-sm text-slate-900 placeholder-slate-400 transition-colors @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-200 bg-rose-50/20 @enderror" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-600 font-medium" />
                    </div>

                    <!-- Checkbox Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-dinas-green shadow-xs focus:ring-dinas-green cursor-pointer">
                            <span class="ml-2 text-sm text-slate-600 select-none">Ingat Saya</span>
                        </label>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-dinas-green hover:bg-dinas-greenhover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-dinas-green transition-colors">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

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
