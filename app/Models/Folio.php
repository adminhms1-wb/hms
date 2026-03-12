<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folio extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'guest_id',
        'total',
        'subtotal',
        'tax_amount',
        'service_charge',
        'discount',
        'paid',
        'balance',
        'status',
        'folio_date',
        'notes',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount' => 'decimal:2',
        'paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'folio_date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function items()
    {
        return $this->hasMany(FolioItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
