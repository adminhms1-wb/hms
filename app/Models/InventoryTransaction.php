<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'item_id',
        'type',
        'qty',
        'reference',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}

