<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LostAndFound extends Model
{
    use SoftDeletes;

    protected $table = 'lost_and_found';

    protected $fillable = [
        'room_id',
        'item',
        'found_date',
        'status',
        'description',
        'found_by',
        'claimed_by',
        'claimed_date',
        'location',
    ];

    protected $casts = [
        'found_date' => 'date',
        'claimed_date' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function foundBy()
    {
        return $this->belongsTo(User::class, 'found_by');
    }

    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
