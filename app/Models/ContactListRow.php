<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactListRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_list_id',
        'lead_id',
        'row_number',
        'raw_payload',
        'status',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'row_number' => 'integer',
            'raw_payload' => 'array',
        ];
    }

    public function contactList()
    {
        return $this->belongsTo(ContactList::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeCreated($query)
    {
        return $query->where('status', 'created');
    }

    public function scopeMerged($query)
    {
        return $query->whereIn('status', ['merged', 'merged_reopened']);
    }
}
