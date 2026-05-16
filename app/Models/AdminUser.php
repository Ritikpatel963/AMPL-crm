<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class AdminUser extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $table = 'adminusers'; // Your table name

    protected $fillable = [
        'name',
        'role',
        'username',
        'email',
        'password', // Add other fields if needed
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'admin';

    public function isSuperAdmin(): bool
    {
        $roleValue = strtolower((string) ($this->role ?? ''));

        if (in_array($roleValue, ['admin', 'super admin', 'superadmin', 'super_admin'], true)) {
            return true;
        }

        if (!method_exists($this, 'hasRole')) {
            return false;
        }

        return $this->hasRole('admin')
            || $this->hasRole('Super Admin')
            || $this->hasRole('superadmin')
            || $this->hasRole('super_admin');
    }
  
  	/**
     * Admin user has one vendor detail
     */
    public function vendorDetail()
    {
        return $this->hasOne(VendorDetail::class, 'user_id');
    }
}
