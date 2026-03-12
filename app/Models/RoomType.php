<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'base_price',
        'max_guests',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'max_guests' => 'integer',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
