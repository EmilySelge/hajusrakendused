<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, Comment $comment)
    {
        // Allow comment owner OR post owner to delete
        if ($request->user()->id !== $comment->user_id && $request->user()->id !== $comment->post->user_id) {
            abort(403);
        }

        $comment->delete();

        return redirect()->back();
    }
}