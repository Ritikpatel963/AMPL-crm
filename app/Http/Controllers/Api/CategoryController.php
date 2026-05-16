<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories with subcategories (tree)
     */
    public function index()
    {
        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('status', 1);
            }])
            ->get();

        return response()->json([
            'status' => true,
            'categories' => $categories
        ]);
    }

    /**
     * Get only parent categories
     */
    public function parentCategories()
    {
        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->get();

        return response()->json([
            'status' => true,
            'categories' => $categories
        ]);
    }

    /**
     * Get subcategories by parent id
     */
    public function subCategories($id)
    {
        $categories = Category::where('status', 1)
            ->where('parent_id', $id)
            ->get();

        return response()->json([
            'status' => true,
            'categories' => $categories
        ]);
    }
}