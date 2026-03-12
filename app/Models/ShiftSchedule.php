<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_id',
        'shift_id',
        'schedule_date',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
