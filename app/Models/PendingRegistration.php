<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    use HasFactory;

    protected $table = 'pending_registrations';

    protected $fillable = [
        'user_type',
        'company_name',
        'first_name',
        'last_name',
        'email',
        'password',
        'telephone',
        'position',
        'departement',
        'address',
        'code_postal',
        'ville',
        'siret',
        'description',
        'domains',
        'tarif_horaire',
        'additional_data',
        'status',
        'created_at',
    ];

    protected $casts = [
        'additional_data' => 'array'
    ];
}
