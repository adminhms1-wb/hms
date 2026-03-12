<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryTransactionController extends Controller
{
    /**
     * Return all inventory transactions formatted for the frontend.
     */
    public function index()
    {
        $transactions = InventoryTransaction::with('item')
            ->orderBy('id')
            ->get()
            ->map(function (InventoryTransaction $tx) {
                return [
                    'id' => $tx->id,
                    'itemId' => $tx->item_id,
                    'itemName' => $tx->item?->name,
                    'itemCategory' => $tx->item?->category,
                    'type' => $tx->type,
                    'qty' => $tx->qty,
                    'reference' => $tx->reference,
                    'date' => optional($tx->created_at)->toDateString(),
                ];
            });

        return response()->json($transactions);
    }

    /**
     * Store a newly created inventory transaction.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:inventory_items,id',
            'type' => 'required|in:stock_in,stock_out',
            'qty' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = InventoryTransaction::create([
            'item_id' => $request->item_id,
            'type' => $request->type,
            'qty' => $request->qty,
            'reference' => $request->reference,
        ]);

        return response()->json(['message' => 'Transaction created successfully', 'transaction' => $transaction], 201);
    }

    /**
     * Update the specified inventory transaction.
     */
    public function update(Request $request, $id)
    {
        $transaction = InventoryTransaction::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:inventory_items,id',
            'type' => 'required|in:stock_in,stock_out',
            'qty' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction->update([
            'item_id' => $request->item_id,
            'type' => $request->type,
            'qty' => $request->qty,
            'reference' => $request->reference,
        ]);

        return response()->json(['message' => 'Transaction updated successfully', 'transaction' => $transaction]);
    }

    /**
     * Remove the specified inventory transaction.
     */
    public function destroy($id)
    {
        $transaction = InventoryTransaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}

