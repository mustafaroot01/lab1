<?php

namespace App\Services\Notifications;

use App\Models\Order;
use App\Enums\NotificationType;

class NotificationBuilder
{
    public static function build(NotificationType $type, $model = null): array
    {
        return match($type) {
            NotificationType::PENDING => self::orderPending($model),
            NotificationType::CONFIRMED => self::orderConfirmed($model),
            NotificationType::AWAITING_TECHNICIAN => self::awaitingTechnician($model),
            NotificationType::TECHNICIAN_ASSIGNED => self::technicianAssigned($model),
            NotificationType::ON_THE_WAY => self::technicianOnWay($model),
            NotificationType::SAMPLE_COLLECTED => self::sampleCollected($model),
            NotificationType::IN_PROGRESS => self::inProgress($model),
            NotificationType::COMPLETED => self::orderCompleted($model),
            NotificationType::CANCELLED => self::orderCancelled($model),
            NotificationType::RESULT_READY => self::resultReady($model),
        };
    }

    public static function orderPending(Order $order): array
    {
        return [
            'title' => 'طلب جديد',
            'body' => "تم استلام طلبك بنجاح برقم #{$order->id} وسنقوم بمراجعته قريباً.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::PENDING->value,
                'screen' => 'order_details',
            ]
        ];
    }

    public static function orderConfirmed(Order $order): array
    {
        return [
            'title' => 'تم تأكيد الطلب',
            'body' => "تم تأكيد طلبك رقم #{$order->id} وسيتم تعيين فني لك قريباً.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::CONFIRMED->value,
                'screen' => 'order_details',
            ]
        ];
    }

    public static function awaitingTechnician(Order $order): array
    {
        return [
            'title' => 'بانتظار الفني',
            'body' => "طلبك رقم #{$order->id} الآن في قائمة الانتظار لتخصيص فني ميداني.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::AWAITING_TECHNICIAN->value,
                'screen' => 'order_details',
            ]
        ];
    }

    public static function technicianAssigned(Order $order): array
    {
        $techName = $order->technician ? $order->technician->name : 'فني';
        return [
            'title' => 'تم تعيين فني',
            'body' => "تم تعيين الفني {$techName} لطلبك رقم #{$order->id}.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::TECHNICIAN_ASSIGNED->value,
                'screen' => 'order_details',
            ]
        ];
    }

    public static function technicianOnWay(Order $order): array
    {
        $techName = $order->technician ? $order->technician->name : 'الفني';
        return [
            'title' => 'الفني في الطريق',
            'body' => "{$techName} في الطريق إليك لسحب العينة. يرجى الاستعداد.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::ON_THE_WAY->value,
                'screen' => 'order_details',
            ]
        ];
    }

    public static function sampleCollected(Order $order): array
    {
        return [
            'title' => 'تم سحب العينة',
            'body' => "تم سحب العينة بنجاح لطلبك رقم #{$order->id}. سنقوم بإعلامك فور ظهور النتيجة.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::SAMPLE_COLLECTED->value,
                'screen' => 'order_details',
            ]
        ];
    }

    public static function inProgress(Order $order): array
    {
        return [
            'title' => 'قيد المعالجة',
            'body' => "عينتك للطلب رقم #{$order->id} قيد الفحص والمعالجة في المختبر الآن.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::IN_PROGRESS->value,
                'screen' => 'order_details',
            ]
        ];
    }

    public static function orderCompleted(Order $order): array
    {
        return [
            'title' => 'اكتمل الطلب',
            'body' => "تم اكتمال جميع إجراءات طلبك رقم #{$order->id} بنجاح.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::COMPLETED->value,
                'screen' => 'order_details',
            ]
        ];
    }

    public static function resultReady(Order $order): array
    {
        return [
            'title' => 'النتيجة جاهزة',
            'body' => "نتيجة التحليل لطلبك رقم #{$order->id} أصبحت جاهزة، يمكنك الاطلاع عليها الآن.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::RESULT_READY->value,
                'screen' => 'result_details',
            ]
        ];
    }

    public static function orderCancelled(Order $order): array
    {
        return [
            'title' => 'إلغاء الطلب',
            'body' => "نأسف لإبلاغك بأنه تم إلغاء طلبك رقم #{$order->id}.",
            'payload' => [
                'order_id' => $order->id,
                'type' => NotificationType::CANCELLED->value,
                'screen' => 'order_details',
            ]
        ];
    }
}
