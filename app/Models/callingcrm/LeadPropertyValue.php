<?php

namespace App\Models\callingcrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadPropertyValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'property_id',
        'value',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function property()
    {
        return $this->belongsTo(ContactProperty::class, 'property_id');
    }
}
