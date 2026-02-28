<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [5, 15, 60];

    public function __construct(
        public int $userId,
        public string $type,
        public array $data
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        
        if (!$user) {
            Log::warning("User {$this->userId} not found for notification");
            return;
        }

        // Here you can send email, push notification, etc.
        // For now, just log it
        Log::info("Notification sent to {$user->email}", [
            'type' => $this->type,
            'data' => $this->data,
        ]);

        // Example: Send email
        // Mail::to($user->email)->send(new \App\Mail\NotificationMail($this->type, $this->data));
    }
}
