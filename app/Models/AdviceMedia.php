<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdviceMedia extends Model
{
    use HasFactory;

    protected $table = 'advice_media';

    protected $fillable = [
        'advice_id',
        'media_type',
        'media_url',
        'title',
        'description',
    ];

    public $timestamps = false; // Désactiver les colonnes created_at et updated_at

    public function advice()
    {
        return $this->belongsTo(Advice::class, 'advice_id');
    }
}
