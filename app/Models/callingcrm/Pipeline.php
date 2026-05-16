<?php

namespace App\Models\callingcrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function stages()
    {
        return $this->hasMany(LeadStage::class)->orderBy('sort_order');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function dispositions()
    {
        return $this->hasMany(Disposition::class)->orderBy('sort_order');
    }
}
