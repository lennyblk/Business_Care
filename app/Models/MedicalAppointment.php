<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAppointment extends Model
{
    use HasFactory;

    protected $table = 'medical_appointment';
    protected $fillable = [
        'employee_id', 'provider_id', 'appointment_date', 'reason', 'confidential'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
