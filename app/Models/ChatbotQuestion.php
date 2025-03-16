<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotQuestion extends Model
{
    use HasFactory;

    protected $table = 'chatbot_question';
    protected $fillable = [
        'employee_id', 'question', 'response', 'question_date', 'status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
