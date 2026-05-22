<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'uploaded_by',
        'file_name',
        'sheet_name',
        'storage_path',
        'mime_type',
        'file_size',
        'total_rows',
        'created_rows',
        'merged_rows',
        'failed_rows',
        'status',
        'mapping',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'total_rows' => 'integer',
            'created_rows' => 'integer',
            'merged_rows' => 'integer',
            'failed_rows' => 'integer',
            'mapping' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function rows()
    {
        return $this->hasMany(ContactListRow::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getSuccessCountAttribute()
    {
        return ($this->created_rows ?? 0) + ($this->merged_rows ?? 0);
    }

    public function getFailureRateAttribute()
    {
        if (!$this->total_rows) return 0;
        return round(($this->failed_rows / $this->total_rows) * 100, 2);
    }
}
