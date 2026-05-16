<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = Category::with('parent')->paginate(15);
        return view('admin_panel.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $parentCategories = Category::where('parent_id', null)->get();
        return view('admin_panel.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = make_slug($validated['name']);
        }

        $validated['status'] = $request->has('status');

        Category::create($validated);

        return redirect()->route('admin_panel.admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $parentCategories = Category::where('parent_id', null)
            ->where('id', '!=', $id)
            ->get();
        return view('admin_panel.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category in database
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $id,
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        // Generate slug if not provided or if name changed
        if (empty($validated['slug']) || $validated['slug'] === $category->slug) {
            $validated['slug'] = make_slug($validated['name']);
        }

        $validated['status'] = $request->has('status');

        $category->update($validated);

        return redirect()->route('admin_panel.admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Delete the specified category
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Check if category has subcategories
        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with subcategories. Please delete subcategories first.');
        }

        // Check if category has products
        if ($category->vendorProducts()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with products. Please reassign or delete products first.');
        }

        $category->delete();

        return redirect()->route('admin_panel.admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
