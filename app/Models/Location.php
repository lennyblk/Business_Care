<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'location';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'address',
        'postal_code',
        'city',
        'country',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];


    public function eventProposals()
    {
        return $this->hasMany(EventProposal::class);
    }


    public function providers()
    {
        return Provider::where('ville', $this->city)->get();
    }
}
