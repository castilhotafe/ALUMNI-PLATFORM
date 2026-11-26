<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\MobilePostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['page' => ['sometimes', 'integer', 'min:1', 'max:1000000']]);
        $page = (int) ($data['page'] ?? 1);
        $posts = Post::with('user')->where('visibility', 'public')
            ->orderByDesc('created_at')->orderByDesc('id')
            ->simplePaginate(20, ['*'], 'page', $page);

        return response()->json([
            'data' => MobilePostResource::collection($posts->items()),
            'next_page' => $posts->hasMorePages() ? $page + 1 : null,
        ]);
    }

    public function show(int $id): MobilePostResource
    {
        // This is a public feed, even when a private post belongs to this user.
        $post = Post::with('user')->where('visibility', 'public')->findOrFail($id);

        return new MobilePostResource($post);
    }
}
