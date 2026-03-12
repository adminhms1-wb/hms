<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'is_free',
        'price',
        'description',
        'duration',
        'is_active',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'duration' => 'integer',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(ServiceTimeSlot::class);
    }

    public function usageLogs()
    {
        return $this->hasMany(ServiceUsageLog::class);
    }
}
