<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 

class VendorProductController extends Controller
{
    public function metrics(Request $request)
    {
        $vendorId = $request->user()->id;

        // configurable expiry window (days)
        $nearExpiryDays = 30;

        $totalProducts = VendorProduct::where('vendor_id', $vendorId)->count();

        $activeProducts = VendorProduct::where('vendor_id', $vendorId)
            ->where('quantity', '>', 0)
            ->count();

        $nearExpiryProducts = VendorProduct::where('vendor_id', $vendorId)
            ->whereNotNull('product_expiry')
            ->whereBetween(
                'product_expiry',
                [now(), Carbon::now()->addDays($nearExpiryDays)]
            )
            ->count();

        $recentProducts = VendorProduct::where('vendor_id', $vendorId)
            ->latest()
            ->take(5)
            ->get([
                'id',
                'product_name',
                'product_rate',
                'quantity',
                'product_expiry',
                'images',
                'brand_name'
            ]);

        return response()->json([
            'status' => true,
            'metrics' => [
                'total_products'       => $totalProducts,
                'active_products'      => $activeProducts,
                'near_expiry_products' => $nearExpiryProducts,
            ],
            'recent_products' => $recentProducts
        ]);
    }

    /**
     * List vendor products
     */
    // public function index(Request $request)
    // {
    //     $products = VendorProduct::where('vendor_id', $request->user()->id)
    //         ->latest()
    //         ->get();
        
    //     return response()->json([
    //         'status' => true,
    //         'products' => $products
    //     ]);
    // }

