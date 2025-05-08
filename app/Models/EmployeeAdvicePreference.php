<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvicePreference extends Model
{
    use HasFactory;

    protected $table = 'employee_advice_preference';
    protected $fillable = ['employee_id', 'preferred_categories', 'preferred_tags', 'interests', 'created_at', 'updated_at'];
    public $timestamps = true;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
