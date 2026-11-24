<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Tampilkan riwayat notifikasi (JSON untuk dropdown bell atau View untuk halaman riwayat)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->wantsJson() || $request->ajax()) {
            $notifications = $user->notifications()->take(25)->get()->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Pemberitahuan Sistem',
                    'body' => $n->data['body'] ?? '',
                    'action_url' => $n->data['action_url'] ?? '#',
                    'type' => $n->data['type'] ?? 'info',
                    'read_at' => $n->read_at ? $n->read_at->toISOString() : null,
                    'is_read' => !is_null($n->read_at),
                    'created_at_human' => $n->created_at->diffForHumans(),
                    'created_at_formatted' => $n->created_at->isoFormat('D MMM YYYY, HH:mm'),
                ];
            });

            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count(),
                'notif_enabled' => (bool) ($user->notif_enabled ?? true),
                'notifications' => $notifications,
            ]);
        }

        $allNotifications = $user->notifications()->paginate(15);
        return view('notifications.index', [
            'notifications' => $allNotifications,
            'unreadCount' => $user->unreadNotifications()->count(),
            'notifEnabled' => (bool) ($user->notif_enabled ?? true),
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => Auth::user()->unreadNotifications()->count(),
            ]);
        }

        $actionUrl = $notification->data['action_url'] ?? null;
        if ($actionUrl && $actionUrl !== '#') {
            return redirect($actionUrl);
        }

        return back();
    }

    /**
     * Tandai seluruh notifikasi user sebagai telah dibaca
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
                'message' => 'Semua notifikasi telah ditandai sebagai dibaca.',
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Pengaturan ON/OFF notifikasi aplikasi & push untuk akun pengguna
     */
    public function toggleSetting(Request $request)
    {
        $user = Auth::user();

        if ($request->has('enabled')) {
            $user->notif_enabled = filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN);
        } else {
            $user->notif_enabled = !($user->notif_enabled ?? true);
        }

        $user->save();

        $statusText = $user->notif_enabled ? 'diaktifkan (ON)' : 'dinonaktifkan (OFF)';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'notif_enabled' => (bool) $user->notif_enabled,
                'message' => "Notifikasi sistem berhasil {$statusText}.",
            ]);
        }

        return back()->with('success', "Pengaturan notifikasi berhasil {$statusText}.");
    }

    /**
     * Hapus satu notifikasi
     */
    public function destroy(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => Auth::user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Bersihkan semua riwayat notifikasi milik user
     */
    public function clearAll(Request $request)
    {
        Auth::user()->notifications()->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
                'message' => 'Riwayat notifikasi telah dibersihkan.',
            ]);
        }

        return back()->with('success', 'Riwayat notifikasi telah dibersihkan.');
    }
}
