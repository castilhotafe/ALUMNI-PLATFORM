<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an authenticated user before each test runs
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->withoutMiddleware(PreventRequestForgery::class); // Disable middleware for testing
});

/*
|--------------------------------------------------------------------------
| Store Method Tests
|--------------------------------------------------------------------------
*/

it('stores a newly created post for the authenticated user', function () {
    $payload = [
        'title' => 'My First Pest Test Post',
        'content' => 'This is the content of the post.', // Kept standard post field names
        'visibility' => 'public',                       // Added required validation field
        'body' => 'This is the content of the post.', // Added 'body' field to match Post model
    ];

    $response = $this->post(route('posts.store'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('status_post', 'Post created.');

    $this->assertDatabaseHas('posts', [
        'user_id' => $this->user->id,
        'title' => 'My First Pest Test Post',
        'visibility' => 'public',
        'body' => 'This is the content of the post.',
    ]);
});

/*
|--------------------------------------------------------------------------
| Update Method Tests
|--------------------------------------------------------------------------
*/

it('updates the specified post with validated data', function () {
    $post = Post::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Old Post Title',
        'body' => 'Old post content string.',
        'visibility' => 'private',
    ]);

    $updatePayload = [
        'title' => 'Completely New Post Title',
        'content' => 'Updated post content string.',
        'visibility' => 'public', // Added required validation field
        'body' => 'Updated post content string.', // Added 'body' field to match Post model
    ];

    $response = $this->patch(route('posts.update', $post), $updatePayload);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Post updated.');

    expect($post->fresh())
        ->title->toBe('Completely New Post Title')
        ->visibility->toBe('public');
});

/*
|--------------------------------------------------------------------------
| Destroy Method Tests
|--------------------------------------------------------------------------
*/

it('deletes the specified post successfully', function () {
    $post = Post::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Post to Delete',
        'body' => 'Content of the post to delete.',
        'visibility' => 'public',
    ]);

    $response = $this->delete(route('posts.destroy', $post));

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Post deleted.');

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});
