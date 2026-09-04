<?php

namespace App\Notifications\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function __construct(private WhatsAppService $whatsApp)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $to = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (blank($to)) {
            return;
        }

        $this->whatsApp->send($to, $notification->toWhatsApp($notifiable));
    }
}
