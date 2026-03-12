<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'guest_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'total_amount',
        'status',
        'special_requests',
        'booking_type',
        'group_id',
        'refund_amount',
        'cancellation_reason',
        'checked_in_at',
        'checked_out_at',
        'external_booking_id',
        'webhook_url',
        'payment_status',
        'payment_method',
        'online_booking_metadata',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'number_of_guests' => 'integer',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get all guests associated with this reservation (including primary and additional guests)
     */
    public function guests()
    {
        return $this->belongsToMany(Guest::class, 'reservation_guests')
                    ->withTimestamps();
    }

    /**
     * Get additional guests (excluding the primary guest)
     */
    public function additionalGuests()
    {
        return $this->belongsToMany(Guest::class, 'reservation_guests')
                    ->wherePivot('guest_id', '!=', $this->guest_id)
                    ->withTimestamps();
    }

    /**
     * Front desk check-in record (security deposit, check-in time, etc.)
     */
    public function checkin()
    {
        return $this->hasOne(Checkin::class);
    }
}
