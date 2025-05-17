<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdviceSchedule extends Model
{
    use HasFactory;

    protected $table = 'advice_schedule';
    public $timestamps = false;

    protected $fillable = [
        'advice_id',
        'scheduled_date',
        'is_sent',
        'sent_at',
        'target_audience',
        'target_criteria'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'sent_at' => 'datetime',
        'is_sent' => 'boolean',
        'target_criteria' => 'array'
    ];

    public function advice()
    {
        return $this->belongsTo(Advice::class, 'advice_id');
    }
}
