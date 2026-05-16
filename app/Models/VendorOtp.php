<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorOtp extends Model
{
    protected $fillable = [
        'phone_number',   // ← this was missing!
        'otp',
        'expires_at',
        'is_verified',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function isValid(): bool
    {
        return !$this->is_verified && $this->expires_at->isFuture();
    }
}