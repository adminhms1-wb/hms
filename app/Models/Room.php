<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'room_number',
        'floor',
        'max_guests',
        'bed_type',
        'smoking',
        'status',
    ];

    protected $casts = [
        'smoking' => 'boolean',
        'max_guests' => 'integer',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
