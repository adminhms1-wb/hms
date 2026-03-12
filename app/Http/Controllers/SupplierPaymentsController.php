<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierPaymentsController extends Controller
{
    public function index()
    {
        $payments = SupplierPayment::with('supplier')->get();
        return response()->json(['payments' => $payments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque',
            'status' => 'nullable|in:pending,paid,partial,overdue',
            'payment_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Auto-generate invoice number if not provided
        if (empty($validated['invoice_number'])) {
            $validated['invoice_number'] = 'INV-' . strtoupper(Str::random(8));
        }

        $payment = SupplierPayment::create($validated);
        $payment->load('supplier');

        return response()->json(['message' => 'Supplier payment created successfully', 'payment' => $payment], 201);
    }

    public function update(Request $request, $id)
    {
        $payment = SupplierPayment::findOrFail($id);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque',
            'status' => 'nullable|in:pending,paid,partial,overdue',
            'payment_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);
        $payment->load('supplier');

        return response()->json(['message' => 'Supplier payment updated successfully', 'payment' => $payment]);
    }

    public function destroy($id)
    {
        $payment = SupplierPayment::findOrFail($id);
        $payment->delete();
        return response()->json(['message' => 'Supplier payment deleted successfully']);
    }
}
