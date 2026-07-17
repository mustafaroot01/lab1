<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * إنشاء حدث عند تأسيس وتأكيد طلب جديد من قبل المريض
     */
    public function __construct(public Order $order)
    {
    }

    /**
     * قنوات البث الفوري للمشرفين لتحديث العدادات والاشعارات في اللوحة
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.orders'),
        ];
    }
}
