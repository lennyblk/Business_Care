<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'event';
    protected $fillable = [
        'name', 'description', 'date', 'event_type', 'provider_id', 'capacity', 'location', 'registrations','company_id','duration','event_proposal_id'
    ];
    public $timestamps = false;

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }

    public function registeredEmployees()
    {
        return $this->belongsToMany(Employee::class, 'event_registration', 'event_id', 'employee_id');
    }

    public function eventProposal()
    {
        return $this->belongsTo(EventProposal::class);
    }

    public function serviceEvaluations()
    {
        return $this->hasMany(ServiceEvaluation::class);
    }
}
