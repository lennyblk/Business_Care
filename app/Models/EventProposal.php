<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventProposal extends Model
{
    use HasFactory;

    protected $table = 'event_proposal';

    protected $fillable = [
        'company_id',
        'event_type_id',
        'proposed_date',
        'location_id',
        'status',
        'notes'
    ];

    protected $casts = [
        'proposed_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function eventType()
    {
        return $this->belongsTo(ServiceType::class, 'event_type_id');
    }


    public function location()
    {
        return $this->belongsTo(Location::class);
    }


    public function providerAssignments()
    {
        return $this->hasMany(ProviderAssignment::class);
    }


    public function event()
    {
        return $this->hasOne(Event::class);
    }


    public function recommendationLogs()
    {
        return $this->hasMany(ProviderRecommendationLog::class);
    }


    public function isAssigned()
    {
        return $this->status === 'Assigned' || $this->status === 'Accepted';
    }
}
