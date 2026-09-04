<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-task-reminders')]
#[Description('Send due reminder and overdue notifications for pending tasks')]
class SendTaskReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $dueReminders = Task::query()
            ->with('user')
            ->whereNotNull('reminder_at')
            ->whereNull('reminder_sent_at')
            ->where('reminder_at', '<=', now())
            ->where('status', 'pending')
            ->get();

        foreach ($dueReminders as $task) {
            $task->user->notify(new TaskReminder($task, 'reminder'));
            $task->update(['reminder_sent_at' => now()]);
        }

        $newlyOverdue = Task::query()
            ->with('user')
            ->whereNotNull('due_date')
            ->whereNull('overdue_notified_at')
            ->where('due_date', '<', now())
            ->where('status', 'pending')
            ->get();

        foreach ($newlyOverdue as $task) {
            $task->user->notify(new TaskReminder($task, 'overdue'));
            $task->update(['overdue_notified_at' => now()]);
        }

        $this->info(sprintf(
            'Sent %d reminder(s) and %d overdue notification(s).',
            $dueReminders->count(),
            $newlyOverdue->count()
        ));
    }
}
