<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    // Désactiver les timestamps
    public $timestamps = false;

    protected $table = 'quote';

    protected $fillable = [
        'company_id',
        'creation_date',
        'expiration_date',
        'company_size',
        'formule_abonnement',
        'activities_count',
        'medical_appointments',
        'extra_appointment_fee',
        'chatbot_questions',
        'weekly_advice',
        'personalized_advice',
        'price_per_employee',
        'total_amount',
        'status',
        'services_details'
    ];

    protected $casts = [
        'creation_date' => 'date',
        'expiration_date' => 'date',
        'weekly_advice' => 'boolean',
        'personalized_advice' => 'boolean',
        'extra_appointment_fee' => 'decimal:2',
        'price_per_employee' => 'decimal:2',
        'total_amount' => 'decimal:2'
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