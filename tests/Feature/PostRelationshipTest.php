<?php

use App\Models\Post;
use App\Models\User;

it('a user can have many posts', function () {
    $user = User::factory()->create();

    $post = Post::create([
        'user_id' => $user->id,
        'title' => 'Test post',
        'body' => 'Test post body',
        'visibility' => 'public',
    ]);

    expect($user->posts->first()->id)->toBe($post->id);
    expect($post->user->id)->toBe($user->id);
});

/*
|--------------------------------------------------------------------------
| Local Query Scope Tests (visibleTo)
|--------------------------------------------------------------------------
*/

it('filters and displays only public posts for an unauthenticated guest visitor', function () {
    // Arrange: Create one public and one private post
    Post::factory()->create([
        'title' => 'Public Post',
        'visibility' => 'public',
    ]);

    Post::factory()->create([
        'title' => 'Private Post',
        'visibility' => 'private',
    ]);

    // Act: Invoke the visibleTo scope passing null for the user context
    $visiblePosts = Post::visibleTo(null)->get();

    // Assert: The collection should only yield the public record
    expect($visiblePosts)->toHaveCount(1)
        ->and($visiblePosts->first()->visibility)->toBe('public')
        ->and($visiblePosts->first()->title)->toBe('Public Post');
});

it('allows an authenticated user to see public posts and their own private posts', function () {
    // Arrange: Setup two users
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // 1. Create a public post (visible to all users)
    $publicPost = Post::factory()->create([
        'title' => 'Universal Public Post',
        'visibility' => 'public',
    ]);

    // 2. Create a private post owned by User A
    $privatePostA = Post::factory()->create([
        'user_id' => $userA->id,
        'title' => 'Secret Personal Diary User A',
        'visibility' => 'private',
    ]);

    // 3. Create a private post owned by User B
    $privatePostB = Post::factory()->create([
        'user_id' => $userB->id,
        'title' => 'Secret Personal Diary User B',
        'visibility' => 'private',
    ]);

    // Act: Query posts using the scope bound to User A
    $visibleToUserA = Post::visibleTo($userA)->get();

    // Assert: User A should see the public post and their own private post, but NOT User B's private post
    expect($visibleToUserA)->toHaveCount(2)
        ->and($visibleToUserA->pluck('id'))->toContain($publicPost->id, $privatePostA->id)
        ->and($visibleToUserA->pluck('id'))->not->toContain($privatePostB->id);
});
