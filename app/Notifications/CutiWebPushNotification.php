<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class CutiWebPushNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $actionUrl = '/pengajuan-cuti',
        public ?string $tag = 'cuti-notification',
        public ?string $type = 'info'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Jika user mematikan notifikasi, jangan kirim channel apapun
        $notifEnabled = $notifiable->notif_enabled ?? true;
        if (!$notifEnabled) {
            return [];
        }

        $channels = ['database'];

        // Tambahkan WebPush jika user memiliki push subscription aktif
        try {
            if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
                $channels[] = WebPushChannel::class;
            }
        } catch (\Throwable $e) {
            // Ignore push subscription check error and fallback to database
        }

        return $channels;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->actionUrl,
            'tag' => $this->tag,
            'type' => $this->type ?? 'info',
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Get the channels the event should broadcast on.
     * Mengarahkan broadcast ke PrivateChannel penerima (App.Models.User.{id}).
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Jika dikosongkan, Laravel BroadcastNotificationCreated secara default
        // mengarahkan ke new PrivateChannel('App.Models.User.' . $notifiable->id).
        return [];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'id' => $this->id ?? null,
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->actionUrl,
            'tag' => $this->tag,
            'type' => $this->type ?? 'info',
        ];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, object $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon('/favicon.ico')
            ->badge('/favicon.ico')
            ->action('Buka Berkas', 'open_url')
            ->data(['url' => $this->actionUrl])
            ->tag($this->tag)
            ->options([
                'TTL' => 86400,
                'urgency' => 'high',
            ]);
    }
}
