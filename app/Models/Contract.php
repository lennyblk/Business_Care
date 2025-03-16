<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $table = 'contract';
    protected $fillable = [
        'company_id', 'start_date', 'end_date', 'services', 'amount', 'payment_method'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
