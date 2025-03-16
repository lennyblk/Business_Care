<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $table = 'quote';
    protected $fillable = [
        'company_id', 'creation_date', 'expiration_date', 'total_amount', 'status', 'services_details'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
