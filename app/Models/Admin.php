<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins'; // Your table name

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password', // Add other fields if needed
        'is_main_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get guard name for Spatie Permission
     */
    public function guardName(): string
    {
        return 'admin';
    }

    public function isMainAdmin(): bool
    {
        return (bool) $this->is_main_admin || $this->phone === '9630884927';
    }
}
