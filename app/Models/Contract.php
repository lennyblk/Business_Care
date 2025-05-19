<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    public $timestamps = false;
    protected $table = 'contract';

    protected $fillable = [
        'company_id',
        'start_date',
        'end_date',
        'services',
        'amount',
        'payment_method',
        'formule_abonnement',
        'stripe_checkout_id',
        'stripe_subscription_id',
        'payment_status',
        'is_termination_request'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
