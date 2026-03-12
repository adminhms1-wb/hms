<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecureAuthenticationController extends Controller
{
    public function getSettings()
    {
        $settings = [
            'password_min_length_enabled' => false,
            'password_min_length' => 8,
            'password_require_uppercase' => false,
            'password_require_lowercase' => false,
            'password_require_numbers' => false,
            'password_require_symbols' => false,
            'session_timeout' => 60,
            'remember_me_enabled' => true,
            'remember_me_duration' => 30,
            'two_factor_enabled' => false,
            'two_factor_method' => 'email',
            'login_attempts_enabled' => false,
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'ip_whitelist_enabled' => false,
        ];

        // Load from settings table
        $stored = DB::table('settings')->where('group', 'authentication')->get();
        foreach ($stored as $setting) {
            $key = str_replace('auth_', '', $setting->key);
            if ($setting->type === 'boolean') {
                $settings[$key] = (bool) $setting->value;
            } elseif ($setting->type === 'integer') {
                $settings[$key] = (int) $setting->value;
            } else {
                $settings[$key] = $setting->value;
            }
        }

        return response()->json(['settings' => $settings]);
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'password_min_length_enabled' => 'boolean',
            'password_min_length' => 'integer|min:6|max:32',
            'password_require_uppercase' => 'boolean',
            'password_require_lowercase' => 'boolean',
            'password_require_numbers' => 'boolean',
            'password_require_symbols' => 'boolean',
            'session_timeout' => 'integer|min:5|max:1440',
            'remember_me_enabled' => 'boolean',
            'remember_me_duration' => 'integer|min:1|max:365',
            'two_factor_enabled' => 'boolean',
            'two_factor_method' => 'in:email,sms,app',
            'login_attempts_enabled' => 'boolean',
            'max_login_attempts' => 'integer|min:3|max:10',
            'lockout_duration' => 'integer|min:5|max:1440',
            'ip_whitelist_enabled' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'auth_' . $key, 'group' => 'authentication'],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'),
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['message' => 'Settings saved successfully']);
    }
}
