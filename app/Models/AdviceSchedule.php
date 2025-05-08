<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdviceSchedule extends Model
{
    use HasFactory;

    protected $table = 'advice_schedule';
    protected $fillable = [
        'advice_id', 'scheduled_date', 'is_sent', 'sent_at', 'target_audience', 'target_criteria'
    ];
    public $timestamps = false;

    public function advice()
    {
        return $this->belongsTo(Advice::class, 'advice_id');
    }
}
