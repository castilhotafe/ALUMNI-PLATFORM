<?php

namespace App\Http\Controllers;

use App\Events\ContributionTriggered;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create($request->validated());
        ContributionTriggered::dispatch($request->user(), 'post_created', $post);

        return back()->with('status_post', 'Post created.');
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());
        ContributionTriggered::dispatch($request->user(), 'post_updated', $post);

        return back()->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        ContributionTriggered::dispatch($post->user, 'post_deleted', $post);
        $post->delete();

        return back()->with('status', 'Post deleted.');
    }
}
