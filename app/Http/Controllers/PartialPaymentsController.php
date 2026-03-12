<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Folio;
use Illuminate\Http\Request;

class PartialPaymentsController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['folio.guest', 'folio.reservation'])->where('is_partial', true)->get();
        return response()->json(['payments' => $payments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'folio_id' => 'required|exists:folios,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,bank_transfer,online',
            'status' => 'nullable|in:pending,paid,failed',
            'notes' => 'nullable|string',
        ]);

        $folio = Folio::findOrFail($validated['folio_id']);
        $newPaid = $folio->paid + $validated['amount'];
        $remainingBalance = max(0, $folio->total - $newPaid);

        $payment = Payment::create([
            ...$validated,
            'reference_type' => 'folio',
            'reference_id' => $validated['folio_id'],
            'is_partial' => true,
            'remaining_balance' => $remainingBalance,
        ]);

        $folio->update([
            'paid' => $newPaid,
            'balance' => $remainingBalance,
        ]);

        return response()->json(['message' => 'Partial payment created successfully', 'payment' => $payment], 201);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'folio_id' => 'required|exists:folios,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,bank_transfer,online',
            'status' => 'nullable|in:pending,paid,failed',
            'notes' => 'nullable|string',
        ]);

        $folio = Folio::findOrFail($validated['folio_id']);
        $oldAmount = $payment->amount;
        $newPaid = $folio->paid - $oldAmount + $validated['amount'];
        $remainingBalance = max(0, $folio->total - $newPaid);

        $payment->update([
            ...$validated,
            'remaining_balance' => $remainingBalance,
        ]);

        $folio->update([
            'paid' => $newPaid,
            'balance' => $remainingBalance,
        ]);

        return response()->json(['message' => 'Partial payment updated successfully', 'payment' => $payment]);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $folio = Folio::find($payment->folio_id);
        
        if ($folio) {
            $folio->update([
                'paid' => max(0, $folio->paid - $payment->amount),
                'balance' => min($folio->total, $folio->balance + $payment->amount),
            ]);
        }

        $payment->delete();
        return response()->json(['message' => 'Partial payment deleted successfully']);
    }
}
