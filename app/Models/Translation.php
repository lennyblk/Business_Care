<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $table = 'translations';
    protected $fillable = ['translation_key', 'language', 'text'];

    protected $unique = ['translation_key', 'language'];
}
