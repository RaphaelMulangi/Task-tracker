<?php

namespace App\Notifications;

use App\Models\Task;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminder extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $reason = 'reminder',
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * WhatsApp only goes out when the user has a whatsapp_number saved;
     * WhatsAppChannel itself no-ops if WHATSAPP_TOKEN isn't configured yet.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        if (filled($notifiable->whatsapp_number)) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->reason === 'overdue'
                ? "Task overdue: {$this->task->title}"
                : "Reminder: {$this->task->title}")
            ->greeting("Hi {$notifiable->name},");

        if ($this->reason === 'overdue') {
            $message->line("Your task \"{$this->task->title}\" is now overdue.");
        } else {
            $message->line("This is a reminder for your task \"{$this->task->title}\".");
        }

        if ($this->task->due_date) {
            $message->line('Due: ' . $this->task->due_date->format('D, d M Y H:i'));
        }

        return $message->action('View Task', url('/'))
            ->line('Thanks for using Task Tracker!');
    }

    public function toWhatsApp(object $notifiable): string
    {
        $lines = [
            $this->reason === 'overdue'
                ? "⏰ Task overdue: {$this->task->title}"
                : "🔔 Reminder: {$this->task->title}",
        ];

        if ($this->task->due_date) {
            $lines[] = 'Due: ' . $this->task->due_date->format('D, d M Y H:i');
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'reason' => $this->reason,
            'due_date' => $this->task->due_date?->toIso8601String(),
        ];
    }
}
