<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case AwaitingTechnician = 'awaiting_technician';
    case TechnicianAssigned = 'technician_assigned';
    case OnTheWay = 'on_the_way';
    case SampleCollected = 'sample_collected';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'قيد المراجعة',
            self::Confirmed => 'مؤكد',
            self::AwaitingTechnician => 'بانتظار تخصيص فني',
            self::TechnicianAssigned => 'تم تخصيص فني',
            self::OnTheWay => 'الفني في الطريق',
            self::SampleCollected => 'تم سحب العينة',
            self::InProgress => 'قيد التحليل في المختبر',
            self::Completed => 'مكتمل والنتائج جاهزة',
            self::Cancelled => 'ملغي',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::AwaitingTechnician => 'warning',
            self::TechnicianAssigned => 'primary',
            self::OnTheWay => 'primary',
            self::SampleCollected => 'secondary',
            self::InProgress => 'info',
            self::Completed => 'success',
            self::Cancelled => 'error',
        };
    }
}
