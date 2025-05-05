<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderAvailability extends Model
{
    use HasFactory;

    protected $table = 'provider_availability';
    protected $fillable = [
        'provider_id', 'date_available', 'start_time', 'end_time', 'status'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function providerAssignment()
    {
        return $this->belongsTo(ProviderAssignment::class);
    }
}
