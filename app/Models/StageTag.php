<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StageTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stage_id',
        'name',
        'color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'tag_id');
    }

    public function dispositions()
    {
        return $this->hasMany(Disposition::class, 'tag_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
