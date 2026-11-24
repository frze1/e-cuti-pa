import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo Private Channel Notification Listener.
 * Jika Laravel Echo diaktifkan, sistem mendengarkan PrivateChannel khusus
 * user penerima yang sedang login (App.Models.User.{id}), bukan public channel.
 */
document.addEventListener('DOMContentLoaded', () => {
    if (window.Echo && window.authUser && window.authUser.id) {
        window.Echo.private('App.Models.User.' + window.authUser.id)
            .notification((notification) => {
                if (typeof window.showToast === 'function') {
                    window.showToast({
                        title: notification.title || 'Pemberitahuan Cuti',
                        message: notification.body || notification.message || '',
                        type: notification.type || 'info'
                    });
                }
            });
    }
});
