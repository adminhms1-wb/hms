<?php

namespace App\Http\Controllers;

use App\Models\LinenHousekeepingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LinenHousekeepingItemController extends Controller
{
    /**
     * Return all linen housekeeping items formatted for the frontend.
     */
    public function index()
    {
        $items = LinenHousekeepingItem::orderBy('id')->get()->map(function (LinenHousekeepingItem $item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'category' => $item->category,
                'category_id' => $item->category_id,
                'stock' => $item->stock,
                'minStock' => $item->min_stock,
                'unit' => $item->unit,
                'description' => $item->description,
            ];
        });

        return response()->json($items);
    }

    /**
     * Store a newly created linen housekeeping item.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:linen_housekeeping_items,code',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string|max:255',
            'stock' => 'required|integer|min:0',
            'minStock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = LinenHousekeepingItem::create([
            'code' => $request->code,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'category' => $request->category,
            'stock' => $request->stock,
            'min_stock' => $request->minStock,
            'unit' => $request->unit,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Item created successfully', 'item' => $item], 201);
    }

    /**
     * Update the specified linen housekeeping item.
     */
    public function update(Request $request, $id)
    {
        $item = LinenHousekeepingItem::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:linen_housekeeping_items,code,' . $id,
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string|max:255',
            'stock' => 'required|integer|min:0',
            'minStock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update([
            'code' => $request->code,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'category' => $request->category,
            'stock' => $request->stock,
            'min_stock' => $request->minStock,
            'unit' => $request->unit,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Item updated successfully', 'item' => $item]);
    }

    /**
     * Remove the specified linen housekeeping item.
     */
    public function destroy($id)
    {
        $item = LinenHousekeepingItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
