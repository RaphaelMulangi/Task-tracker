<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

#[Fillable([
    'title', 'description', 'due_date', 'reminder_at', 'priority', 'status',
    'completed_at', 'reminder_sent_at', 'overdue_notified_at',
])]
class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'reminder_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'overdue_notified_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected $appends = ['is_overdue', 'computed_status'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'completed') {
            return 'completed';
        }

        return $this->is_overdue ? 'overdue' : 'pending';
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }
}
