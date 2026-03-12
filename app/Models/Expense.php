<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'category',
        'supplier_id',
        'amount',
        'date',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
