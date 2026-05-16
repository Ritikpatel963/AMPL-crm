<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorCategory; // Make sure you have a Category model
use Illuminate\Http\Request;

class VendorCategoryController extends Controller
{
    public function index()
    {
        // Fetch all categories from your database
        $categories = VendorCategory::select('id', 'name')->get();

        // Return the exact structure your Android app expects
        return response()->json([
            'status' => true,
            'categories' => $categories
        ], 200);
    }
}
