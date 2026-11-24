<?php

namespace App\Http\Controllers;

use App\Notifications\CutiWebPushNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushSubscriptionController extends Controller
{
    /**
     * Get the VAPID Public Key for client-side subscription.
     */
    public function getPublicKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => config('webpush.vapid.public_key'),
        ]);
    }

    /**
     * Store or update a user's web push subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'content_encoding' => 'nullable|string',
        ]);

        $endpoint = $validated['endpoint'];
        $key = $validated['keys']['p256dh'];
        $token = $validated['keys']['auth'];
        $contentEncoding = $validated['content_encoding'] ?? 'aes128gcm';

        $request->user()->updatePushSubscription($endpoint, $key, $token, $contentEncoding);

        return response()->json([
            'success' => true,
            'message' => 'Perangkat Anda berhasil didaftarkan untuk menerima notifikasi sistem.',
        ]);
    }

    /**
     * Remove a web push subscription.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $request->user()->deletePushSubscription($request->endpoint);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi untuk perangkat ini telah dinonaktifkan.',
        ]);
    }

    /**
     * Send a test push notification to verify OS-level pop-ups.
     */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->pushSubscriptions()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada perangkat yang terdaftar untuk akun ini. Silakan aktifkan notifikasi terlebih dahulu.',
            ], 422);
        }

        try {
            $user->notify(new CutiWebPushNotification(
                title: 'Uji Coba Notifikasi Sistem Manajemen Cuti',
                body: 'Halo ' . $user->name . ', notifikasi Windows/perangkat Anda berhasil terhubung dengan sistem!',
                actionUrl: route('cuti.riwayat'),
                tag: 'test-notification-' . time()
            ));

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi uji coba berhasil dikirim. Periksa pop-up pojok kanan bawah desktop Anda!',
            ]);
        } catch (\Throwable $e) {
            Log::error('WebPush test notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi uji coba: ' . $e->getMessage(),
            ], 500);
        }
    }
}
