<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'role_id',
        'employee_id',
        'phone',
        'address',
        'hire_date',
        'birth_date',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
