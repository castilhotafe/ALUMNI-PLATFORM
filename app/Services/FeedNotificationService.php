<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FeedNotificationService
{
    public function send(): void
    {
        if (! config('services.firebase.enabled')) {
            return;
        }

        $projectId = config('services.firebase.project_id');
        $credentialsPath = config('services.firebase.credentials');

        if (! $projectId || ! $credentialsPath || ! is_file($credentialsPath)) {
            throw new RuntimeException('Firebase configuration is incomplete.');
        }

        // Google signs the request using the server's private credentials.
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $credentialsPath
        );

        $token = $credentials->fetchAuthToken();

        if (empty($token['access_token'])) {
            throw new RuntimeException('Could not obtain a Firebase access token.');
        }

        // Send only a generic announcement, not the post's private data.
        Http::withToken($token['access_token'])
            ->acceptJson()
            ->connectTimeout(10)
            ->timeout(20)
            ->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                [
                    'message' => [
                        'topic' => 'public_feed',
                        'notification' => [
                            'title' => 'Alumni feed',
                            'body' => 'New posts are available in the Alumni feed.',
                        ],
                        'data' => [
                            'type' => 'public_feed',
                        ],
                    ],
                ]
            )
            ->throw();
    }
}
