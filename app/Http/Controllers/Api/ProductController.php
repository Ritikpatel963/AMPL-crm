<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'all');
        $search = trim((string) $request->query('search', ''));

        $products = Product::select('id', 'name', 'regular_price', 'sale_price', 'images', 'featured', 'is_offer')
            ->where('status', 1)
            ->when($type === 'trending', fn ($query) => $query->where('featured', 1))
            ->when($type === 'offer', fn ($query) => $query->where(function ($offer) {
                $offer->where('is_offer', 1)
                    ->orWhere(function ($priceOffer) {
                        $priceOffer->whereNotNull('sale_price')
                            ->whereColumn('sale_price', '<', 'regular_price');
                    });
            }))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('id', 'DESC')
            ->paginate(min(max($request->integer('per_page', 12), 1), 100));

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
        $product = Product::where('status', 1)->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $images = json_decode($product->images, true);
        $product->image = $images[0] ?? null;
        $product->image_list = $images ?: [];
        $product->attributes = $product->attributes_json ?? [];
        $product->variations = $product->variations_json ?? [];
        unset($product->images);
        unset($product->attributes_json, $product->variations_json);

        return response()->json([
            'status' => true,
            'product' => $product
        ]);
    }
}
