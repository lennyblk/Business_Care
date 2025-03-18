<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'event';
    protected $fillable = [
        'name', 'description', 'date', 'event_type', 'provider_id', 'capacity', 'location', 'registrations'
    ];
    public $timestamps = false;

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
