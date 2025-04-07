<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;

    protected $table = 'event_registration';
    protected $fillable = [
        'event_id', 'employee_id', 'registration_date', 'status'
    ];
    public $timestamps = false;

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
