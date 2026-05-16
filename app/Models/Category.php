<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'status',
        'parent_id',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    // Relationship for parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Relationship for subcategories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relationship for vendor products
    public function vendorProducts()
    {
        return $this->hasMany(VendorProduct::class, 'category_id');
    }
}
