<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FolioItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folio_id',
        'description',
        'amount',
        'module',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function folio()
    {
        return $this->belongsTo(Folio::class);
    }
}
