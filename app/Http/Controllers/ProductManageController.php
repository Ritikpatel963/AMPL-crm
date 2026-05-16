<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use File;

class ProductManageController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'subcategory'])->latest()->get();
        return view('admin_panel.product.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        $subcategories = Category::whereNotNull('parent_id')->get();
        return view('admin_panel.product.add_product', compact('categories', 'subcategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'sku' => 'required|unique:products',
            'category_id' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/products'), $imageName);
                $imagePaths[] = 'uploads/products/' . $imageName;
            }
        }

        Product::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'brand' => $request->brand,
            'regular_price' => $request->regular_price,
            'sale_price' => $request->sale_price,
            'tax' => $request->tax,
            'stock_quantity' => $request->stock_quantity,
            'stock_status' => $request->stock_status ?? 'in_stock',
            'low_stock_alert' => $request->low_stock_alert,
            'video_url' => $request->video_url,
            'status' => $request->has('status') ? 1 : 0,
            'featured' => $request->has('featured') ? 1 : 0,
            'images' => json_encode($imagePaths),
        ]);

        return redirect()->route('admin_panel.admin.products.index')->with('success', 'Product added successfully!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::whereNull('parent_id')->get();
        $subcategories = Category::whereNotNull('parent_id')->get();

        return view('admin_panel.product.edit_product', compact('product', 'categories', 'subcategories'));
    }

    // ✅ UPDATE PRODUCT
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'sku' => 'required|unique:products,sku,' . $product->id,
            'category_id' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $imagePaths = json_decode($product->images, true) ?? [];

        // ✅ Replace old images if new ones are uploaded
        if ($request->hasFile('images')) {
            if ($imagePaths) {
                foreach ($imagePaths as $oldImage) {
                    if (File::exists(public_path($oldImage))) {
                        File::delete(public_path($oldImage));
                    }
                }
            }

            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/products'), $imageName);
                $imagePaths[] = 'uploads/products/' . $imageName;
            }
        }

        $product->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'brand' => $request->brand,
            'regular_price' => $request->regular_price,
            'sale_price' => $request->sale_price,
            'tax' => $request->tax,
            'stock_quantity' => $request->stock_quantity,
            'stock_status' => $request->stock_status ?? 'in_stock',
            'low_stock_alert' => $request->low_stock_alert,
            'video_url' => $request->video_url,
            'status' => $request->has('status') ? 1 : 0,
            'featured' => $request->has('featured') ? 1 : 0,
            'images' => json_encode($imagePaths),
        ]);

        return redirect()->route('admin_panel.admin.products.index')->with('success', 'Product updated successfully!');
    }

    // ✅ DELETE PRODUCT
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $imagePaths = json_decode($product->images, true);
        if ($imagePaths) {
            foreach ($imagePaths as $image) {
                if (File::exists(public_path($image))) {
                    File::delete(public_path($image));
                }
            }
        }

        $product->delete();

        return redirect()->route('admin_panel.admin.products.index')->with('success', 'Product deleted successfully!');
    }
}
