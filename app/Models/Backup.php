<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'size',
        'status',
        'file_path',
        'backup_date',
        'retention_days',
    ];

    protected $casts = [
        'backup_date' => 'datetime',
        'size' => 'integer',
        'retention_days' => 'integer',
    ];
}
