<?php

namespace App\Jobs;

use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $notifiable,
        public string $title,
        public string $body,
        public string $type,
        public array $payload = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        NotificationDispatcher::dispatch(
            $this->notifiable,
            $this->title,
            $this->body,
            $this->type,
            $this->payload
        );
    }
}
