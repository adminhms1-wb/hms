<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomServiceItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_item_id',
        'qty',
        'price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(RoomServiceOrder::class, 'order_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
}
