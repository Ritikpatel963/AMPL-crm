<?php

namespace App\Models\callingcrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disposition extends Model
{
    use HasFactory;

    public const TYPE_OPTIONS = [
        'in_progress',
        'closed_won',
        'closed_lost',
    ];

    protected $fillable = [
        'pipeline_id',
        'name',
        'type',
        'sort_order',
    ];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }
}
