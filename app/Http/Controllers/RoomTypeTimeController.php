<?php

namespace App\Http\Controllers;

use App\Models\RoomTypeTime;
use Illuminate\Http\Request;

class RoomTypeTimeController extends Controller
{
    /**
     * Get all room type times
     */
    public function index()
    {
        $times = RoomTypeTime::with('roomType')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($time) {
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

                return [
                    'id' => $time->id,
                    'room_type_id' => $time->room_type_id,
                    'room_type' => $time->roomType,
                    'checkin_time' => $formatTime($time->checkin_time),
                    'checkout_time' => $formatTime($time->checkout_time),
                    'early_checkin_allowed' => $time->early_checkin_allowed ?? false,
                    'early_checkin_time' => $formatTime($time->early_checkin_time),
                    'late_checkout_allowed' => $time->late_checkout_allowed ?? false,
                    'late_checkout_time' => $formatTime($time->late_checkout_time),
                    'late_checkout_fee' => $time->late_checkout_fee ?? 0,
                    'created_at' => $time->created_at,
                    'updated_at' => $time->updated_at,
                ];
            });

        return response()->json($times);
    }

    /**
     * Create or update room type time
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:room_type_times,id',
            'room_type_id' => 'required|exists:room_types,id',
            'checkin_time' => 'required|date_format:H:i',
            'checkout_time' => 'required|date_format:H:i',
            'early_checkin_allowed' => 'boolean',
            'early_checkin_time' => 'nullable|date_format:H:i',
            'late_checkout_allowed' => 'boolean',
            'late_checkout_time' => 'nullable|date_format:H:i',
            'late_checkout_fee' => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['id'])) {
            $time = RoomTypeTime::findOrFail($validated['id']);
            $time->update($validated);
        } else {
            // Check if room type already has a time configuration (including soft-deleted)
            $existing = RoomTypeTime::withTrashed()
                ->where('room_type_id', $validated['room_type_id'])
                ->first();
            
            if ($existing) {
                // If soft-deleted, restore it first
                if ($existing->trashed()) {
                    $existing->restore();
                }
                // Update the existing record
                $existing->update($validated);
                $time = $existing;
            } else {
                // Create new record using updateOrCreate to handle race conditions
                // Wrap in try-catch to handle potential duplicate entry errors
                try {
                    $time = RoomTypeTime::updateOrCreate(
                        ['room_type_id' => $validated['room_type_id']],
                        $validated
                    );
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle duplicate entry error (race condition)
                    if ($e->getCode() == 23000) {
                        // Record was created by another request, fetch and update it
                        $time = RoomTypeTime::where('room_type_id', $validated['room_type_id'])->first();
                        if ($time) {
                            $time->update($validated);
                        } else {
                            throw $e;
                        }
                    } else {
                        throw $e;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Room type time saved successfully!',
            'data' => $time->load('roomType')
        ]);
    }

    /**
     * Delete room type time
     */
    public function destroy($id)
    {
        $time = RoomTypeTime::findOrFail($id);
        $time->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room type time deleted successfully!'
        ]);
    }
}
