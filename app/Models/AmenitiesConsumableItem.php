<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AmenitiesConsumableItem extends Model
{
    use SoftDeletes;

    protected $table = 'amenities_consumables_items';

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'category',
        'stock',
        'min_stock',
        'unit',
        'description',
    ];
}
