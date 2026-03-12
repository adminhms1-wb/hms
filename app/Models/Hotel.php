<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'logo',
        'favicon',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'tax_percentage',
        'service_charge',
        'check_in_time',
        'check_out_time',
        'currency',
        'timezone',
        'online_booking',
        'email_notifications',
        'auto_checkin',
        'maintenance_mode',
        'early_checkin_allowed',
        'early_checkin_time',
        'late_checkout_allowed',
        'late_checkout_time',
        'late_checkout_fee',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'check_in_time' => 'string',
        'check_out_time' => 'string',
        'online_booking' => 'boolean',
        'email_notifications' => 'boolean',
        'auto_checkin' => 'boolean',
        'maintenance_mode' => 'boolean',
        'early_checkin_allowed' => 'boolean',
        'early_checkin_time' => 'string',
        'late_checkout_allowed' => 'boolean',
        'late_checkout_time' => 'string',
        'late_checkout_fee' => 'decimal:2',
    ];
}
