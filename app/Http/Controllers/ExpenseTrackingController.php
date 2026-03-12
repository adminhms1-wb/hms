<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseTrackingController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('supplier')->get();
        return response()->json(['expenses' => $expenses]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'category' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,cheque',
            'status' => 'nullable|in:pending,paid,approved',
            'notes' => 'nullable|string',
        ]);

        $expense = Expense::create($validated);
        $expense->load('supplier');

        return response()->json(['message' => 'Expense created successfully', 'expense' => $expense], 201);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'category' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,cheque',
            'status' => 'nullable|in:pending,paid,approved',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);
        $expense->load('supplier');

        return response()->json(['message' => 'Expense updated successfully', 'expense' => $expense]);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
