<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::select('id', 'name', 'sale_price', 'images')
            ->orderBy('id', 'DESC')
            ->paginate(4); // page size

        $products->getCollection()->transform(function ($product) {
            $images = json_decode($product->images, true);
            $product->image = $images[0] ?? null;   
            unset($product->images);
            return $product;
        });

        return response()->json([
            'status' => true,
            'products' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'has_more' => $products->hasMorePages(),
        ]);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'product' => $product
        ]);
    }
}
