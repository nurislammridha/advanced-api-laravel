<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Store Comment
    public function store(Request $request, $blogId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'blog_id' => $blogId,
            'user_id' => Auth::user()->id,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Comment added', 'data' => $comment], 201);
    }

    // List comments for a blog
    public function index($blogId)
    {
        $comments = Comment::where('blog_id', $blogId)
            ->with('user:id,name,email')
            ->latest()
            ->paginate(10);

        return response()->json($comments);
    }

    // Delete comment (only owner)
    public function destroy($id)
    {
        $comment = Comment::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }

    //  Get user list who commented on a blog
    public function users($blogId)
    {
        $blog = Blog::with(['comments.user:id,name,email'])->findOrFail($blogId);

        // unique users only
        $users = $blog->comments->pluck('user')->unique('id')->values();

        return response()->json([
            'blog_id' => $blog->id,
            'blog_title' => $blog->title,
            'total_comments' => $blog->comments->count(),
            'unique_commenters' => $users->count(),
            'users' => $users,
        ]);
    }
    // Get user list who commented on a blog (with their comments)
    public function contents($blogId)
    {
        $blog = Blog::with(['comments.user:id,name,email'])->findOrFail($blogId);

        // Map comments with user and comment content
        $comments = $blog->comments->map(function ($comment) {
            return [
                'comment_id' => $comment->id,
                'comment' => $comment->comment, // assuming field name = comment/content
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'email' => $comment->user->email,
                ],
            ];
        });

        // Get unique users only
        $uniqueUsers = $blog->comments
            ->pluck('user')
            ->unique('id')
            ->values();

        return response()->json([
            'blog_id' => $blog->id,
            'blog_title' => $blog->title,
            'total_comments' => $blog->comments->count(),
            'unique_commenters' => $uniqueUsers->count(),
            'users' => $uniqueUsers,
            'comments' => $comments, // added
        ]);
    }
}
