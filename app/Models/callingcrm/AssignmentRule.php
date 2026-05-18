<?php

namespace App\Models\callingcrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'condition_field',
        'condition_operator',
        'condition_value',
        'sort_order',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
