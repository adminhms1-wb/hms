<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_id',
        'reviewed_by',
        'review_date',
        'category',
        'title',
        'description',
        'rating',
        'type',
        'action_items',
    ];

    protected $casts = [
        'review_date' => 'date',
        'rating' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
