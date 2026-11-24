<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Profil Pengguna') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola data diri pengguna, kata sandi, dan preferensi notifikasi
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">
        <!-- Card Profil & Password Dibuat Bersebelahan (Grid 2 Kolom di Desktop/Tablet) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
            <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-xs rounded-none border border-slate-300 dark:border-slate-700">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-xs rounded-none border border-slate-300 dark:border-slate-700">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-xs rounded-none border border-slate-300 dark:border-slate-700">
            @include('profile.partials.notification-preferences-form')
        </div>

        <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 shadow-xs rounded-none border border-slate-300 dark:border-slate-700">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
