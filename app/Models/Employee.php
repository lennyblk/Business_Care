<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employee';
    protected $fillable = [
        'company_id', 'first_name', 'last_name', 'email', 'telephone', 'position', 'departement',
        'date_creation_compte', 'password', 'derniere_connexion', 'preferences_langue', 'id_carte_nfc'
    ];
    protected $hidden = ['password'];
    public $timestamps = false;

    /**
     * Relation avec l'entreprise.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
