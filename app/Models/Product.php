<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'description', 'category_id', 'subcategory_id', 'brand',
        'regular_price', 'sale_price', 'tax', 'stock_quantity', 'stock_status',
        'low_stock_alert', 'video_url', 'images', 'status', 'featured', 'is_offer',
    ];

    protected $casts = [
        'status' => 'boolean',
        'featured' => 'boolean',
        'is_offer' => 'boolean',
    ];

    public function category()
{
    return $this->belongsTo(\App\Models\Category::class, 'category_id');
}

    public function subcategory()
{
    return $this->belongsTo(\App\Models\Category::class, 'subcategory_id');
}
 public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}

