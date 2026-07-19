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

    public const SENDER_ADMIN   = 'admin';
    public const SENDER_PATIENT = 'patient';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
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
        return $this->isFromAdmin() ? $this->senderAdmin : $this->senderPatient;
    }

    public function isFromAdmin(): bool
    {
        // المصدر الموثوق: العمود الصريح sender_type
        if ($this->sender_type !== null) {
            return $this->sender_type === self::SENDER_ADMIN;
        }

        // fallback للبيانات القديمة قبل ترحيل sender_type: التمييز عبر مالك المحادثة
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

        // رابط موقّت موقّع (صالح 30 دقيقة) لعرض المرفق الطبي من القرص الخاص دون كشفه للعامة
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'chat.attachment',
            now()->addMinutes(30),
            ['message' => $this->id]
        );
    }
}
