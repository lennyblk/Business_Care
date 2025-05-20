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
        'target_criteria',
        'is_disabled'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'sent_at' => 'datetime',
        'is_sent' => 'integer',
        'is_disabled' => 'integer'  // Changé de 'boolean' à 'integer' pour correspondre au tinyint
    ];


    public function advice()
    {
        return $this->belongsTo(Advice::class, 'advice_id');
    }
}
