<div x-data="toastNotificationComponent()"
     x-cloak
     @toast.window="addToast($event.detail)"
     class="fixed bottom-5 right-5 z-[9998] flex flex-col items-end gap-2.5 max-w-sm sm:max-w-md w-full pointer-events-none px-4 sm:px-0"
     role="region"
     aria-label="Pemberitahuan Sistem">

    <template x-for="item in toasts" :key="item.id">
        <div x-show="item.visible"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-4 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100 scale-100"
             x-transition:leave-end="translate-x-full opacity-0 scale-90"
             @mouseenter="pauseToast(item)"
             @mouseleave="resumeToast(item)"
             class="pointer-events-auto relative w-full bg-white dark:bg-slate-800 rounded-none shadow-xl shadow-slate-900/10 dark:shadow-black/40 border border-slate-300 dark:border-slate-700 p-4 overflow-hidden backdrop-blur-md">
            
            <div class="flex items-start gap-3.5">
                <!-- Status Icon Badge -->
                <div class="w-10 h-10 rounded-none shrink-0 flex items-center justify-center transition-colors"
                     :class="{
                         'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/70 dark:text-emerald-400 ring-2 ring-emerald-500/20': item.type === 'success',
                         'bg-rose-100 text-rose-600 dark:bg-rose-950/70 dark:text-rose-400 ring-2 ring-rose-500/20': item.type === 'error',
                         'bg-amber-100 text-amber-600 dark:bg-amber-950/70 dark:text-amber-400 ring-2 ring-amber-500/20': item.type === 'warning',
                         'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/70 dark:text-indigo-400 ring-2 ring-indigo-500/20': item.type === 'info'
                     }">
                    
                    <!-- Success Icon -->
                    <template x-if="item.type === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </template>

                    <!-- Error Icon -->
                    <template x-if="item.type === 'error'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </template>

                    <!-- Warning Icon -->
                    <template x-if="item.type === 'warning'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </template>

                    <!-- Info Icon -->
                    <template x-if="item.type === 'info'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>
                </div>

                <!-- Text Body -->
                <div class="flex-1 min-w-0 pr-6">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white leading-tight" 
                        x-text="item.title"></h4>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line" 
                       x-text="item.message"></p>
                </div>
            </div>

            <!-- Close Button (X) -->
            <button type="button"
                    @click="removeToast(item.id)"
                    class="absolute top-3 right-3 p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-300 dark:hover:bg-slate-700/60 transition focus:outline-none"
                    aria-label="Tutup notifikasi">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Bottom Progress Countdown Bar -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-slate-100 dark:bg-slate-700/50">
                <div class="h-full transition-all duration-100 linear"
                     :class="{
                         'bg-emerald-500': item.type === 'success',
                         'bg-rose-500': item.type === 'error',
                         'bg-amber-500': item.type === 'warning',
                         'bg-indigo-500': item.type === 'info'
                     }"
                     :style="`width: ${item.progress}%;`"></div>
            </div>
        </div>
    </template>

</div>

<script>
    function toastNotificationComponent() {
        return {
            toasts: [],

            init() {
                // Read flash session messages injected from Laravel
                @if(session('success'))
                    this.addToast({
                        type: 'success',
                        title: 'Aksi Berhasil!',
                        message: @json(session('success')),
                        duration: 4000
                    });
                @endif

                @if(session('error'))
                    this.addToast({
                        type: 'error',
                        title: 'Gagal / Perhatian!',
                        message: @json(session('error')),
                        duration: 5000
                    });
                @endif

                @if(session('warning'))
                    this.addToast({
                        type: 'warning',
                        title: 'Peringatan',
                        message: @json(session('warning')),
                        duration: 4000
                    });
                @endif

                @if(session('status'))
                    this.addToast({
                        type: 'info',
                        title: 'Informasi Status',
                        message: @json(
                            session('status') === 'profile-updated' ? 'Profil pengguna berhasil diperbarui.' :
                            (session('status') === 'password-updated' ? 'Kata sandi berhasil diperbarui.' :
                            (session('status') === 'verification-link-sent' ? 'Tautan verifikasi email telah dikirim.' : session('status')))
                        ),
                        duration: 4000
                    });
                @endif
            },

            addToast(payload) {
                const id = Date.now() + Math.random().toString(36).substring(2, 6);
                const type = payload.type || 'info';
                let defaultTitle = 'Pemberitahuan';
                if (type === 'success') defaultTitle = 'Aksi Berhasil!';
                else if (type === 'error') defaultTitle = 'Gagal!';
                else if (type === 'warning') defaultTitle = 'Perhatian';

                const duration = payload.duration || (type === 'error' ? 5000 : 4000);

                const item = {
                    id: id,
                    type: type,
                    title: payload.title || defaultTitle,
                    message: payload.message || '',
                    duration: duration,
                    remaining: duration,
                    progress: 100,
                    visible: true,
                    paused: false,
                    timerId: null,
                    intervalId: null
                };

                this.toasts.push(item);
                this.startCountdown(item);
            },

            startCountdown(item) {
                const step = 50;
                item.intervalId = setInterval(() => {
                    if (item.paused) return;

                    item.remaining -= step;
                    item.progress = Math.max(0, (item.remaining / item.duration) * 100);

                    if (item.remaining <= 0) {
                        clearInterval(item.intervalId);
                        this.removeToast(item.id);
                    }
                }, step);
            },

            pauseToast(item) {
                item.paused = true;
            },

            resumeToast(item) {
                item.paused = false;
            },

            removeToast(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    const item = this.toasts[index];
                    if (item.intervalId) clearInterval(item.intervalId);
                    item.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 250);
                }
            }
        };
    }

    // Global helper for dispatching toast notification from any JavaScript code
    window.showToast = function(options = {}) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                type: options.type || 'info',
                title: options.title || '',
                message: options.message || '',
                duration: options.duration
            }
        }));
    };
</script>
