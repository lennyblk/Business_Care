<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnonymousReport extends Model
{
    use HasFactory;

    protected $table = 'anonymous_report';
    protected $fillable = [
        'encrypted_employee_id', 'report_date', 'description', 'category', 'status', 'severity_level'
    ];
}
