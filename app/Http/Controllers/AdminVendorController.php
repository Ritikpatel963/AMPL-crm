<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VendorProduct;
use App\Models\VendorCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminVendorController extends Controller
{

    public function index()
{
    $vendors = User::where('role', 'vendor')
        ->with('vendorDetail')
        ->latest()
        ->get();

    return view('admin_panel.vendors.index', compact('vendors'));
}
    /** View a single vendor's details */
    public function vendorsShow($id)
    {
        $vendor = User::where('role', 'vendor')
            ->with('vendorDetail')
            ->findOrFail($id);

        return view('admin_panel.vendors.show', compact('vendor'));
    }

    /** Show all products for specific vendor with filtering */
    public function vendorProducts(Request $request, $id)
    {
        $vendor = User::where('role', 'vendor')
            ->with('vendorDetail')
            ->findOrFail($id);

        $query = VendorProduct::where('vendor_id', $id)
            ->with('vendorCategory');

        // Apply filters
        $this->applyProductFilters($query, $request);

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get all products (not paginated for DataTable)
        $products = $query->get();

        // Get filter options for this vendor
        $filterOptions = $this->getVendorProductFilterOptions($id);

        return view('admin_panel.vendors.products', compact('vendor', 'products', 'filterOptions'));
    }

    /**
     * Apply filters to the product query
     */
    private function applyProductFilters($query, Request $request)
    {
        // Search by product name
        if ($request->filled('search')) {
            $query->where('product_name', 'LIKE', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by brand
        if ($request->filled('brand_name')) {
            $query->where('brand_name', $request->brand_name);
        }

        // Filter by unit type (kg, ml, ltr, etc.)
        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->unit_type);
        }

        // Filter by unit size
        if ($request->filled('unit_size')) {
            $query->where('unit_size', $request->unit_size);
        }

        // Filter by quantity range
        if ($request->filled('min_quantity')) {
            $query->where('quantity', '>=', $request->min_quantity);
        }
        if ($request->filled('max_quantity')) {
            $query->where('quantity', '<=', $request->max_quantity);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('product_rate', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('product_rate', '<=', $request->max_price);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '=', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)->where('quantity', '<=', 10);
                    break;
            }
        }

        // Filter by expiry status
        if ($request->filled('expiry_status')) {
            switch ($request->expiry_status) {
                case 'near_expiry':
                    $query->whereNotNull('product_expiry')
                          ->whereBetween('product_expiry', [now(), now()->addDays(30)]);
                    break;
                case 'expired':
                    $query->whereNotNull('product_expiry')
                          ->where('product_expiry', '<', now());
                    break;
                case 'expiring_soon':
                    $query->whereNotNull('product_expiry')
                          ->whereBetween('product_expiry', [now(), now()->addDays(7)]);
                    break;
                case 'not_expired':
                    $query->where(function($q) {
                        $q->whereNull('product_expiry')
                          ->orWhere('product_expiry', '>', now());
                    });
                    break;
            }
        }
    }

    /**
     * Get filter options for vendor products
     */
    private function getVendorProductFilterOptions($vendorId)
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

    /** Pending vendors — shown on KYC > Pending page */
    public function pending()
    {
        $vendors = User::where('role', 'vendor')
            ->where('approval_status', 'pending')
            ->with('vendorDetail')
            ->latest()
            ->get();

        return view('admin_panel.kyc.pending', compact('vendors'));
    }

    /** Approved vendors — shown on KYC > Approved page */
    public function approved()
    {
        $vendors = User::where('role', 'vendor')
            ->where('approval_status', 'approved')
            ->with('vendorDetail')
            ->latest()
            ->get();

        return view('admin_panel.kyc.approved', compact('vendors'));
    }

    /** Rejected vendors — shown on KYC > Rejected page */
    public function rejected()
    {
        $vendors = User::where('role', 'vendor')
            ->where('approval_status', 'rejected')
            ->with('vendorDetail')
            ->latest()
            ->get();

        return view('admin_panel.kyc.rejected', compact('vendors'));
    }

    /** View a single vendor's full details + documents */
    public function show($id)
    {
        $vendor = User::where('role', 'vendor')
            ->with('vendorDetail')
            ->findOrFail($id);

        return view('admin_panel.kyc.show', compact('vendor'));
    }

    /** Approve a vendor */
    public function approve($id)
    {
        $user = User::where('role', 'vendor')->findOrFail($id);
        $user->update(['approval_status' => 'approved']);

        return redirect()->back()->with('success', "Vendor {$user->name} approved successfully.");
    }

    /** Reject a vendor */
    public function reject(Request $request, $id)
    {
        $user = User::where('role', 'vendor')->findOrFail($id);
        $user->update(['approval_status' => 'rejected']);

        return redirect()->back()->with('success', "Vendor {$user->name} has been rejected.");
    }
}