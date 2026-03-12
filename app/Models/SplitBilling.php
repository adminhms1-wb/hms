<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SplitBilling extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folio_id',
        'split_type',
        'number_of_splits',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'number_of_splits' => 'integer',
    ];

    public function folio()
    {
        return $this->belongsTo(Folio::class);
    }

    public function items()
    {
        return $this->hasMany(SplitBillingItem::class);
    }
}
