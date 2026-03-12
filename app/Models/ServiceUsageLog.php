<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceUsageLog extends Model
{
    protected $fillable = [
        'service_id',
        'service_booking_id',
        'guest_id',
        'room_id',
        'reservation_id',
        'usage_date',
        'usage_time',
        'amount',
        'charge_type',
        'payment_status',
        'processed_by',
        'notes',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'usage_time' => 'string',
        'amount' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceBooking()
    {
        return $this->belongsTo(ServiceBooking::class);
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

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
