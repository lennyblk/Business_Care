<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $table = 'service_type';

    public $timestamps = false;

    protected $fillable = [
        'provider_id',
        'title',
        'description',
        'price',
        'duration'
    ];


    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function eventProposals()
    {
        return $this->hasMany(EventProposal::class, 'event_type_id');
    }
}
