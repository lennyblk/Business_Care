<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $table = 'quote';


    protected $fillable = [
        'company_id',
        'company_name',
        'company_size',
        'contract_duration',
        'formule_abonnement',
        'price_per_employee',
        'activities_count',
        'medical_appointments',
        'extra_appointment_fee',
        'chatbot_questions',
        'weekly_advice',
        'personalized_advice',
        'annual_amount',
        'total_amount',
        'total_amount_ttc',
        'reference_number',
        'creation_date',
        'expiration_date',
        'status',
        'services_details',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function isPending()
    {
        return $this->status === 'Pending';
    }

    public function isAccepted()
    {
        return $this->status === 'Accepted';
    }

    public function isRejected()
    {
        return $this->status === 'Rejected';
    }
}