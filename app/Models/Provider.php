<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'provider';

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array
     */
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

    /**
     * Les attributs qui doivent être cachés pour la sérialisation.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Les attributs qui doivent être convertis en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'rating' => 'decimal:2',
        'tarif_horaire' => 'decimal:2',
        'date_validation' => 'date',
        'statut_prestataire' => 'string',
    ];

    /**
     * Indique si le modèle doit utiliser les timestamps.
     *
     * @var bool
     */
    public $timestamps = false; // Si votre table n'a pas de colonnes `created_at` et `updated_at`
}
