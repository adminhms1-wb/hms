<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_id',
        'task_type',
        'title',
        'description',
        'assigned_date',
        'due_date',
        'priority',
        'status',
        'room_id',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
