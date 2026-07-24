<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = ['title', 'body', 'image_url', 'target', 'target_label', 'sent_by', 'success_count', 'failure_count'];
}
