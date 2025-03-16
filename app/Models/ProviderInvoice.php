<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderInvoice extends Model
{
    use HasFactory;

    protected $table = 'provider_invoice';
    protected $fillable = [
        'provider_id', 'month', 'year', 'total_amount', 'payment_status', 'issue_date', 'payment_date', 'pdf_path'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
