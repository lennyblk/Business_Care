<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceEvaluation extends Model
{
    use HasFactory;

    protected $table = 'service_evaluation';
    protected $fillable = [
        'intervention_id', 'employee_id', 'rating', 'comment', 'evaluation_date'
    ];

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
