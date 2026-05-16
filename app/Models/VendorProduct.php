<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    use HasFactory;

    protected $table = 'vendor_products';

    protected $fillable = [
        'vendor_id',
        'product_name',
        'images',
        'category_id',
        'vendor_category_id',
        'brand_name',
        'unit_type',
        'unit_size',
        'product_rate',
        'product_expiry',
        'quantity',
    ];

    protected $appends = ['product_expiry_formatted'];

    protected $casts = [
        'images'         => 'array',
        'product_expiry' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function vendorCategory()
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function getCategoryNameAttribute()
    {
        // Try vendor_category_id first, then category_id
        if ($this->vendor_category_id) {
            $category = VendorCategory::find($this->vendor_category_id);
            if ($category) return $category->name;
        }
        
        if ($this->category_id) {
            $category = VendorCategory::find($this->category_id);
            if ($category) return $category->name;
        }
        
        return '—';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFirstImageAttribute()
    {
        return $this->images[0] ?? null;
    }

    public function getProductExpiryFormattedAttribute()
    {
        return $this->product_expiry
            ? Carbon::parse($this->product_expiry)->format('d-m-Y')
            : null;
    }

    public function getFormattedQuantityAttribute()
    {
        return $this->quantity ?? '—';
    }
}