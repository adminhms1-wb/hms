<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeasonalPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_type_id',
        'start_date',
        'end_date',
        'price',
        'name',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
