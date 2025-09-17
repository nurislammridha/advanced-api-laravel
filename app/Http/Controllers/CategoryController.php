<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // List all categories
    public function index()
    {
        return response()->json(Category::all(), 200);
    }

    // Store new category
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:categories,name']);
        $category = Category::create($request->only('name'));

        return response()->json(['message' => 'Category created', 'data' => $category], 201);
    }

    // Show single category
    public function show(Category $category)
    {
        return response()->json($category, 200);
    }

    // Update category
    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|unique:categories,name,' . $category->id]);
        $category->update($request->only('name'));

        return response()->json(['message' => 'Category updated', 'data' => $category], 200);
    }

    // Delete category
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted'], 200);
    }
}
