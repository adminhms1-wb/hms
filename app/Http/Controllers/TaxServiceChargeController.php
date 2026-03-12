<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;

class TaxServiceChargeController extends Controller
{
    public function getSettings()
    {
        $hotel = Hotel::first();
        return response()->json([
            'tax_rate' => $hotel->tax_percentage ?? 0,
            'service_charge' => $hotel->service_charge ?? 0,
        ]);
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'service_charge' => 'nullable|numeric|min:0|max:100',
        ]);

        $hotel = Hotel::first();
        if (!$hotel) {
            $hotel = new Hotel();
            $hotel->name = 'Hotel';
        }

        $hotel->tax_percentage = $validated['tax_rate'] ?? 0;
        $hotel->service_charge = $validated['service_charge'] ?? 0;
        $hotel->save();

        return response()->json(['message' => 'Settings saved successfully']);
    }
}
