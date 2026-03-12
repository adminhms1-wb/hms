<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;

    protected $table = 'attendance';

    protected $fillable = [
        'staff_id',
        'shift_schedule_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'check_in_type',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function shiftSchedule()
    {
        return $this->belongsTo(ShiftSchedule::class);
    }
}
