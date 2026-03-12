<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousekeepingTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'staff_id',
        'task_type',
        'status',
        'date',
        'notes',
        'completed_at',
        'started_at',
    ];

    protected $casts = [
        'date' => 'date',
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
