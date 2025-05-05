<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderAssignment extends Model
{
    use HasFactory;

    protected $table = 'provider_assignment';

    public $timestamps = false;

    protected $fillable = [
        'event_proposal_id',
        'provider_id',
        'status',
        'payment_amount'
    ];

    protected $casts = [
        'proposed_at' => 'datetime',
        'response_at' => 'datetime',
        'payment_amount' => 'decimal:2'
    ];


    public function eventProposal()
    {
        return $this->belongsTo(EventProposal::class);
    }


    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }


    public function availability()
    {
        return $this->hasOne(ProviderAvailability::class);
    }


    public function accept()
    {
        $this->status = 'Accepted';
        $this->response_at = now();
        $this->save();

        // Update the proposal status
        $proposal = $this->eventProposal;
        $proposal->status = 'Accepted';
        $proposal->save();

        return $this->createEvent();
    }

    public function reject()
    {
        $this->status = 'Rejected';
        $this->response_at = now();
        $this->save();

        return true;
    }

    protected function createEvent()
    {
        $proposal = $this->eventProposal;
        $serviceType = $proposal->eventType;

        return Event::create([
            'name' => $serviceType->title,
            'description' => $serviceType->description,
            'date' => $proposal->proposed_date,
            'event_type' => 'Workshop',
            'capacity' => 30, // Capacité par défaut, peut être ajustée
            'location' => $proposal->location->name,
            'company_id' => $proposal->company_id,
            'event_proposal_id' => $proposal->id
        ]);
    }
}
