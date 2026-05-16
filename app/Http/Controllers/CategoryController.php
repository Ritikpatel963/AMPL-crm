<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display all categories.
     */
    public function index()
    {
        $categories = Category::with('parent')->get();
        return view('admin_panel.product.product_category', compact('categories'));
    }

    /**
     * Store a new category and redirect back.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'nullable|string|unique:categories,slug',
            'icon'      => 'nullable|string|max:255',
            'status'    => 'required|in:Active,Inactive',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        // Auto-generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        Category::create($validated);

        return redirect()->back()->with('success', 'Category added successfully!');
    }

    /**
     * Edit category (for modal or form use).
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    /**
     * Update category and redirect back.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'nullable|string|unique:categories,slug,' . $id,
            'icon'      => 'nullable|string|max:255',
            'status'    => 'required|in:Active,Inactive',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    /**
     * Delete category and redirect back.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}
