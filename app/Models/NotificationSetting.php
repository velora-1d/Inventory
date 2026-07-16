<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'notification_type',
        'channel',
        'recipient_type',
        'recipient_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
