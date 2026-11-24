<div x-data="confirmModalComponent()"
     x-cloak
     @open-confirm.window="openModal($event.detail)"
     @keydown.escape.window="if(isOpen) close()"
     class="relative z-[9999]"
     aria-labelledby="confirm-modal-title"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop Blur Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="if(!loading) close()"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         style="display: none;"></div>

    <!-- Modal Dialog Placement (Centered) -->
    <div x-show="isOpen"
         class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
         style="display: none;">
        
        <div x-show="isOpen"
             style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop
             class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-none shadow-2xl border border-slate-300 dark:border-slate-700 p-5 sm:p-6 overflow-hidden">
            
            <!-- Close Button (X) -->
            <button type="button"
                    @click="close()"
                    :disabled="loading"
                    class="absolute top-4 right-4 p-1.5 rounded-none text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:text-slate-300 dark:hover:bg-slate-700/60 transition focus:outline-none"
                    aria-label="Tutup dialog">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="flex items-start gap-4">
                <!-- Icon badge depending on type -->
                <div class="w-12 h-12 rounded-none shrink-0 flex items-center justify-center transition-colors"
                     :class="{
                         'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 ring-4 ring-rose-50 dark:ring-rose-950/30': type === 'danger',
                         'bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 ring-4 ring-amber-50 dark:ring-amber-950/30': type === 'warning',
                         'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 ring-4 ring-indigo-50 dark:ring-indigo-950/30': type === 'info'
                     }">
                    
                    <!-- Danger Icon (Trash / Alert) -->
                    <template x-if="type === 'danger'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </template>

                    <!-- Warning Icon (Exit / Door / Exclamation) -->
                    <template x-if="type === 'warning'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </template>

                    <!-- Info Icon (Question / Info) -->
                    <template x-if="type === 'info'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </template>
                </div>

                <!-- Text Content -->
                <div class="flex-1 min-w-0 pr-2">
                    <h3 id="confirm-modal-title" 
                        class="text-base sm:text-lg font-bold text-slate-800 dark:text-white leading-snug" 
                        x-text="title"></h3>
                    <p class="mt-1.5 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed" 
                       x-text="message"></p>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 sm:gap-3">
                <button type="button"
                        @click="close()"
                        :disabled="loading"
                        class="w-full sm:w-auto px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/80 dark:hover:bg-slate-700 rounded-none border border-slate-300 dark:border-slate-600 transition focus:outline-none disabled:opacity-50 text-center"
                        x-text="cancelText">
                </button>
                <button type="button"
                        @click="handleConfirm()"
                        :disabled="loading"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs sm:text-sm font-semibold text-white rounded-none shadow-xs transition focus:outline-none disabled:opacity-50 text-center"
                        :class="{
                            'bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-500/20 shadow-red-500/20': type === 'danger',
                            'bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:ring-amber-500/20 shadow-amber-500/20': type === 'warning',
                            'bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-500/20 shadow-indigo-500/20': type === 'info'
                        }">
                    <!-- Loading Spinner -->
                    <svg x-show="loading" class="animate-spin -ml-0.5 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmModalComponent() {
        return {
            isOpen: false,
            loading: false,
            title: 'Konfirmasi Tindakan',
            message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Batal',
            type: 'danger',
            targetForm: null,
            callback: null,

            openModal(detail) {
                this.title = detail.title || 'Konfirmasi Tindakan';
                this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                this.confirmText = detail.confirmText || 'Ya, Lanjutkan';
                this.cancelText = detail.cancelText || 'Batal';
                this.type = detail.type || 'danger';
                this.targetForm = detail.targetForm || null;
                this.callback = detail.callback || null;
                this.loading = false;
                this.isOpen = true;
            },

            close() {
                if (this.loading) return;
                this.isOpen = false;
                this.targetForm = null;
                this.callback = null;
            },

            handleConfirm() {
                this.loading = true;

                if (this.callback && typeof this.callback === 'function') {
                    try {
                        const result = this.callback();
                        if (result instanceof Promise) {
                            result.finally(() => {
                                this.loading = false;
                                this.isOpen = false;
                            });
                            return;
                        }
                    } catch (e) {
                        console.error('Error during modal confirmation callback:', e);
                    }
                    this.loading = false;
                    this.isOpen = false;
                } else if (this.targetForm) {
                    this.targetForm._confirmed = true;
                    HTMLFormElement.prototype.submit.call(this.targetForm);
                } else {
                    this.loading = false;
                    this.isOpen = false;
                }
            }
        };
    }

    // Global helper for opening the confirmation modal
    window.confirmDialog = function(options = {}) {
        window.dispatchEvent(new CustomEvent('open-confirm', {
            detail: {
                title: options.title || 'Konfirmasi Tindakan',
                message: options.message || 'Apakah Anda yakin?',
                confirmText: options.confirmText || 'Ya, Lanjutkan',
                cancelText: options.cancelText || 'Batal',
                type: options.type || 'danger',
                callback: options.onConfirm || null,
                targetForm: options.targetForm || null
            }
        }));
    };

    // Auto-intercept forms with data-confirm attribute
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;

        if (form.hasAttribute('data-confirm')) {
            if (form._confirmed) {
                return; // Let verified submission pass
            }

            e.preventDefault();
            e.stopPropagation();

            const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const rawMsg = form.getAttribute('data-confirm-message') || form.getAttribute('data-confirm');
            const message = (rawMsg && rawMsg !== 'true') ? rawMsg : 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            const confirmText = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
            const cancelText = form.getAttribute('data-confirm-cancel') || 'Batal';
            const type = form.getAttribute('data-confirm-type') || 'danger';

            window.confirmDialog({
                title: title,
                message: message,
                confirmText: confirmText,
                cancelText: cancelText,
                type: type,
                targetForm: form
            });
        }
    }, true);
</script>
