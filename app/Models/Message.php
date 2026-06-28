<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'message_type',
        'file_path',
        'file_name',
        'file_size',
        'is_read',
    ];
}
