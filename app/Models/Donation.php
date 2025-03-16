<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $table = 'donation';
    protected $fillable = [
        'association_id', 'employee_id', 'donation_type', 'amount_or_description', 'donation_date', 'status'
    ];

    public function association()
    {
        return $this->belongsTo(Association::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
