<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create($request->validated());

        return back()->with('status_post', 'Post created.');
    }
}
