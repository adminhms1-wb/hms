<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RefundHandlingController extends Controller
{
    public function index()
    {
        $refunds = Refund::with(['payment', 'reservation', 'folio'])->get();
        return response()->json(['refunds' => $refunds]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'nullable|exists:payments,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'folio_id' => 'nullable|exists:folios,id',
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_method' => 'required|in:original_method,cash,card,bank_transfer',
            'status' => 'nullable|in:pending,approved,processed,completed,cancelled',
            'reason' => 'required|string',
            'refund_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['reference_number'] = 'REF-' . strtoupper(Str::random(8));

        $refund = Refund::create($validated);
        $refund->load(['payment', 'reservation', 'folio']);

        return response()->json(['message' => 'Refund processed successfully', 'refund' => $refund], 201);
    }

    public function update(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        $validated = $request->validate([
            'payment_id' => 'nullable|exists:payments,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'folio_id' => 'nullable|exists:folios,id',
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_method' => 'required|in:original_method,cash,card,bank_transfer',
            'status' => 'nullable|in:pending,approved,processed,completed,cancelled',
            'reason' => 'required|string',
            'refund_date' => 'required|date',
            'processed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'processed' && !$refund->processed_date) {
            $validated['processed_date'] = now()->toDateString();
        }

        $refund->update($validated);
        $refund->load(['payment', 'reservation', 'folio']);

        return response()->json(['message' => 'Refund updated successfully', 'refund' => $refund]);
    }

    public function destroy($id)
    {
        $refund = Refund::findOrFail($id);
        $refund->delete();
        return response()->json(['message' => 'Refund deleted successfully']);
    }
}
