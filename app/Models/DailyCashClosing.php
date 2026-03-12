<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyCashClosing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'closing_date',
        'user_id',
        'opening_cash',
        'cash_received',
        'cash_paid',
        'card_received',
        'bank_transfer_received',
        'online_received',
        'expected_cash',
        'actual_cash',
        'difference',
        'status',
        'notes',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'opening_cash' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'cash_paid' => 'decimal:2',
        'card_received' => 'decimal:2',
        'bank_transfer_received' => 'decimal:2',
        'online_received' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
