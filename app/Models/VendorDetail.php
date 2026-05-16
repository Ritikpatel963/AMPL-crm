<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDetail extends Model
{
    use HasFactory;

    protected $table = 'vendor_details';

    protected $fillable = [
        'user_id',
        'firm_name',
        'gst_number',
        'license_type',
        'fertilizer_license_no',
        'seeds_license_no',
        'pesticides_license_no',
        'gst_doc',
        'license_doc',
        'address',
        'near_transport_indore',
        'phone_number',
        'alternate_number',
        'aadhar_front_path',
        'aadhar_back_path',
    ];

    /**
     * Vendor belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
