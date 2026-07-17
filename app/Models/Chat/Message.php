<?php

namespace App\Models\Chat;

use App\Enums\Chat\AttachmentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'is_system',
        'body',
        'attachment_disk',
        'attachment_path',
        'attachment_mime',
        'attachment_type',
        'attachment_name',
        'attachment_size',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_system'       => 'boolean',
            'attachment_type' => AttachmentType::class,
            'edited_at'        => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function senderAdmin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'sender_id');
    }

    public function senderPatient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Patient::class, 'sender_id');
    }

    public function getSenderAttribute()
    {
        if ($this->relationLoaded('senderAdmin') && $this->senderAdmin) {
            return $this->senderAdmin;
        }
        if ($this->relationLoaded('senderPatient') && $this->senderPatient) {
            return $this->senderPatient;
        }
        if ($this->relationLoaded('conversation') && $this->conversation) {
            $patientId = $this->conversation->patient_id ?: $this->conversation->user_id;
            if ($patientId && (int) $this->sender_id === (int) $patientId) {
                return $this->senderPatient;
            }
        }
        return $this->senderAdmin;
    }

    public function isFromAdmin(): bool
    {
        if ($this->relationLoaded('senderAdmin') && $this->senderAdmin) {
            return true;
        }
        if ($this->relationLoaded('senderPatient') && $this->senderPatient) {
            return false;
        }
        if ($this->relationLoaded('conversation') && $this->conversation) {
            $patientId = $this->conversation->patient_id ?: $this->conversation->user_id;
            if ($patientId && (int) $this->sender_id === (int) $patientId) {
                return false;
            }
        }
        return true;
    }

    public function isSystem(): bool
    {
        return (bool) $this->is_system;
    }

    public function attachmentUrl(): ?string
    {
        if (!$this->attachment_path)
            return null;

        return \Illuminate\Support\Facades\Storage::disk($this->attachment_disk ?: 'public')->url($this->attachment_path);
    }
}
