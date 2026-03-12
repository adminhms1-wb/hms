<?php

namespace App\Http\Controllers;

use App\Models\AdvancePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdvancePaymentsController extends Controller
{
    public function index()
    {
        $payments = AdvancePayment::with(['reservation', 'guest'])->get();
        return response()->json(['payments' => $payments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'required|exists:guests,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,online,cheque',
            'status' => 'nullable|in:pending,confirmed,applied,refunded',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['reference_number'] = 'ADV-' . strtoupper(Str::random(8));

        $payment = AdvancePayment::create($validated);
        $payment->load(['reservation', 'guest']);

        return response()->json(['message' => 'Advance payment created successfully', 'payment' => $payment], 201);
    }

    public function update(Request $request, $id)
    {
        $payment = AdvancePayment::findOrFail($id);

        $validated = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'required|exists:guests,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,online,cheque',
            'status' => 'nullable|in:pending,confirmed,applied,refunded',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);
        $payment->load(['reservation', 'guest']);

        return response()->json(['message' => 'Advance payment updated successfully', 'payment' => $payment]);
    }

    public function destroy($id)
    {
        $payment = AdvancePayment::findOrFail($id);
        $payment->delete();
        return response()->json(['message' => 'Advance payment deleted successfully']);
    }
}
