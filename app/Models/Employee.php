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
        'date_creation_compte', 'password', 'derniere_connexion', 'preferences_langue', 'id_carte_nfc', 'advice_notification_enabled'
    ];
    protected $hidden = ['password'];
    public $timestamps = false;


    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'employee_id');
    }

    public function registeredEvents()
    {
        return $this->belongsToMany(Event::class, 'event_registration', 'employee_id', 'event_id');
    }

    public function adviceViews()
    {
        return $this->hasMany(EmployeeAdviceView::class, 'employee_id');
    }

    public function adviceFeedbacks()
    {
        return $this->hasMany(AdviceFeedback::class, 'employee_id');
    }

    public function advicePreferences()
    {
        return $this->hasOne(EmployeeAdvicePreference::class, 'employee_id');
    }

    public function preferences()
    {
        return $this->hasOne(EmployeeAdvicePreference::class, 'employee_id');
    }
}
