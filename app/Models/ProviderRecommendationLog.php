<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderRecommendationLog extends Model
{
    use HasFactory;

    protected $table = 'provider_recommendation_log';

    public $timestamps = false;

    protected $fillable = [
        'event_proposal_id',
        'provider_id',
        'geographic_match',
        'skill_match',
        'rating_score',
        'price_score',
        'availability_score',
        'total_score',
        'recommended'
    ];

    protected $casts = [
        'geographic_match' => 'boolean',
        'skill_match' => 'boolean',
        'rating_score' => 'decimal:2',
        'price_score' => 'decimal:2',
        'availability_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'recommended' => 'boolean',
        'created_at' => 'datetime'
    ];


    public function eventProposal()
    {
        return $this->belongsTo(EventProposal::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
