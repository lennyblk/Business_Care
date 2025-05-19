<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';
    public $timestamps = false;
    protected $fillable = [
        'company_id', 'contract_id', 'issue_date', 'due_date', 'total_amount', 'payment_status', 'pdf_path', 'details', 'is_donation'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
