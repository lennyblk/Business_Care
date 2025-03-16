<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notification';
    protected $fillable = [
        'recipient_id', 'recipient_type', 'title', 'message', 'creation_date', 'send_date', 'status', 'notification_type'
    ];
}
