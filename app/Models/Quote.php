<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $table = 'quote';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array
     */
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

    /**
     * Relation avec la société
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relation avec les factures
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Vérifie si le devis est en attente
     */
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    /**
     * Vérifie si le devis est accepté
     */
    public function isAccepted()
    {
        return $this->status === 'Accepted';
    }

    /**
     * Vérifie si le devis est rejeté
     */
    public function isRejected()
    {
        return $this->status === 'Rejected';
    }
}