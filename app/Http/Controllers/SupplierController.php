<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * Return all suppliers formatted for the frontend.
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('id')->get()->map(function (Supplier $supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'company_name' => $supplier->company_name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'mobile' => $supplier->mobile,
                'address' => $supplier->address,
                'city' => $supplier->city,
                'state' => $supplier->state,
                'country' => $supplier->country,
                'postal_code' => $supplier->postal_code,
                'contact_person' => $supplier->contact_person,
                'tax_id' => $supplier->tax_id,
                'notes' => $supplier->notes,
                'is_active' => $supplier->is_active,
            ];
        });

        return response()->json($suppliers);
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supplier = Supplier::create([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'contact_person' => $request->contact_person,
            'tax_id' => $request->tax_id,
            'notes' => $request->notes,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json(['message' => 'Supplier created successfully', 'supplier' => $supplier], 201);
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supplier->update([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'contact_person' => $request->contact_person,
            'tax_id' => $request->tax_id,
            'notes' => $request->notes,
            'is_active' => $request->is_active ?? $supplier->is_active,
        ]);

        return response()->json(['message' => 'Supplier updated successfully', 'supplier' => $supplier]);
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}
