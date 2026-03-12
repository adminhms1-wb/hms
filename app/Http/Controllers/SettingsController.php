<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Hotel;

class SettingsController extends Controller
{
    // Logo, Color Theme & Hotel Name
    public function getLogoColorTheme(Request $request)
    {
        $settings = [
            'hotel_name' => '',
            'hotel_tagline' => '',
            'logo_url' => '',
            'favicon_url' => '',
            'primary_color' => '#3498db',
            'secondary_color' => '#2c3e50',
            'accent_color' => '#e74c3c'
        ];

        // Get hotel data from hotels table
        $hotel = Hotel::first();
        if ($hotel) {
            $settings['hotel_name'] = $hotel->name ?? '';
            
            // Helper function to get image URL
            $getImageUrl = function($path) use ($request) {
                // #region agent log
                file_put_contents('f:\xampp\htdocs\Lr\hms\.cursor\debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'SettingsController.php:getImageUrl','message'=>'Function entry','data'=>['path'=>$path],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                
                if (!$path) {
                    // #region agent log
                    file_put_contents('f:\xampp\htdocs\Lr\hms\.cursor\debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'SettingsController.php:getImageUrl','message'=>'Path is empty','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                    // #endregion
                    return '';
                }
                
                // Check if file exists
                $exists = Storage::disk('public')->exists($path);
                // #region agent log
                file_put_contents('f:\xampp\htdocs\Lr\hms\.cursor\debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'SettingsController.php:getImageUrl','message'=>'File exists check','data'=>['path'=>$path,'exists'=>$exists],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                
                if (!$exists) {
                    // #region agent log
                    file_put_contents('f:\xampp\htdocs\Lr\hms\.cursor\debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'SettingsController.php:getImageUrl','message'=>'File does not exist','data'=>['path'=>$path],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                    // #endregion
                    return '';
                }
                
                // Get the base URL from the current request (includes subdirectory if any)
                $scheme = $request->getSchemeAndHttpHost();
                $basePath = $request->getBasePath();
                $baseUrl = $scheme . $basePath;
                
                // #region agent log
                file_put_contents('f:\xampp\htdocs\Lr\hms\.cursor\debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'post-fix','hypothesisId'=>'B','location'=>'SettingsController.php:getImageUrl','message'=>'Base URL components','data'=>['scheme'=>$scheme,'basePath'=>$basePath,'baseUrl'=>$baseUrl],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                
                // Path is already in format: hotel/filename.png
                // Construct URL with app/public: baseUrl/storage/app/public/path
                // This works on production, staging, and local server
                $cleanPath = ltrim($path, '/');
                // Remove /public/ or /public from base path if it exists (handle both with and without trailing slash)
                $basePathClean = rtrim($baseUrl, '/');
                $basePathBeforeReplace = $basePathClean;
                // Remove /public/ (with trailing slash) or /public (at end) from base path
                $basePathClean = preg_replace('#/public(/|$)#', '/', $basePathClean);
                // Ensure no trailing slash before adding /storage/app/public/
                $basePathClean = rtrim($basePathClean, '/');
                $url = $basePathClean . '/storage/app/public/' . $cleanPath;
                
                // #region agent log
                file_put_contents('f:\xampp\htdocs\Lr\hms\.cursor\debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'verify-fix','hypothesisId'=>'A','location'=>'SettingsController.php:getImageUrl','message'=>'Final URL constructed','data'=>['cleanPath'=>$cleanPath,'basePathBeforeReplace'=>$basePathBeforeReplace,'basePathClean'=>$basePathClean,'urlTemplate'=>'/storage/app/public/','finalUrl'=>$url],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                
                return $url;
            };
            
            $settings['logo_url'] = $getImageUrl($hotel->logo);
            $settings['favicon_url'] = $getImageUrl($hotel->favicon);
        }

        // Get color theme settings from settings table
        $stored = DB::table('settings')->where('group', 'logo_color_theme')->get();
        foreach ($stored as $setting) {
            $key = str_replace('logo_theme_', '', $setting->key);
            if ($key === 'hotel_name' || $key === 'logo_url' || $key === 'favicon_url') {
                continue; // Skip these as we get them from hotels table
            }
            if ($setting->type === 'boolean') {
                $settings[$key] = (bool) $setting->value;
            } else {
                $settings[$key] = $setting->value;
            }
        }

        return response()->json(['settings' => $settings]);
    }

    public function saveLogoColorTheme(Request $request)
    {
        $validated = $request->validate([
            'hotel_name' => 'nullable|string|max:255',
            'hotel_tagline' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string',
            'favicon_url' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
        ]);

        // Save hotel name to hotels table
        if (isset($validated['hotel_name'])) {
            $hotel = Hotel::first();
            if (!$hotel) {
                $hotel = new Hotel();
            }
            $hotel->name = $validated['hotel_name'];
            $hotel->save();
        }

        // Save other settings to settings table
        foreach ($validated as $key => $value) {
            if ($key === 'hotel_name' || $key === 'logo_url' || $key === 'favicon_url') {
                continue; // Skip these as they're handled separately
            }
            DB::table('settings')->updateOrInsert(
                ['key' => 'logo_theme_' . $key, 'group' => 'logo_color_theme'],
                [
                    'value' => (string) $value,
                    'type' => 'string',
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['message' => 'Settings saved successfully']);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);
        
        $hotel = Hotel::first();
        if (!$hotel) {
            $hotel = new Hotel();
            $hotel->name = '';
            $hotel->save();
        }
        
        // Delete old logo if exists
        if ($hotel->logo && Storage::disk('public')->exists($hotel->logo)) {
            Storage::disk('public')->delete($hotel->logo);
        }
        
        $path = $request->file('logo')->store('hotel', 'public');
        $hotel->logo = $path;
        $hotel->save();
        
        // Get the base URL from the current request (includes subdirectory if any)
        $baseUrl = $request->getSchemeAndHttpHost() . $request->getBasePath();
        
        // Path is already in format: hotel/filename.png
        // Construct URL with app/public: baseUrl/storage/app/public/path
        // This works on production, staging, and local server
        $cleanPath = ltrim($path, '/');
        // Remove /public/ or /public from base path if it exists (handle both with and without trailing slash)
        $basePath = rtrim($baseUrl, '/');
        // Remove /public/ (with trailing slash) or /public (at end) from base path
        $basePath = preg_replace('#/public(/|$)#', '/', $basePath);
        // Ensure no trailing slash before adding /storage/app/public/
        $basePath = rtrim($basePath, '/');
        $url = $basePath . '/storage/app/public/' . $cleanPath;

        return response()->json(['url' => $url]);
    }

    public function uploadFavicon(Request $request)
    {
        $request->validate(['favicon' => 'required|image|max:512']);
        
        $hotel = Hotel::first();
        if (!$hotel) {
            $hotel = new Hotel();
            $hotel->name = '';
            $hotel->save();
        }
        
        // Delete old favicon if exists
        if ($hotel->favicon && Storage::disk('public')->exists($hotel->favicon)) {
            Storage::disk('public')->delete($hotel->favicon);
        }
        
        $path = $request->file('favicon')->store('hotel', 'public');
        $hotel->favicon = $path;
        $hotel->save();
        
        // Get the base URL from the current request (includes subdirectory if any)
        $baseUrl = $request->getSchemeAndHttpHost() . $request->getBasePath();
        
        // Path is already in format: hotel/filename.png
        // Construct URL with app/public: baseUrl/storage/app/public/path
        // This works on production, staging, and local server
        $cleanPath = ltrim($path, '/');
        // Remove /public/ or /public from base path if it exists (handle both with and without trailing slash)
        $basePath = rtrim($baseUrl, '/');
        // Remove /public/ (with trailing slash) or /public (at end) from base path
        $basePath = preg_replace('#/public(/|$)#', '/', $basePath);
        // Ensure no trailing slash before adding /storage/app/public/
        $basePath = rtrim($basePath, '/');
        $url = $basePath . '/storage/app/public/' . $cleanPath;

        return response()->json(['url' => $url]);
    }

    public function removeLogo()
    {
        $hotel = Hotel::first();
        if ($hotel && $hotel->logo) {
            if (Storage::disk('public')->exists($hotel->logo)) {
                Storage::disk('public')->delete($hotel->logo);
            }
            $hotel->logo = null;
            $hotel->save();
        }
        return response()->json(['message' => 'Logo removed']);
    }

    public function removeFavicon()
    {
        $hotel = Hotel::first();
        if ($hotel && $hotel->favicon) {
            if (Storage::disk('public')->exists($hotel->favicon)) {
                Storage::disk('public')->delete($hotel->favicon);
            }
            $hotel->favicon = null;
            $hotel->save();
        }
        return response()->json(['message' => 'Favicon removed']);
    }

    // Invoice & Receipt Templates
    public function getInvoiceTemplates()
    {
        $template = [
            'id' => 'default',
            'settings' => [
                'header_text' => '',
                'footer_text' => '',
                'show_logo' => true,
                'show_tax_details' => true,
                'show_payment_method' => true
            ]
        ];

        $stored = DB::table('settings')->where('group', 'invoice_templates')->first();
        if ($stored) {
            $template = json_decode($stored->value, true);
        }

        return response()->json(['template' => $template]);
    }

    public function saveInvoiceTemplates(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|string',
            'settings' => 'required|array',
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'invoice_template', 'group' => 'invoice_templates'],
            [
                'value' => json_encode($validated),
                'type' => 'json',
                'updated_at' => now()
            ]
        );

        return response()->json(['message' => 'Template settings saved successfully']);
    }

    // Custom Footer Branding
    public function getFooterBranding()
    {
        $settings = [
            'footer_text' => '',
            'copyright_text' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'website' => '',
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'show_address' => true,
            'show_contact' => true,
            'show_social_media' => true
        ];

        $stored = DB::table('settings')->where('group', 'footer_branding')->get();
        foreach ($stored as $setting) {
            $key = str_replace('footer_', '', $setting->key);
            if ($setting->type === 'boolean') {
                $settings[$key] = (bool) $setting->value;
            } else {
                $settings[$key] = $setting->value;
            }
        }

        return response()->json(['settings' => $settings]);
    }

    public function saveFooterBranding(Request $request)
    {
        $validated = $request->all();

        foreach ($validated as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'footer_' . $key, 'group' => 'footer_branding'],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'type' => is_bool($value) ? 'boolean' : 'string',
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['message' => 'Settings saved successfully']);
    }

    // Module Enable/Disable
    public function getModules()
    {
        $stored = DB::table('settings')->where('key', 'enabled_modules')->where('group', 'modules')->first();
        $enabledModules = $stored ? json_decode($stored->value, true) : [];

        return response()->json(['modules' => $enabledModules]);
    }

    public function saveModules(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $stored = DB::table('settings')->where('key', 'enabled_modules')->where('group', 'modules')->first();
        $enabledModules = $stored ? json_decode($stored->value, true) : [];

        if ($validated['enabled']) {
            if (!in_array($validated['module_id'], $enabledModules)) {
                $enabledModules[] = $validated['module_id'];
            }
        } else {
            $enabledModules = array_values(array_filter($enabledModules, fn($id) => $id !== $validated['module_id']));
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'enabled_modules', 'group' => 'modules'],
            [
                'value' => json_encode($enabledModules),
                'type' => 'json',
                'updated_at' => now()
            ]
        );

        return response()->json(['message' => 'Module updated successfully']);
    }

    // Tax & Currency Setup
    public function getTaxCurrency()
    {
        $settings = [
            'currency' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'before',
            'default_tax_rate' => 0,
            'service_charge_rate' => 0,
            'tax_inclusive' => false,
            'additional_tax_rates' => []
        ];

        $stored = DB::table('settings')->where('group', 'tax_currency')->get();
        foreach ($stored as $setting) {
            $key = str_replace('tax_currency_', '', $setting->key);
            if ($setting->type === 'boolean') {
                $settings[$key] = (bool) $setting->value;
            } elseif ($setting->type === 'integer' || $setting->type === 'float') {
                $settings[$key] = (float) $setting->value;
            } elseif ($setting->type === 'json') {
                $settings[$key] = json_decode($setting->value, true);
            } else {
                $settings[$key] = $setting->value;
            }
        }

        return response()->json(['settings' => $settings]);
    }

    public function saveTaxCurrency(Request $request)
    {
        $validated = $request->validate([
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'nullable|string|max:10',
            'currency_position' => 'required|in:before,after',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'service_charge_rate' => 'nullable|numeric|min:0|max:100',
            'tax_inclusive' => 'boolean',
            'additional_tax_rates' => 'nullable|array',
        ]);

        foreach ($validated as $key => $value) {
            $type = 'string';
            if (is_bool($value)) {
                $type = 'boolean';
                $value = $value ? '1' : '0';
            } elseif (is_numeric($value)) {
                $type = 'float';
            } elseif (is_array($value)) {
                $type = 'json';
                $value = json_encode($value);
            }

            DB::table('settings')->updateOrInsert(
                ['key' => 'tax_currency_' . $key, 'group' => 'tax_currency'],
                [
                    'value' => (string) $value,
                    'type' => $type,
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['message' => 'Settings saved successfully']);
    }

    // Language Settings
    public function getLanguage()
    {
        $settings = [
            'default_language' => 'en',
            'date_format' => 'Y-m-d',
            'time_format' => '24'
        ];

        $stored = DB::table('settings')->where('group', 'language')->get();
        foreach ($stored as $setting) {
            $key = str_replace('language_', '', $setting->key);
            $settings[$key] = $setting->value;
        }

        return response()->json(['settings' => $settings]);
    }

    public function saveLanguage(Request $request)
    {
        $validated = $request->validate([
            'default_language' => 'required|string|max:2',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|in:12,24',
        ]);

        foreach ($validated as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'language_' . $key, 'group' => 'language'],
                [
                    'value' => (string) $value,
                    'type' => 'string',
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['message' => 'Settings saved successfully']);
    }
}
