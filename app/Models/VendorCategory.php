<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCategory extends Model
{
    use HasFactory;

    protected $table = 'vendor_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'parent_id',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Get parent category
     */
    public function parent()
    {
        return $this->belongsTo(VendorCategory::class, 'parent_id');
    }

    /**
     * Get subcategories
     */
    public function children()
    {
        return $this->hasMany(VendorCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get vendor products in this category
     */
    public function vendorProducts()
    {
        return $this->hasMany(VendorProduct::class, 'vendor_category_id');
    }

    /**
     * Scope to get only main categories
     */
    public function scopeMainCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Generate slug from name
     */
    public static function generateSlug($name)
    {
        $slug = strtolower($name);
        $slug = str_replace(' ', '-', $slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
