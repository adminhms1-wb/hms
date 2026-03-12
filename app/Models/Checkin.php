<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkin extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'checkin_time',
        'deposit',
    ];

    protected $casts = [
        'checkin_time' => 'datetime',
        'deposit' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}

