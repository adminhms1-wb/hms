<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SplitBillingItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'split_billing_id',
        'guest_id',
        'guest_name',
        'amount',
        'percentage',
        'payment_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function splitBilling()
    {
        return $this->belongsTo(SplitBilling::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
