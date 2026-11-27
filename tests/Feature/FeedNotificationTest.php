<?php

use App\Jobs\SendPublicFeedNotification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Queue;

it('queues a notification only for a new public post', function () {
    Queue::fake();
    config(['services.firebase.enabled' => true]);

    $this->actingAs(User::factory()->create());
    $this->withoutMiddleware(PreventRequestForgery::class);

    foreach (['public', 'private'] as $visibility) {
        $this->post(route('posts.store'), [
            'title' => "A {$visibility} post",
            'body' => 'Notification test.',
            'visibility' => $visibility,
        ])->assertSessionHasNoErrors()->assertRedirect();
    }

    $publicPost = Post::where('visibility', 'public')->firstOrFail();

    Queue::assertPushed(
        SendPublicFeedNotification::class,
        fn ($job) => $job->postId === $publicPost->id
    );

    Queue::assertPushed(SendPublicFeedNotification::class, 1);
});

it('does not queue notifications when Firebase is disabled', function () {
    Queue::fake();
    config(['services.firebase.enabled' => false]);

    $this->actingAs(User::factory()->create());
    $this->withoutMiddleware(PreventRequestForgery::class);

    $this->post(route('posts.store'), [
        'title' => 'Public post without notifications',
        'body' => 'Notification test.',
        'visibility' => 'public',
    ])->assertSessionHasNoErrors()->assertRedirect();

    Queue::assertNotPushed(SendPublicFeedNotification::class);
});
