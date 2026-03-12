<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceBooking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'guest_id',
        'room_id',
        'reservation_id',
        'time_slot_id',
        'assigned_staff_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'charge_type',
        'payment_status',
        'payment_method',
        'amount',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(ServiceTimeSlot::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }
}
