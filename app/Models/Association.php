<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Association extends Model
{
    use HasFactory;

    protected $table = 'association';
    protected $fillable = [
        'name', 'description', 'domain', 'contact_info', 'website', 'status'
    ];
}
