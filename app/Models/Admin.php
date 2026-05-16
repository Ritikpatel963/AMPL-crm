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
        'password', // Add other fields if needed
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
}
