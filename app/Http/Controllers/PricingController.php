<?php

namespace App\Http\Controllers;

use App\Models\SeasonalPrice;
use App\Models\PricingRule;
use App\Models\RoomType;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Get all seasonal prices
     */
    public function getSeasonalPrices()
    {
        $prices = SeasonalPrice::with('roomType')
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json($prices);
    }

    /**
     * Create or update seasonal price
     */
    public function saveSeasonalPrice(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:seasonal_prices,id',
            'room_type_id' => 'required|exists:room_types,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price' => 'required|numeric|min:0',
        ]);

        if (isset($validated['id'])) {
            $price = SeasonalPrice::findOrFail($validated['id']);
            $price->update($validated);
        } else {
            $price = SeasonalPrice::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Seasonal price saved successfully!',
            'data' => $price->load('roomType')
        ]);
    }

    /**
     * Delete seasonal price
     */
    public function deleteSeasonalPrice($id)
    {
        $price = SeasonalPrice::findOrFail($id);
        $price->delete();

        return response()->json([
            'success' => true,
            'message' => 'Seasonal price deleted successfully!'
        ]);
    }

    /**
     * Get all pricing rules
     */
    public function getPricingRules()
    {
        $rules = PricingRule::with('roomType')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($rules);
    }

    /**
     * Create or update pricing rule
     */
    public function savePricingRule(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:pricing_rules,id',
            'room_type_id' => 'nullable|exists:room_types,id',
            'rule_type' => 'required|in:weekend,peak,holiday',
            'name' => 'nullable|string|max:255',
            'day_of_week' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'price_multiplier' => 'required|numeric|min:0.01|max:10',
            'fixed_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['id'])) {
            $rule = PricingRule::findOrFail($validated['id']);
            $rule->update($validated);
        } else {
            $rule = PricingRule::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule saved successfully!',
            'data' => $rule->load('roomType')
        ]);
    }

    /**
     * Delete pricing rule
     */
    public function deletePricingRule($id)
    {
        $rule = PricingRule::findOrFail($id);
        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing rule deleted successfully!'
        ]);
    }
}
