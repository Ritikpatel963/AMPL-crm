<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCustomerAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'customer_id',
    ];

    /**
     * Get the agent assigned to this record.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the customer assigned to this record.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}

