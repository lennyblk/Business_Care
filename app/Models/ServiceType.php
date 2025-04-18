<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $table = 'service_type';
    protected $fillable = [
        'provider_id', 'title', 'description', 'price', 'duration'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
