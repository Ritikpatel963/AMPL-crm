<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetryRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'retry_reason_id',
        'logic_type',
        'max_retries',
        'interval_value',
        'interval_unit',
        'mark_lost_after_exhausted',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_retries' => 'integer',
            'interval_value' => 'integer',
            'mark_lost_after_exhausted' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function retryReason()
    {
        return $this->belongsTo(RetryReason::class);
    }
}
