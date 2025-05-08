<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdviceView extends Model
{
    use HasFactory;

    protected $table = 'employee_advice_view';
    protected $fillable = ['employee_id', 'advice_id', 'viewed_at', 'time_spent', 'has_feedback'];
    public $timestamps = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function advice()
    {
        return $this->belongsTo(Advice::class, 'advice_id');
    }
}
