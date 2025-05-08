<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdviceFeedback extends Model
{
    protected $table = 'advice_feedback';
    
    protected $fillable = [
        'employee_id',
        'advice_id', 
        'rating',
        'comment',
        'is_helpful',
        'created_at'
    ];

    public $timestamps = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function advice()
    {
        return $this->belongsTo(Advice::class);
    }
}
