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
