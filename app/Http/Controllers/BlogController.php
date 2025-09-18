<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    // List blogs with user & category (paginated)
    public function index(Request $request)
    {
        $query = Blog::with(['category', 'user']);

        // Search by keyword in title, description, or user name
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                    ->orWhere('description', 'like', "%$keyword%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%$keyword%"));
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date (YYYY-MM-DD)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Paginate results
        $blogs = $query
            ->withCount('comments', 'likes')
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json($blogs, 200);
    }

    // Store new blog
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blogs', 'public');
        }

        $blog = Blog::create([
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'user_id'     => $user->id,
            'image'       => $imagePath,
        ]);

        return response()->json([
            'message' => 'Blog created',
            'data'    => $this->formatBlogResponse($blog),
        ], 201);
    }

    // Show single blog with relations
    public function show(Blog $blog)
    {
        $blog->load(['category', 'user'])
            ->loadCount('comments', 'likes');

        return response()->json($blog, 200);
    }

    // Update blog (only owner can update)
    public function update(Request $request, Blog $blog)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($blog->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required',
            'category_id' => 'sometimes|required|exists:categories,id',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle new image
        if ($request->hasFile('image')) {
            // delete old image if exists
            if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                Storage::disk('public')->delete($blog->image);
            }
            $blog->image = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($request->only('title', 'description', 'category_id'));

        return response()->json([
            'message' => 'Blog updated',
            'data'    => $this->formatBlogResponse($blog),
        ], 200);
    }

    // Delete blog (only owner can delete)
    public function destroy(Blog $blog)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($blog->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // delete image file
        if ($blog->image && Storage::disk('public')->exists($blog->image)) {
            Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();

        return response()->json(['message' => 'Blog deleted'], 200);
    }

    // Search blogs by title, description, or author name
    public function search($keyword)
    {
        $blogs = Blog::with(['category', 'user'])
            ->where('title', 'like', "%$keyword%")
            ->orWhere('description', 'like', "%$keyword%")
            ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%$keyword%"))
            ->paginate(10);

        return response()->json($blogs, 200);
    }

    // Filter blogs by category, user, or date
    public function filter(Request $request)
    {
        $query = Blog::with(['category', 'user']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return response()->json($query->paginate(10), 200);
    }

    // Helper: format response for image url
    private function formatBlogResponse(Blog $blog)
    {
        return [
            'id'          => $blog->id,
            'title'       => $blog->title,
            'description' => $blog->description,
            'category_id' => $blog->category_id,
            'user_id'     => $blog->user_id,
            'image'       => $blog->image ? asset('storage/' . $blog->image) : null,
            'created_at'  => $blog->created_at,
            'updated_at'  => $blog->updated_at,
        ];
    }
}
