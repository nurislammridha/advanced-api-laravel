<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    // Toggle Like/Unlike
    public function toggleLike($blogId)
    {
        $userId = Auth::user()->id;

        $like = Like::where('blog_id', $blogId)->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            return response()->json(['message' => 'Unliked']);
        } else {
            Like::create([
                'blog_id' => $blogId,
                'user_id' => $userId,
            ]);
            return response()->json(['message' => 'Liked']);
        }
    }

    //Get user list who liked a blog
    public function users($blogId)
    {
        $blog = Blog::with(['likes.user:id,name,email'])->findOrFail($blogId);

        $users = $blog->likes->pluck('user');

        return response()->json([
            'blog_id' => $blog->id,
            'blog_title' => $blog->title,
            'total_likes' => $blog->likes->count(),
            'users' => $users
        ]);
    }
}
