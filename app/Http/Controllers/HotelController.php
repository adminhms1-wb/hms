<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    /**
     * Get hotel information
     */
    public function getInfo(Request $request)
    {
        $hotel = Hotel::first();
        
        if (!$hotel) {
            return response()->json([
                'name' => '',
                'address' => '',
                'city' => '',
                'country' => '',
                'phone' => '',
                'email' => '',
                'website' => '',
                'logo' => null,
                'favicon' => null,
            ]);
        }

        // Helper function to get image URL
        $getImageUrl = function($path) use ($request) {
            if (!$path) {
                return null;
            }
            
            // Check if file exists
            if (!Storage::disk('public')->exists($path)) {
                return null;
            }
            
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
            
            return $url;
        };

        return response()->json([
            'name' => $hotel->name ?? '',
            'address' => $hotel->address ?? '',
            'city' => $hotel->city ?? '',
            'country' => $hotel->country ?? '',
            'phone' => $hotel->phone ?? '',
            'email' => $hotel->email ?? '',
            'website' => $hotel->website ?? '',
            'logo' => $getImageUrl($hotel->logo),
            'favicon' => $getImageUrl($hotel->favicon),
        ]);
    }

    /**
     * Save hotel information
     */
    public function saveInfo(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:512',
        ]);

        $hotel = Hotel::first();

        if (!$hotel) {
            $hotel = new Hotel();
        }

        $hotel->name = $validated['name'];
        $hotel->address = $validated['address'] ?? '';
        $hotel->city = $validated['city'] ?? '';
        $hotel->country = $validated['country'] ?? '';
        $hotel->phone = $validated['phone'] ?? '';
        $hotel->email = $validated['email'] ?? '';
        $hotel->website = $validated['website'] ?? '';

        // Handle logo upload or removal
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($hotel->logo && Storage::disk('public')->exists($hotel->logo)) {
                Storage::disk('public')->delete($hotel->logo);
            }
            
            $logoPath = $request->file('logo')->store('hotel', 'public');
            $hotel->logo = $logoPath;
        } elseif ($request->has('remove_logo') && $request->remove_logo == '1') {
            // Remove logo if requested
            if ($hotel->logo && Storage::disk('public')->exists($hotel->logo)) {
                Storage::disk('public')->delete($hotel->logo);
            }
            $hotel->logo = null;
        }

        // Handle favicon upload or removal
        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if ($hotel->favicon && Storage::disk('public')->exists($hotel->favicon)) {
                Storage::disk('public')->delete($hotel->favicon);
            }
            
            $faviconPath = $request->file('favicon')->store('hotel', 'public');
            $hotel->favicon = $faviconPath;
        } elseif ($request->has('remove_favicon') && $request->remove_favicon == '1') {
            // Remove favicon if requested
            if ($hotel->favicon && Storage::disk('public')->exists($hotel->favicon)) {
                Storage::disk('public')->delete($hotel->favicon);
            }
            $hotel->favicon = null;
        }

        $hotel->save();

        // Helper function to get image URL
        $getImageUrl = function($path) use ($request) {
            if (!$path) {
                return null;
            }
            
            // Check if file exists
            if (!Storage::disk('public')->exists($path)) {
                return null;
            }
            
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
            
            return $url;
        };

        return response()->json([
            'success' => true,
            'message' => 'Hotel information saved successfully!',
            'hotel' => [
                'name' => $hotel->name,
                'address' => $hotel->address,
                'city' => $hotel->city,
                'country' => $hotel->country,
                'phone' => $hotel->phone,
                'email' => $hotel->email,
                'website' => $hotel->website,
                'logo' => $getImageUrl($hotel->logo),
                'favicon' => $getImageUrl($hotel->favicon),
            ]
        ]);
    }

    /**
     * Get hotel settings
     */
    public function getSettings()
    {
        $hotel = Hotel::first();
        
        if (!$hotel) {
            return response()->json([
                'currency' => 'USD',
                'timezone' => 'UTC',
                'checkin_time' => '14:00',
                'checkout_time' => '11:00',
                'tax_rate' => 0,
                'service_charge' => 0,
            ]);
        }

        // Helper function to format time value safely without preg_match
        $formatTime = function($timeValue) {
            if (!$timeValue) return null;
            
            // If it's a Carbon instance or similar, format it directly
            if (is_object($timeValue) && method_exists($timeValue, 'format')) {
                return $timeValue->format('H:i');
            }
            
            // Convert to string
            $timeStr = (string) $timeValue;
            
            // If empty after conversion, return null
            if (empty($timeStr)) {
                return null;
            }
            
            // Simple string extraction - find HH:MM pattern
            // Look for pattern like "HH:MM" or "HH:MM:SS"
            if (strlen($timeStr) >= 5) {
                // Try to extract first 5 characters (HH:MM)
                $timePart = substr($timeStr, 0, 5);
                // Verify it's in HH:MM format (2 digits, colon, 2 digits)
                if (strlen($timePart) === 5 && 
                    ctype_digit(substr($timePart, 0, 2)) && 
                    $timePart[2] === ':' && 
                    ctype_digit(substr($timePart, 3, 2))) {
                    return $timePart;
                }
            }
            
            return null;
        };

        return response()->json([
            'currency' => $hotel->currency ?? 'USD',
            'timezone' => $hotel->timezone ?? 'UTC',
            'checkin_time' => $formatTime($hotel->check_in_time) ?? '14:00',
            'checkout_time' => $formatTime($hotel->check_out_time) ?? '11:00',
            'tax_rate' => $hotel->tax_percentage ?? 0,
            'service_charge' => $hotel->service_charge ?? 0,
            'online_booking' => $hotel->online_booking ?? false,
            'email_notifications' => $hotel->email_notifications ?? true,
            'auto_checkin' => $hotel->auto_checkin ?? false,
            'maintenance_mode' => $hotel->maintenance_mode ?? false,
            'early_checkin_allowed' => $hotel->early_checkin_allowed ?? false,
            'early_checkin_time' => $formatTime($hotel->early_checkin_time),
            'late_checkout_allowed' => $hotel->late_checkout_allowed ?? false,
            'late_checkout_time' => $formatTime($hotel->late_checkout_time),
            'late_checkout_fee' => $hotel->late_checkout_fee ?? 0,
        ]);
    }

    /**
     * Save hotel settings
     */
    public function saveSettings(Request $request)
    {
        $rules = [
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'service_charge' => 'nullable|numeric|min:0|max:100',
            'online_booking' => 'nullable|boolean',
            'email_notifications' => 'nullable|boolean',
            'auto_checkin' => 'nullable|boolean',
            'maintenance_mode' => 'nullable|boolean',
            'early_checkin_allowed' => 'nullable|boolean',
            'late_checkout_allowed' => 'nullable|boolean',
            'late_checkout_fee' => 'nullable|numeric|min:0',
        ];

        // Only validate time format if value is provided (optional fields)
        if ($request->has('checkin_time') && $request->checkin_time !== null && trim($request->checkin_time) !== '') {
            $rules['checkin_time'] = 'string|date_format:H:i';
        } else {
            $rules['checkin_time'] = 'nullable';
        }

        if ($request->has('checkout_time') && $request->checkout_time !== null && trim($request->checkout_time) !== '') {
            $rules['checkout_time'] = 'string|date_format:H:i';
        } else {
            $rules['checkout_time'] = 'nullable';
        }

        // Only validate early_checkin_time if early_checkin_allowed is true and value is provided
        if ($request->has('early_checkin_allowed') && $request->early_checkin_allowed && 
            $request->has('early_checkin_time') && $request->early_checkin_time !== null && trim($request->early_checkin_time) !== '') {
            $rules['early_checkin_time'] = 'string|date_format:H:i';
        } else {
            $rules['early_checkin_time'] = 'nullable';
        }

        // Only validate late_checkout_time if late_checkout_allowed is true and value is provided
        if ($request->has('late_checkout_allowed') && $request->late_checkout_allowed && 
            $request->has('late_checkout_time') && $request->late_checkout_time !== null && trim($request->late_checkout_time) !== '') {
            $rules['late_checkout_time'] = 'string|date_format:H:i';
        } else {
            $rules['late_checkout_time'] = 'nullable';
        }

        $validated = $request->validate($rules, [
            'checkin_time.date_format' => 'The check-in time must be in the format HH:MM (e.g., 14:00).',
            'checkout_time.date_format' => 'The check-out time must be in the format HH:MM (e.g., 11:00).',
            'early_checkin_time.date_format' => 'The early check-in time must be in the format HH:MM (e.g., 13:00).',
            'late_checkout_time.date_format' => 'The late check-out time must be in the format HH:MM (e.g., 14:00).',
        ]);

        $hotel = Hotel::first();

        if (!$hotel) {
            $hotel = new Hotel();
            $hotel->name = 'Hotel'; // Default name
            $hotel->address = ''; // Default address
        }

        // Update currency if provided
        if (isset($validated['currency'])) {
            $hotel->currency = $validated['currency'];
        } elseif (!$hotel->currency) {
            $hotel->currency = 'USD';
        }

        // Update timezone if provided
        if (isset($validated['timezone'])) {
            $hotel->timezone = $validated['timezone'];
        } elseif (!$hotel->timezone) {
            $hotel->timezone = 'UTC';
        }

        // Update check-in time if provided (optional)
        if (isset($validated['checkin_time']) && $validated['checkin_time'] !== null && trim($validated['checkin_time']) !== '') {
            $hotel->check_in_time = $validated['checkin_time'] . ':00';
        } elseif (!$hotel->check_in_time) {
            // Only set default if hotel doesn't have a check-in time yet
            $hotel->check_in_time = '14:00:00';
        }
        // If checkin_time is empty/null and hotel already has a value, keep existing value

        // Update check-out time if provided (optional)
        if (isset($validated['checkout_time']) && $validated['checkout_time'] !== null && trim($validated['checkout_time']) !== '') {
            $hotel->check_out_time = $validated['checkout_time'] . ':00';
        } elseif (!$hotel->check_out_time) {
            // Only set default if hotel doesn't have a check-out time yet
            $hotel->check_out_time = '11:00:00';
        }
        // If checkout_time is empty/null and hotel already has a value, keep existing value

        // Update tax rate if provided
        if (isset($validated['tax_rate'])) {
            $hotel->tax_percentage = $validated['tax_rate'];
        } elseif ($hotel->tax_percentage === null) {
            $hotel->tax_percentage = 0;
        }

        // Update service charge if provided
        if (isset($validated['service_charge'])) {
            $hotel->service_charge = $validated['service_charge'];
        } elseif ($hotel->service_charge === null) {
            $hotel->service_charge = 0;
        }

        // Update additional configuration settings
        if (isset($validated['online_booking'])) {
            $hotel->online_booking = (bool) $validated['online_booking'];
        } elseif ($hotel->online_booking === null) {
            $hotel->online_booking = false;
        }

        if (isset($validated['email_notifications'])) {
            $hotel->email_notifications = (bool) $validated['email_notifications'];
        } elseif ($hotel->email_notifications === null) {
            $hotel->email_notifications = true;
        }

        if (isset($validated['auto_checkin'])) {
            $hotel->auto_checkin = (bool) $validated['auto_checkin'];
        } elseif ($hotel->auto_checkin === null) {
            $hotel->auto_checkin = false;
        }

        if (isset($validated['maintenance_mode'])) {
            $hotel->maintenance_mode = (bool) $validated['maintenance_mode'];
        } elseif ($hotel->maintenance_mode === null) {
            $hotel->maintenance_mode = false;
        }

        // Update early check-in settings
        if (isset($validated['early_checkin_allowed'])) {
            $hotel->early_checkin_allowed = (bool) $validated['early_checkin_allowed'];
        } elseif ($hotel->early_checkin_allowed === null) {
            $hotel->early_checkin_allowed = false;
        }

        if (isset($validated['early_checkin_time']) && $validated['early_checkin_time'] !== null && trim($validated['early_checkin_time']) !== '') {
            $hotel->early_checkin_time = $validated['early_checkin_time'] . ':00';
        } elseif (isset($validated['early_checkin_allowed']) && !$validated['early_checkin_allowed']) {
            // Clear early check-in time if early check-in is disabled
            $hotel->early_checkin_time = null;
        }

        // Update late check-out settings
        if (isset($validated['late_checkout_allowed'])) {
            $hotel->late_checkout_allowed = (bool) $validated['late_checkout_allowed'];
        } elseif ($hotel->late_checkout_allowed === null) {
            $hotel->late_checkout_allowed = false;
        }

        if (isset($validated['late_checkout_time']) && $validated['late_checkout_time'] !== null && trim($validated['late_checkout_time']) !== '') {
            $hotel->late_checkout_time = $validated['late_checkout_time'] . ':00';
        } elseif (isset($validated['late_checkout_allowed']) && !$validated['late_checkout_allowed']) {
            // Clear late check-out time if late check-out is disabled
            $hotel->late_checkout_time = null;
        }

        if (isset($validated['late_checkout_fee'])) {
            $hotel->late_checkout_fee = $validated['late_checkout_fee'];
        } elseif ($hotel->late_checkout_fee === null) {
            $hotel->late_checkout_fee = 0;
        }

        $hotel->save();
        
        // Update application timezone
        if ($hotel->timezone) {
            config(['app.timezone' => $hotel->timezone]);
            date_default_timezone_set($hotel->timezone);
        }

        // Helper function to format time value safely without preg_match
        $formatTime = function($timeValue) {
            if (!$timeValue) return null;
            
            // If it's a Carbon instance or similar, format it directly
            if (is_object($timeValue) && method_exists($timeValue, 'format')) {
                return $timeValue->format('H:i');
            }
            
            // Convert to string
            $timeStr = (string) $timeValue;
            
            // If empty after conversion, return null
            if (empty($timeStr)) {
                return null;
            }
            
            // Simple string extraction - find HH:MM pattern
            // Look for pattern like "HH:MM" or "HH:MM:SS"
            if (strlen($timeStr) >= 5) {
                // Try to extract first 5 characters (HH:MM)
                $timePart = substr($timeStr, 0, 5);
                // Verify it's in HH:MM format (2 digits, colon, 2 digits)
                if (strlen($timePart) === 5 && 
                    ctype_digit(substr($timePart, 0, 2)) && 
                    $timePart[2] === ':' && 
                    ctype_digit(substr($timePart, 3, 2))) {
                    return $timePart;
                }
            }
            
            return null;
        };

        return response()->json([
            'success' => true,
            'message' => 'Hotel settings saved successfully!',
            'settings' => [
                'currency' => $hotel->currency,
                'timezone' => $hotel->timezone,
                'checkin_time' => $formatTime($hotel->check_in_time),
                'checkout_time' => $formatTime($hotel->check_out_time),
                'tax_rate' => $hotel->tax_percentage,
                'service_charge' => $hotel->service_charge,
                'online_booking' => $hotel->online_booking,
                'email_notifications' => $hotel->email_notifications,
                'auto_checkin' => $hotel->auto_checkin,
                'maintenance_mode' => $hotel->maintenance_mode,
            ]
        ]);
    }
}
