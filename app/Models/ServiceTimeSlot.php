<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTimeSlot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_available',
        'max_bookings',
        'available_days',
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time' => 'string',
        'duration_minutes' => 'integer',
        'is_available' => 'boolean',
        'max_bookings' => 'integer',
        'available_days' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }
}
