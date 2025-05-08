<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalizedAdvice extends Model
{
    use HasFactory;

    protected $table = 'personalized_advice';
    protected $fillable = ['advice_id', 'target_criteria', 'suggested_activities'];
    public $timestamps = false;

    public function advice()
    {
        return $this->belongsTo(Advice::class, 'advice_id');
    }
}
