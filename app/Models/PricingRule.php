<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_type_id',
        'rule_type',
        'name',
        'day_of_week',
        'start_date',
        'end_date',
        'price_multiplier',
        'fixed_price',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_multiplier' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
