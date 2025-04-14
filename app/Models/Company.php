<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company';
    protected $fillable = [
        'name', 'address', 'code_postal', 'ville', 'pays', 'telephone',
        'creation_date', 'email', 'password',
        'siret', 'formule_abonnement', 'statut_compte', 'date_debut_contrat', 'date_fin_contrat',
        'mode_paiement_prefere', 'employee_count'
    ];
    protected $hidden = ['password'];
    public $timestamps = false;
}
