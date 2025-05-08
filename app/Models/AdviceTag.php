<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdviceTag extends Model
{
    protected $table = 'advice_tag';
    protected $fillable = ['name'];
    public $timestamps = false;

    public function advices()
    {
        return $this->belongsToMany(Advice::class, 'advice_has_tag', 'tag_id', 'advice_id');
    }
}
