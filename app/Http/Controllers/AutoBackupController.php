<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class AutoBackupController extends Controller
{
    public function getSettings()
    {
        $settings = [
            'enabled' => false,
            'backup_time' => '02:00',
            'retention_days' => 30,
            'backup_location' => 'local'
        ];

        // Load from settings table if exists
        $stored = DB::table('settings')->where('group', 'auto_backup')->get();
        foreach ($stored as $setting) {
            $key = str_replace('auto_backup_', '', $setting->key);
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
            'enabled' => 'boolean',
            'backup_time' => 'required|string',
            'retention_days' => 'required|integer|min:1|max:365',
            'backup_location' => 'required|in:local,cloud',
        ]);

        foreach ($validated as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'auto_backup_' . $key, 'group' => 'auto_backup'],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'),
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['message' => 'Settings saved successfully']);
    }

    public function index()
    {
        $backups = Backup::where('type', 'auto')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($backup) {
                return $backup != null && $backup->id != null;
            })
            ->values();

        return response()->json(['backups' => $backups]);
    }
}
