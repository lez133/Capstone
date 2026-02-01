<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'sender_id',
        'sender_name',
        'recipient',
        'subject',
        'message',
        'type',
        'status',
        'created_at',
    ];
}
