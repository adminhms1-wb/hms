<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvancePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'guest_id',
        'reference_number',
        'amount',
        'payment_method',
        'status',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
