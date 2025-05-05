<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $table = 'provider';

    protected $fillable = [
        'last_name',
        'first_name',
        'description',
        'rating',
        'domains',
        'email',
        'telephone',
        'password',
        'adresse',
        'code_postal',
        'ville',
        'siret',
        'iban',
        'statut_prestataire',
        'date_validation',
        'validation_documents',
        'tarif_horaire',
        'nombre_evaluations',
    ];


    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'tarif_horaire' => 'decimal:2',
        'date_validation' => 'date',
        'statut_prestataire' => 'string',
    ];

    public $timestamps = false;

    public function assignments()
    {
        return $this->hasMany(ProviderAssignment::class);
    }

    public function recommendationLogs()
    {
        return $this->hasMany(ProviderRecommendationLog::class);
    }

}
