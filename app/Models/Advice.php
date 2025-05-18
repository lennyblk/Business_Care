<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advice extends Model
{
    use HasFactory;

    protected $table = 'advice';

    protected $fillable = [
        'title',
        'content',
        'category_id',
        'publish_date',
        'expiration_date',
        'is_personalized',
        'min_formule',
        'is_published',
    ];

    public function media()
    {
        return $this->hasMany(AdviceMedia::class, 'advice_id');
    }

    public function category()
    {
        return $this->belongsTo(AdviceCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(AdviceTag::class, 'advice_has_tag', 'advice_id', 'tag_id');
    }

    /**
     * Get the schedules for this advice
     */
    public function schedules()
    {
        return $this->hasMany(AdviceSchedule::class, 'advice_id');
    }
}
