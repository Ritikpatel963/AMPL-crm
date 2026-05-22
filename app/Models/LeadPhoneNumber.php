<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadPhoneNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'phone',
        'type',
        'is_primary',
        'is_valid',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_valid' => 'boolean',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
