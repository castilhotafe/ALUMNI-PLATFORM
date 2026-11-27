<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\FeedNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPublicFeedNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $postId) {}

    public function handle(FeedNotificationService $notifications): void
    {
        // The post might have been deleted or made private while waiting.
        $post = Post::find($this->postId);

        if (! $post || $post->visibility !== 'public') {
            return;
        }

        $notifications->send();
    }
}
