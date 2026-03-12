<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'id_number',
        'id_type',
        'address',
        'date_of_birth',
        'nationality',
        'is_vip',
        'allergies',
        'preferences',
        'is_blacklisted',
        'blacklist_reason',
        'flagged_at',
        'flagged_reason',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_vip' => 'boolean',
        'is_blacklisted' => 'boolean',
        'preferences' => 'array',
        'flagged_at' => 'datetime',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get all reservations where this guest is associated (as primary or additional guest)
     */
    public function allReservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_guests')
                    ->withTimestamps();
    }
}
