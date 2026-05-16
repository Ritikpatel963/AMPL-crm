<?php

namespace App\Http\Controllers;

use App\Models\VendorCategory;
use Illuminate\Http\Request;

class AdminVendorCategoryController extends Controller
{
    /**
     * Display a listing of vendor categories
     */
    public function index()
    {
        $categories = VendorCategory::with('parent')->paginate(15);
        return view('admin_panel.vendor_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $parentCategories = VendorCategory::mainCategories()->get();
        return view('admin_panel.vendor_categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendor_categories,name',
            'slug' => 'nullable|string|max:255|unique:vendor_categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:vendor_categories,id',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = VendorCategory::generateSlug($validated['name']);
        }

        $validated['status'] = $request->has('status');
        $validated['sort_order'] = $request->input('sort_order', 0);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/vendor_categories'), $imageName);
            $validated['image'] = 'uploads/vendor_categories/' . $imageName;
        }

        VendorCategory::create($validated);

        return redirect()->route('admin_panel.admin.vendor_categories.index')
            ->with('success', 'Vendor category created successfully!');
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit($id)
    {
        $category = VendorCategory::findOrFail($id);
        $parentCategories = VendorCategory::mainCategories()
            ->where('id', '!=', $id)
            ->get();
        return view('admin_panel.vendor_categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category in database
     */
    public function update(Request $request, $id)
    {
        $category = VendorCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendor_categories,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:vendor_categories,slug,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:vendor_categories,id',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        // Generate slug if not provided or if name changed
        if (empty($validated['slug']) || $validated['slug'] === $category->slug) {
            $validated['slug'] = VendorCategory::generateSlug($validated['name']);
        }

        $validated['status'] = $request->has('status');
        $validated['sort_order'] = $request->input('sort_order', $category->sort_order);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }
            
            $imageName = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/vendor_categories'), $imageName);
            $validated['image'] = 'uploads/vendor_categories/' . $imageName;
        }

        $category->update($validated);

        return redirect()->route('admin_panel.admin.vendor_categories.index')
            ->with('success', 'Vendor category updated successfully!');
    }

    /**
     * Delete the specified category
     */
    public function destroy($id)
    {
        $category = VendorCategory::findOrFail($id);

        // Check if category has subcategories
        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with subcategories. Please delete subcategories first.');
        }

        // Check if category has vendor products
        if ($category->vendorProducts()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with vendor products. Please reassign or delete products first.');
        }

        // Delete image
        if ($category->image && file_exists(public_path($category->image))) {
            unlink(public_path($category->image));
        }

        $category->delete();

        return redirect()->route('admin_panel.admin.vendor_categories.index')
            ->with('success', 'Vendor category deleted successfully!');
    }
}
