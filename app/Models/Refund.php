<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_id',
        'reservation_id',
        'folio_id',
        'reference_number',
        'refund_amount',
        'refund_method',
        'status',
        'reason',
        'refund_date',
        'processed_date',
        'notes',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'refund_date' => 'date',
        'processed_date' => 'date',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function folio()
    {
        return $this->belongsTo(Folio::class);
    }
}
