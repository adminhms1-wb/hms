<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomServiceOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'guest_id',
        'reservation_id',
        'assigned_staff_id',
        'room_number',
        'guest_name',
        'total_amount',
        'subtotal',
        'tax',
        'status',
        'ordered_at',
        'order_time',
        'kitchen_time',
        'delivery_time',
        'delivered_time',
        'charges_posted',
        'charges_posted_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'ordered_at' => 'datetime',
        'order_time' => 'datetime',
        'kitchen_time' => 'datetime',
        'delivery_time' => 'datetime',
        'delivered_time' => 'datetime',
        'charges_posted' => 'boolean',
        'charges_posted_at' => 'datetime',
    ];
    
    // Accessor to provide order_time as alias for ordered_at
    public function getOrderTimeAttribute()
    {
        return $this->attributes['order_time'] ?? $this->attributes['ordered_at'] ?? null;
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function items()
    {
        return $this->hasMany(RoomServiceItem::class, 'order_id');
    }
}
