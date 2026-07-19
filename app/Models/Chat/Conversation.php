<?php

namespace App\Models\Chat;

use App\Enums\Chat\ConversationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'patient_id',
        'user_id',
        'status',
        'assigned_to_user_id',
        'assigned_at',
        'closed_at',
        'closed_by',
        'last_message_at',
        'last_sender_id',
        'last_message_preview',
        'admin_last_read_message_id',
        'patient_last_read_message_id',
    ];

    protected function casts(): array
    {
        return [
            'status'           => ConversationStatus::class,
            'assigned_at'      => 'datetime',
            'closed_at'        => 'datetime',
            'last_message_at'  => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Patient::class, 'patient_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'assigned_to_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'closed_by');
    }

    public function lastSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_sender_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * غير المقروء للأدمن = رسائل المريض التي id > آخر ما قرأه الأدمن.
     */
    public function unreadForAdmin(): int
    {
        return $this->messages()
            ->where('sender_type', \App\Models\Chat\Message::SENDER_PATIENT)
            ->when($this->admin_last_read_message_id, fn ($q) => $q->where('id', '>', $this->admin_last_read_message_id))
            ->count();
    }

    /**
     * غير المقروء للمريض = رسائل الأدمن التي id > آخر ما قرأه المريض.
     */
    public function unreadForPatient(): int
    {
        return $this->messages()
            ->where('sender_type', \App\Models\Chat\Message::SENDER_ADMIN)
            ->when($this->patient_last_read_message_id, fn ($q) => $q->where('id', '>', $this->patient_last_read_message_id))
            ->count();
    }

    public function isOpen(): bool
    {
        return $this->status === ConversationStatus::Open;
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to_user_id);
    }

    public function isAssignedTo($user): bool
    {
        return (int) $this->assigned_to_user_id === (int) ($user->id ?? 0);
    }
}