    public function index(Request $request)
    {
        try {
            $vendorId = $request->user()->id;
            $query = VendorProduct::where('vendor_id', $vendorId);

            // 1. Filter by Near Expiry
            if ($request->query('near_expiry') == 1) {
                $query->whereNotNull('product_expiry')
                      ->whereBetween('product_expiry', [now(), now()->addDays(30)]);
            }

            // 2. Filter by Unit Size & Type
            if ($request->query('unit_size')) {
                $query->where('unit_size', $request->query('unit_size'));
            }
            if ($request->query('unit_type')) {
                $query->where('unit_type', $request->query('unit_type'));
            }

            // --- ad start: Added Price Filter Logic ---
            if ($request->query('min_price')) {
                $query->where('product_rate', '>=', $request->query('min_price'));
            }
            if ($request->query('max_price')) {
                $query->where('product_rate', '<=', $request->query('max_price'));
            }
            // --- ad close ---

            $products = $query->latest()->get();

            // (Unique variants logic for the chips)
            $uniqueVariants = VendorProduct::where('vendor_id', $vendorId)
                ->select('unit_size', 'unit_type')
                ->distinct()
                ->get();

            return response()->json([
                'status' => true,
                'products' => $products,
                'unique_variants' => $uniqueVariants
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }





    /**
     * Get filter options for frontend
     */
    private function getFilterOptions($vendorId)
    {
        return [
            'unit_types' => VendorProduct::where('vendor_id', $vendorId)
                ->whereNotNull('unit_type')
                ->distinct()
                ->pluck('unit_type')
                ->sort()
                ->values()
                ->toArray(),

            'unit_sizes' => VendorProduct::where('vendor_id', $vendorId)
                ->whereNotNull('unit_size')
                ->distinct()
                ->pluck('unit_size')
                ->sort()
                ->values()
                ->toArray(),

            'brands' => VendorProduct::where('vendor_id', $vendorId)
                ->whereNotNull('brand_name')
                ->distinct()
                ->pluck('brand_name')
                ->sort()
                ->values()
                ->toArray(),

            'categories' => VendorProduct::where('vendor_id', $vendorId)
                ->with('vendorCategory')
                ->distinct()
                ->pluck('vendor_category_id')
                ->map(function($categoryId) {
                    $category = VendorCategory::find($categoryId);
                    return $category ? [
                        'id' => $category->id,
                        'name' => $category->name
                    ] : null;
                })
                ->filter()
                ->values()
                ->toArray(),

            'price_range' => [
                'min' => VendorProduct::where('vendor_id', $vendorId)->min('product_rate') ?? 0,
                'max' => VendorProduct::where('vendor_id', $vendorId)->max('product_rate') ?? 0,
            ],

            'quantity_range' => [
                'min' => VendorProduct::where('vendor_id', $vendorId)->min('quantity') ?? 0,
                'max' => VendorProduct::where('vendor_id', $vendorId)->max('quantity') ?? 0,
            ],

            'expiry_status_options' => [
                ['value' => 'near_expiry', 'label' => 'Near Expiry (30 days)'],
                ['value' => 'expiring_soon', 'label' => 'Expiring Soon (7 days)'],
                ['value' => 'expired', 'label' => 'Expired'],
                ['value' => 'not_expired', 'label' => 'Not Expired'],
            ],

            'stock_status_options' => [
                ['value' => 'in_stock', 'label' => 'In Stock'],
                ['value' => 'out_of_stock', 'label' => 'Out of Stock'],
                ['value' => 'low_stock', 'label' => 'Low Stock (≤10)'],
            ],

            'sort_options' => [
                ['value' => 'created_at', 'label' => 'Date Added'],
                ['value' => 'product_name', 'label' => 'Product Name'],
                ['value' => 'product_rate', 'label' => 'Price'],
                ['value' => 'quantity', 'label' => 'Quantity'],
                ['value' => 'product_expiry', 'label' => 'Expiry Date'],
            ],
        ];
    }

    /**
     * Store product
     */
    public function store(Request $request)
{
    Log::info("====== STORE PRODUCT STARTED ======");
    Log::info("RAW Request Headers: ", $request->headers->all());
    Log::info("RAW Request Data: ", $request->all());
    Log::info("Files in request: ", array_keys($request->allFiles()));
    Log::info("User from token: " . ($request->user() ? $request->user()->id : 'NULL - NOT AUTHENTICATED'));

    // ---- STEP 1: VALIDATION ----
    Log::info("STEP 1: Starting validation...");
    try {
        $request->validate([
            'product_name'   => 'required|string|max:255',
            'category_id'    => 'required|integer|exists:vendor_categories,id',
            'brand_name'     => 'nullable|string|max:255',
            'variations'     => 'required|string',
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png'
        ]);
        Log::info("STEP 1: Validation PASSED");
    } catch (\Illuminate\Validation\ValidationException $ve) {
        Log::error("STEP 1: Validation FAILED", $ve->errors());
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $ve->errors()
        ], 422);
    }

    // ---- STEP 2: DB TRANSACTION ----
    Log::info("STEP 2: Starting DB transaction...");
    DB::beginTransaction();

    try {
        $imagePaths = [];

        // ---- STEP 3: IMAGE UPLOAD ----
        Log::info("STEP 3: Checking for images...");
        if ($request->hasFile('images')) {
            $imageCount = count($request->file('images'));
            Log::info("STEP 3: Images found: $imageCount");

            foreach ($request->file('images') as $i => $image) {
                Log::info("STEP 3: Processing image #$i => " . $image->getClientOriginalName() . " | size: " . $image->getSize() . " bytes");
                try {
                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/products'), $imageName);
                    $imagePaths[] = "uploads/products/" . $imageName;
                    Log::info("STEP 3: Image #$i saved as => $imageName");
                } catch (\Exception $imgEx) {
                    Log::error("STEP 3: Image #$i upload FAILED => " . $imgEx->getMessage());
                }
            }
            Log::info("STEP 3: All image paths => ", $imagePaths);
        } else {
            Log::warning("STEP 3: No images found in request. Continuing without images.");
        }

        // ---- STEP 4: DECODE VARIATIONS ----
        Log::info("STEP 4: Decoding variations JSON...");
        Log::info("STEP 4: Raw variations string => " . $request->variations);
        $variations = json_decode($request->variations, true);
        $jsonError  = json_last_error_msg();
        Log::info("STEP 4: JSON decode error check => $jsonError");
        Log::info("STEP 4: Decoded Variations => ", (array) $variations);

        if (empty($variations)) {
            Log::error("STEP 4: Variations is EMPTY or INVALID JSON. Throwing exception.");
            throw new \Exception("The variations JSON is empty or invalid. JSON Error: $jsonError");
        }
        Log::info("STEP 4: Total variations count => " . count($variations));

        // ---- STEP 5: CREATE PRODUCTS ----
        Log::info("STEP 5: Starting product creation loop...");
        $createdProducts = [];

        foreach ($variations as $index => $v) {
            Log::info("STEP 5: Creating variation #$index => ", $v);

            $payload = [
                'vendor_id'           => $request->user()->id,
                'product_name'        => $request->product_name,
                'category_id'         => $request->category_id,
                'vendor_category_id'  => $request->category_id, // Use the same ID for now
                'brand_name'          => $request->brand_name,
                'unit_type'           => $v['unit_type']      ?? 'KG',
                'unit_size'           => $v['unit_size']      ?? '0',
                'product_rate'        => $v['product_rate']   ?? 0,
                'product_expiry'      => $v['product_expiry'] ?? null,
                'quantity'            => $v['quantity']       ?? 0,
                'images'              => $imagePaths,
            ];
            Log::info("STEP 5: DB payload for variation #$index => ", $payload);

            $product = VendorProduct::create($payload);
            Log::info("STEP 5: Variation #$index created with ID => " . $product->id);
            $createdProducts[] = $product;
        }

        // ---- STEP 6: COMMIT ----
        Log::info("STEP 6: Committing DB transaction...");
        DB::commit();
        Log::info("STEP 6: DB commit SUCCESS. Total products created => " . count($createdProducts));
        Log::info("====== STORE PRODUCT COMPLETED SUCCESSFULLY ======");

        return response()->json([
            'status'  => true,
            'message' => count($createdProducts) . ' product variations added successfully'
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("====== STORE PRODUCT FAILED ======");
        Log::error("ERROR Message => " . $e->getMessage());
        Log::error("ERROR File    => " . $e->getFile());
        Log::error("ERROR Line    => " . $e->getLine());
        Log::error("ERROR Trace   => " . $e->getTraceAsString());
        Log::info("====== STORE PRODUCT END (WITH ERROR) ======");

        return response()->json([
            'status'  => false,
            'message' => 'Bulk creation failed',
            'error'   => $e->getMessage()
        ], 500);
    }
}
    /**
     * Show product
     */
    public function show(Request $request, $id)
    {
        $product = VendorProduct::where('vendor_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'product' => $product
        ]);
    }

    /**
     * Update product
     */
        /**
     * Update product
     */
//     public function update(Request $request, $id)
// {
//     Log::info('====== UPDATE PRODUCT STARTED ======');
//     Log::info('STEP 1: Raw input received', [
//         'product_id' => $id,
//         'user_id'    => $request->user()->id,
//         'input'      => $request->except('images'),
//         'has_images' => $request->hasFile('images'),
//     ]);

//     // ---- STEP 1: FIND PRODUCT ----
//     $product = VendorProduct::where('vendor_id', $request->user()->id)
//         ->findOrFail($id);

//     Log::info('STEP 1: Product found', [
//         'product_id'   => $product->id,
//         'product_name' => $product->product_name,
//         'vendor_id'    => $product->vendor_id,
//     ]);

//     // ---- STEP 2: VALIDATION ----
//     Log::info('STEP 2: Starting validation...');
//     try {
//         $request->validate([
//             'product_name'   => 'sometimes|string|max:255',
//             'category_id'    => 'sometimes|integer|exists:vendor_categories,id',
//             'brand_name'     => 'sometimes|string|max:255',
//             'product_rate'   => 'sometimes|numeric',
//             'quantity'       => 'sometimes|integer',
//             'unit_type'      => 'sometimes|string',
//             'unit_size'      => 'sometimes|string',
//             'product_expiry' => 'sometimes|nullable|date',
//             'images.*'       => 'nullable|image|mimes:jpg,jpeg,png,dng'
//         ]);
//         Log::info('STEP 2: Validation PASSED');
//     } catch (\Illuminate\Validation\ValidationException $ve) {
//         Log::error('STEP 2: Validation FAILED', $ve->errors());
//         return response()->json([
//             'status'  => false,
//             'message' => 'Validation failed',
//             'errors'  => $ve->errors()
//         ], 422);
//     }

//     // ---- STEP 3: IMAGE UPLOAD ----
//     Log::info('STEP 3: Checking for images...');
//     if ($request->hasFile('images')) {
//         $imageCount = count($request->file('images'));
//         Log::info("STEP 3: Images found: $imageCount");

//         $newImages = [];
//         foreach ($request->file('images') as $i => $image) {
//             Log::info("STEP 3: Processing image #$i => " . $image->getClientOriginalName() . " | size: " . $image->getSize() . " bytes");
//             try {
//                 $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
//                 $image->move(public_path('uploads/products'), $imageName);
//                 $newImages[] = "uploads/products/" . $imageName;
//                 Log::info("STEP 3: Image #$i saved => uploads/products/$imageName");
//             } catch (\Exception $imgEx) {
//                 Log::error("STEP 3: Image #$i upload FAILED => " . $imgEx->getMessage());
//             }
//         }

//         $product->images = $newImages;
//         Log::info('STEP 3: All new image paths => ', $newImages);
//     } else {
//         Log::info('STEP 3: No images in request — keeping existing images');
//     }

//     // ---- STEP 4: PREPARE UPDATE DATA ----
//     Log::info('STEP 4: Preparing update payload...');
//     $updateData = $request->except(['images', '_method']);

//     if ($request->has('category_id')) {
//         $updateData['vendor_category_id'] = $request->category_id;
//         Log::info('STEP 4: Syncing vendor_category_id => ' . $request->category_id);
//     }

//     Log::info('STEP 4: Final update payload => ', $updateData);

//     // ---- STEP 5: DB UPDATE ----
//     Log::info('STEP 5: Updating product in DB...');
//     try {
//         $product->update($updateData);
//         Log::info('STEP 5: Product updated successfully', ['product_id' => $product->id]);
//     } catch (\Exception $dbEx) {
//         Log::error('STEP 5: DB update FAILED => ' . $dbEx->getMessage());
//         return response()->json([
//             'status'  => false,
//             'message' => 'Failed to update product',
//             'error'   => $dbEx->getMessage()
//         ], 500);
//     }

//     Log::info('====== UPDATE PRODUCT COMPLETED ======');

//     return response()->json([
//         'status'  => true,
//         'message' => 'Product updated successfully',
//         'product' => $product->load('vendorCategory')
//     ]);
// }

public function update(Request $request, $id)
{
    Log::info('====== UPDATE PRODUCT STARTED ======');
    Log::info('STEP 1: Raw input received', [
        'product_id' => $id,
        'user_id'    => $request->user()->id,
        'input'      => $request->except('images'),
        'has_images' => $request->hasFile('images'),
    ]);

    // ---- STEP 1: FIND PRODUCT ----
    $product = VendorProduct::where('vendor_id', $request->user()->id)
        ->findOrFail($id);

    // ---- STEP 2: VALIDATION ----
    Log::info('STEP 2: Starting validation...');
    try {
        $request->validate([
            'product_name'   => 'sometimes|string|max:255',
            'category_id'    => 'sometimes|integer|exists:vendor_categories,id',
            'brand_name'     => 'sometimes|string|max:255',
            'product_rate'   => 'sometimes|numeric',
            'quantity'       => 'sometimes|integer',
            'unit_type'      => 'sometimes|string',
            'unit_size'      => 'sometimes|string',
            'product_expiry' => 'sometimes|nullable|date',
            'variations'     => 'nullable|string', // 🔥 Added variations validation
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png,dng'
        ]);
        Log::info('STEP 2: Validation PASSED');
    } catch (\Illuminate\Validation\ValidationException $ve) {
        Log::error('STEP 2: Validation FAILED', $ve->errors());
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $ve->errors()
        ], 422);
    }

    // ---- STEP 3: IMAGE UPLOAD ----
    if ($request->hasFile('images')) {
        $newImages = [];
        foreach ($request->file('images') as $i => $image) {
            try {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products'), $imageName);
                $newImages[] = "uploads/products/" . $imageName;
            } catch (\Exception $imgEx) {
                Log::error("Image upload FAILED => " . $imgEx.getMessage());
            }
        }
        $product->images = $newImages;
    }

    // ---- STEP 4: PREPARE UPDATE DATA ----
    $updateData = $request->except(['images', '_method', 'variations']); // 🔥 Exclude variations from main update

    if ($request->has('category_id')) {
        $updateData['vendor_category_id'] = $request->category_id;
    }

    // ---- STEP 5: DB UPDATE (CURRENT VARIANT) ----
    try {
        $product->update($updateData);
    } catch (\Exception $dbEx) {
        return response()->json([
            'status'  => false,
            'message' => 'Failed to update product',
            'error'   => $dbEx->getMessage()
        ], 500);
    }

    // ---- STEP 6: BATCH ADD NEW VARIATIONS (Optional) ----
    // 🔥 This logic allows adding new variants to the same product group
    if ($request->has('variations')) {
        Log::info('STEP 6: Processing additional variations...');
        $variations = json_decode($request->variations, true);
        if (json_last_error() === JSON_ERROR_NONE && !empty($variations)) {
            foreach ($variations as $v) {
                VendorProduct::create([
                    'vendor_id'           => $request->user()->id,
                    'product_name'        => $product->product_name,
                    'category_id'         => $product->category_id,
                    'vendor_category_id'  => $product->category_id,
                    'brand_name'          => $product->brand_name,
                    'unit_type'           => $v['unit_type']      ?? 'KG',
                    'unit_size'           => $v['unit_size']      ?? '0',
                    'product_rate'        => $v['product_rate']   ?? 0,
                    'product_expiry'      => $v['product_expiry'] ?? null,
                    'quantity'            => $v['quantity']       ?? 0,
                    'images'              => $product->images, // Link new variants to the same images
                ]);
            }
            Log::info('STEP 6: Additional variations created successfully.');
        }
    }

    Log::info('====== UPDATE PRODUCT COMPLETED ======');

    return response()->json([
        'status'  => true,
        'message' => 'Product updated successfully',
        'product' => $product->load('vendorCategory')
    ]);
}

    /**
     * Delete product
     */
    public function destroy(Request $request, $id)
    {
        $product = VendorProduct::where('vendor_id', $request->user()->id)
            ->findOrFail($id);

        foreach ($product->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
