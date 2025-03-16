<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $table = 'intervention';
    protected $fillable = [
        'provider_id', 'service_type_id', 'employee_id', 'intervention_date', 'start_time', 'end_time', 'location', 'status', 'notes'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
