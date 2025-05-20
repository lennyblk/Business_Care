<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdviceCategory extends Model
{
    use HasFactory;

    protected $table = 'advice_category';
    protected $fillable = ['name', 'description', 'is_active'];

    public $timestamps = false;

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function advices()
    {
        return $this->hasMany(Advice::class, 'category_id');
    }
}
