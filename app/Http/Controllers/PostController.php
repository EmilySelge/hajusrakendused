<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        return Inertia::render('blog/Index', [

            'posts' => Post::with('user', 'comments')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($post) => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'created_at' => $post->created_at->format('d.m.Y H:i'),
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                    ],
                    'comments_count' => $post->comments->count(),
                ]),
        ]);
    }

    public function show(Post $post)
    {
        return Inertia::render('blog/Show', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'description' => $post->description,
                'created_at' => $post->created_at->format('d.m.Y H:i'),
                'updated_at' => $post->updated_at->format('d.m.Y H:i'),
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                ],
                'comments' => $post->comments()
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(fn($comment) => [
                        'id' => $comment->id,
                        'body' => $comment->body,
                        'created_at' => $comment->created_at->format('d.m.Y H:i'),
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                    ]),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $request->user()->posts()->create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Post $post)
    {
        if ($request->user()->id !== $post->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $post->update($validated);

        return redirect()->back();
    }

    public function destroy(Request $request, Post $post)
    {
        if ($request->user()->id !== $post->user_id) {
            abort(403);
        }

        $post->delete();

        return redirect()->route('blog.index');
    }
}
