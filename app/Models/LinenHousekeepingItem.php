<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinenHousekeepingItem extends Model
{
    use SoftDeletes;

    protected $table = 'linen_housekeeping_items';

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

