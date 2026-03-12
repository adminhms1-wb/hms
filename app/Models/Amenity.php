<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amenity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_paid',
        'price',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_amenities');
    }
}
