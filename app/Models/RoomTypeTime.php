<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomTypeTime extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_type_id',
        'checkin_time',
        'checkout_time',
        'early_checkin_allowed',
        'early_checkin_time',
        'late_checkout_allowed',
        'late_checkout_time',
        'late_checkout_fee',
    ];

    protected $casts = [
        'checkin_time' => 'string',
        'checkout_time' => 'string',
        'early_checkin_allowed' => 'boolean',
        'early_checkin_time' => 'string',
        'late_checkout_allowed' => 'boolean',
        'late_checkout_time' => 'string',
        'late_checkout_fee' => 'decimal:2',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
