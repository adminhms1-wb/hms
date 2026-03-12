<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="base-url" content="{{ url('/') }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @php
            try {
                $hotel = \App\Models\Hotel::first();
                $faviconUrl = $hotel && $hotel->favicon ? asset('storage/' . $hotel->favicon) : null;
            } catch (\Throwable $e) {
                $hotel = null;
                $faviconUrl = null;
            }
        @endphp
        @if($faviconUrl)
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}" id="favicon-link">
        @endif
        @php
            try {
                $inventoryStoreItems = \App\Models\InventoryStoreItem::query()
                    ->orderBy('id')
                    ->get()
                    ->map(function ($item) {
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
                    })
                    ->values();

                $linenHousekeepingItems = \App\Models\LinenHousekeepingItem::query()
                    ->orderBy('id')
                    ->get()
                    ->map(function ($item) {
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
                    })
                    ->values();

                $amenitiesConsumablesItems = \App\Models\AmenitiesConsumableItem::query()
                    ->orderBy('id')
                    ->get()
                    ->map(function ($item) {
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
                    })
                    ->values();

                $stockInventoryItems = \App\Models\InventoryItem::query()
                    ->orderBy('name')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'category' => $item->category,
                            'quantity' => $item->quantity,
                            'expiryDate' => $item->expiry_date,
                            'threshold' => $item->threshold,
                        ];
                    })
                    ->values();

                $stockTransactions = \App\Models\InventoryTransaction::with('item')
                    ->orderBy('id')
                    ->get()
                    ->map(function ($tx) {
                        return [
                            'id' => $tx->id,
                            'itemId' => $tx->item_id,
                            'itemName' => optional($tx->item)->name,
                            'itemCategory' => optional($tx->item)->category,
                            'type' => $tx->type,
                            'qty' => $tx->qty,
                            'reference' => $tx->reference,
                            'date' => optional($tx->created_at)->toDateString(),
                        ];
                    })
                    ->values();

                $suppliers = \App\Models\Supplier::query()
                    ->orderBy('id')
                    ->get()
                    ->map(function ($supplier) {
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
                    })
                    ->values();

                $folios = \App\Models\Folio::with(['reservation', 'guest', 'items'])
                    ->orderBy('id')
                    ->get()
                    ->map(function ($folio) {
                        return [
                            'id' => $folio->id,
                            'reservation_id' => $folio->reservation_id,
                            'guest_id' => $folio->guest_id,
                            'folio_date' => optional($folio->folio_date)->toDateString(),
                            'subtotal' => $folio->subtotal,
                            'tax_amount' => $folio->tax_amount,
                            'service_charge' => $folio->service_charge,
                            'discount' => $folio->discount,
                            'total' => $folio->total,
                            'paid' => $folio->paid,
                            'balance' => $folio->balance,
                            'status' => $folio->status,
                            'notes' => $folio->notes,
                            'guest' => $folio->guest ? [
                                'id' => $folio->guest->id,
                                'name' => $folio->guest->name,
                            ] : null,
                            'reservation' => $folio->reservation ? [
                                'id' => $folio->reservation->id,
                                'guest_name' => $folio->reservation->guest_name,
                            ] : null,
                            'items' => $folio->items->map(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'description' => $item->description,
                                    'module' => $item->module,
                                    'amount' => $item->amount,
                                ];
                            })->toArray(),
                        ];
                    })
                    ->values();

                $reservations = \App\Models\Reservation::with(['guest', 'room'])
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($reservation) {
                        return [
                            'id' => $reservation->id,
                            'guest_name' => $reservation->guest_name,
                            'guest_id' => $reservation->guest_id,
                            'room_id' => $reservation->room_id,
                            'room_number' => $reservation->room ? $reservation->room->room_number : null,
                            'check_in_date' => optional($reservation->check_in_date)->toDateString(),
                            'check_out_date' => optional($reservation->check_out_date)->toDateString(),
                            'status' => $reservation->status,
                            'booking_type' => $reservation->booking_type,
                            'total_amount' => $reservation->total_amount,
                            'guest' => $reservation->guest ? [
                                'id' => $reservation->guest->id,
                                'name' => $reservation->guest->name,
                            ] : null,
                        ];
                    })
                    ->values();
            } catch (\Throwable $e) {
                $inventoryStoreItems = collect();
                $linenHousekeepingItems = collect();
                $amenitiesConsumablesItems = collect();
                $stockInventoryItems = collect();
                $stockTransactions = collect();
                $suppliers = collect();
                $folios = collect();
                $reservations = collect();
            }
        @endphp
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body>
    <script>
        window.__HMS_BOOTSTRAP__ = window.__HMS_BOOTSTRAP__ || {};
        window.__HMS_BOOTSTRAP__.inventoryStoreItems = @json($inventoryStoreItems);
        window.__HMS_BOOTSTRAP__.linenHousekeepingItems = @json($linenHousekeepingItems);
        window.__HMS_BOOTSTRAP__.amenitiesConsumablesItems = @json($amenitiesConsumablesItems);
        window.__HMS_BOOTSTRAP__.stockInventoryItems = @json($stockInventoryItems);
        window.__HMS_BOOTSTRAP__.stockTransactions = @json($stockTransactions);
        window.__HMS_BOOTSTRAP__.suppliers = @json($suppliers);
        window.__HMS_BOOTSTRAP__.folios = @json($folios);
        window.__HMS_BOOTSTRAP__.reservations = @json($reservations);
    </script>
    <div id="app"></div>
</body>
</html>

