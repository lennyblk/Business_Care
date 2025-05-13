<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceEvaluation extends Model
{
    protected $table = 'service_evaluation';
    
    public $timestamps = false;
    
    protected $fillable = [
        'event_id',
        'employee_id',
        'rating',
        'comment',
        'evaluation_date'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'evaluation_date' => 'datetime'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
