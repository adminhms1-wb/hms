<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_type',
        'reference_id',
        'folio_id',
        'amount',
        'method',
        'status',
        'is_partial',
        'is_advance',
        'remaining_balance',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'is_partial' => 'boolean',
        'is_advance' => 'boolean',
    ];

    public function folio()
    {
        return $this->belongsTo(Folio::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
