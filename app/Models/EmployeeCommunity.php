<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCommunity extends Model
{
    use HasFactory;

    protected $table = 'employee_community';
    protected $fillable = ['employee_id', 'community_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }
}
