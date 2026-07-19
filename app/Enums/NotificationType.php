<?php

namespace App\Enums;

enum NotificationType: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case AWAITING_TECHNICIAN = 'awaiting_technician';
    case TECHNICIAN_ASSIGNED = 'technician_assigned';
    case ON_THE_WAY = 'on_the_way';
    case SAMPLE_COLLECTED = 'sample_collected';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RESULT_READY = 'result_ready';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'طلب جديد',
            self::CONFIRMED => 'تم تأكيد الطلب',
            self::AWAITING_TECHNICIAN => 'بانتظار الفني',
            self::TECHNICIAN_ASSIGNED => 'تم تعيين فني',
            self::ON_THE_WAY => 'الفني في الطريق',
            self::SAMPLE_COLLECTED => 'تم سحب العينة',
            self::IN_PROGRESS => 'قيد المعالجة بالمختبر',
            self::COMPLETED => 'اكتمل الطلب',
            self::CANCELLED => 'تم إلغاء الطلب',
            self::RESULT_READY => 'النتيجة جاهزة',
        };
    }
}

