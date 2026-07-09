<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class PushNotificationService
{
    /**
     * Sends a push notification to a user's registered device. No-ops
     * silently if the user has no FCM token; never throws — a notification
     * failure must never break the endpoint that triggered it.
     *
     * @param  array<string, string>  $data
     */
    public function sendToUser(User $user, string $title, string $body, array $data): void
    {
        if (! $user->fcm_token) {
            return;
        }

        try {
            $message = CloudMessage::new()
                ->withToken($user->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            Firebase::messaging()->send($message);
        } catch (Throwable $e) {
            Log::warning('Push notification failed to send', [
                'user_id' => $user->id,
                'type' => $data['type'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
