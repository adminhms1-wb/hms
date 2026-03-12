<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_items';

    protected $fillable = [
        'name',
        'category',
        'quantity',
        'expiry_date',
        'threshold',
    ];

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }
}

