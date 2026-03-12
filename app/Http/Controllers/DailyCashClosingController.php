<?php

namespace App\Http\Controllers;

use App\Models\DailyCashClosing;
use Illuminate\Http\Request;

class DailyCashClosingController extends Controller
{
    public function index()
    {
        $closings = DailyCashClosing::with('user')->get();
        return response()->json(['closings' => $closings]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'closing_date' => 'required|date',
            'opening_cash' => 'required|numeric|min:0',
            'cash_received' => 'nullable|numeric|min:0',
            'cash_paid' => 'nullable|numeric|min:0',
            'card_received' => 'nullable|numeric|min:0',
            'bank_transfer_received' => 'nullable|numeric|min:0',
            'online_received' => 'nullable|numeric|min:0',
            'actual_cash' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:open,closed,verified',
            'notes' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Get user_id from request, session, auth, or default to first user
        $userId = $validated['user_id'] ?? $request->session()->get('user_id') ?? auth()->id();
        
        // If still null, get the first user as fallback
        if (!$userId) {
            $firstUser = \App\Models\User::first();
            $userId = $firstUser ? $firstUser->id : 1;
        }
        
        // Remove user_id from validated if it was provided (we'll set it manually)
        unset($validated['user_id']);
        $validated['user_id'] = $userId;
        $validated['expected_cash'] = ($validated['opening_cash'] ?? 0) + 
                                      ($validated['cash_received'] ?? 0) - 
                                      ($validated['cash_paid'] ?? 0);
        $validated['difference'] = ($validated['actual_cash'] ?? 0) - $validated['expected_cash'];

        $closing = DailyCashClosing::create($validated);
        $closing->load('user');

        return response()->json(['message' => 'Cash closing created successfully', 'closing' => $closing], 201);
    }

    public function update(Request $request, $id)
    {
        $closing = DailyCashClosing::findOrFail($id);

        $validated = $request->validate([
            'closing_date' => 'required|date',
            'opening_cash' => 'required|numeric|min:0',
            'cash_received' => 'nullable|numeric|min:0',
            'cash_paid' => 'nullable|numeric|min:0',
            'card_received' => 'nullable|numeric|min:0',
            'bank_transfer_received' => 'nullable|numeric|min:0',
            'online_received' => 'nullable|numeric|min:0',
            'actual_cash' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:open,closed,verified',
            'notes' => 'nullable|string',
        ]);

        $validated['expected_cash'] = ($validated['opening_cash'] ?? 0) + 
                                      ($validated['cash_received'] ?? 0) - 
                                      ($validated['cash_paid'] ?? 0);
        $validated['difference'] = ($validated['actual_cash'] ?? 0) - $validated['expected_cash'];

        $closing->update($validated);
        $closing->load('user');

        return response()->json(['message' => 'Cash closing updated successfully', 'closing' => $closing]);
    }

    public function destroy($id)
    {
        $closing = DailyCashClosing::findOrFail($id);
        $closing->delete();
        return response()->json(['message' => 'Cash closing deleted successfully']);
    }
}
