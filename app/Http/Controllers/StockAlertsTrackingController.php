<?php

namespace App\Http\Controllers;

use App\Models\InventoryStoreItem;
use App\Models\InventoryItem;
use App\Models\LinenHousekeepingItem;
use App\Models\AmenitiesConsumableItem;
use App\Models\RoomServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockAlertsTrackingController extends Controller
{
    /**
     * Get all stock alerts, expiry tracking, and usage consumption data.
     */
    public function index()
    {
        // Low Stock Alerts - from all inventory tables
        $lowStockAlerts = [];
        
        // From inventory_store_items
        $inventoryStoreLowStock = InventoryStoreItem::whereColumn('stock', '<=', 'min_stock')
            ->orWhere('stock', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'category' => $item->category,
                    'current_stock' => $item->stock,
                    'min_stock' => $item->min_stock,
                    'unit' => $item->unit,
                    'type' => 'Inventory Store',
                    'status' => $item->stock == 0 ? 'out_of_stock' : 'low_stock',
                ];
            });
        $lowStockAlerts = array_merge($lowStockAlerts, $inventoryStoreLowStock->toArray());
        
        // From linen_housekeeping_items
        $linenLowStock = LinenHousekeepingItem::whereColumn('stock', '<=', 'min_stock')
            ->orWhere('stock', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'category' => $item->category,
                    'current_stock' => $item->stock,
                    'min_stock' => $item->min_stock,
                    'unit' => $item->unit,
                    'type' => 'Linen & Housekeeping',
                    'status' => $item->stock == 0 ? 'out_of_stock' : 'low_stock',
                ];
            });
        $lowStockAlerts = array_merge($lowStockAlerts, $linenLowStock->toArray());
        
        // From amenities_consumables_items
        $amenitiesLowStock = AmenitiesConsumableItem::whereColumn('stock', '<=', 'min_stock')
            ->orWhere('stock', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'category' => $item->category,
                    'current_stock' => $item->stock,
                    'min_stock' => $item->min_stock,
                    'unit' => $item->unit,
                    'type' => 'Amenities Consumables',
                    'status' => $item->stock == 0 ? 'out_of_stock' : 'low_stock',
                ];
            });
        $lowStockAlerts = array_merge($lowStockAlerts, $amenitiesLowStock->toArray());
        
        // From inventory_items
        $inventoryItemsLowStock = InventoryItem::whereColumn('quantity', '<=', 'threshold')
            ->orWhere('quantity', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => null,
                    'name' => $item->name,
                    'category' => $item->category,
                    'current_stock' => $item->quantity,
                    'min_stock' => $item->threshold,
                    'unit' => 'pcs',
                    'type' => 'Inventory Items',
                    'status' => $item->quantity == 0 ? 'out_of_stock' : 'low_stock',
                ];
            });
        $lowStockAlerts = array_merge($lowStockAlerts, $inventoryItemsLowStock->toArray());
        
        // Expiry Tracking - from inventory_items with expiry_date
        $expiryAlerts = InventoryItem::whereNotNull('expiry_date')
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($item) {
                $daysUntilExpiry = Carbon::now()->diffInDays(Carbon::parse($item->expiry_date), false);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'quantity' => $item->quantity,
                    'expiry_date' => $item->expiry_date,
                    'days_until_expiry' => $daysUntilExpiry,
                    'status' => $daysUntilExpiry <= 7 ? 'expiring_soon' : ($daysUntilExpiry <= 30 ? 'expiring_soon' : 'normal'),
                ];
            });
        
        // Usage Consumption from Restaurant & Room Service
        $usageConsumption = [];
        
        // Room Service Consumption
        $roomServiceUsage = RoomServiceItem::with(['order', 'menuItem'])
            ->whereHas('order', function ($query) {
                $query->where('status', '!=', 'CANCELLED');
            })
            ->select('menu_item_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('menu_item_id')
            ->get()
            ->map(function ($item) {
                return [
                    'item_name' => $item->menuItem->name ?? 'Unknown',
                    'source' => 'Room Service',
                    'quantity' => $item->total_qty,
                    'unit' => 'pcs',
                ];
            });
        $usageConsumption = array_merge($usageConsumption, $roomServiceUsage->toArray());
        
        // Restaurant Orders Consumption (if table exists)
        try {
            $restaurantUsage = DB::table('restaurant_order_items')
                ->join('restaurant_orders', 'restaurant_order_items.order_id', '=', 'restaurant_orders.id')
                ->join('menu_items', 'restaurant_order_items.menu_item_id', '=', 'menu_items.id')
                ->where('restaurant_orders.status', '!=', 'cancelled')
                ->select('menu_items.name as item_name', DB::raw('SUM(restaurant_order_items.qty) as total_qty'))
                ->groupBy('menu_items.name')
                ->get()
                ->map(function ($item) {
                    return [
                        'item_name' => $item->item_name,
                        'source' => 'Restaurant',
                        'quantity' => $item->total_qty,
                        'unit' => 'pcs',
                    ];
                });
            $usageConsumption = array_merge($usageConsumption, $restaurantUsage->toArray());
        } catch (\Exception $e) {
            // Table might not exist, skip
        }
        
        return response()->json([
            'low_stock_alerts' => $lowStockAlerts,
            'expiry_alerts' => $expiryAlerts,
            'usage_consumption' => $usageConsumption,
        ]);
    }

    /**
     * Update stock for low stock alert item.
     */
    public function updateStock(Request $request, $type, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            switch ($type) {
                case 'inventory_store':
                    $item = InventoryStoreItem::findOrFail($id);
                    $item->update([
                        'stock' => $request->stock,
                        'min_stock' => $request->min_stock ?? $item->min_stock,
                    ]);
                    break;
                case 'linen_housekeeping':
                    $item = LinenHousekeepingItem::findOrFail($id);
                    $item->update([
                        'stock' => $request->stock,
                        'min_stock' => $request->min_stock ?? $item->min_stock,
                    ]);
                    break;
                case 'amenities_consumables':
                    $item = AmenitiesConsumableItem::findOrFail($id);
                    $item->update([
                        'stock' => $request->stock,
                        'min_stock' => $request->min_stock ?? $item->min_stock,
                    ]);
                    break;
                case 'inventory_items':
                    $item = InventoryItem::findOrFail($id);
                    $item->update([
                        'quantity' => $request->stock,
                        'threshold' => $request->min_stock ?? $item->threshold,
                    ]);
                    break;
                default:
                    return response()->json(['error' => 'Invalid item type'], 400);
            }

            return response()->json(['message' => 'Stock updated successfully', 'item' => $item]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update stock: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update expiry date and quantity for inventory item.
     */
    public function updateExpiry(Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'threshold' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $item = InventoryItem::findOrFail($id);
            $updateData = [
                'quantity' => $request->quantity,
                'threshold' => $request->threshold ?? $item->threshold,
            ];
            
            // Only update expiry_date if provided
            if ($request->has('expiry_date') && $request->expiry_date) {
                $updateData['expiry_date'] = $request->expiry_date;
            }
            
            $item->update($updateData);

            return response()->json(['message' => 'Item updated successfully', 'item' => $item]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete item from alerts (soft delete).
     */
    public function deleteItem($type, $id)
    {
        try {
            switch ($type) {
                case 'inventory_store':
                    $item = InventoryStoreItem::findOrFail($id);
                    $item->delete();
                    break;
                case 'linen_housekeeping':
                    $item = LinenHousekeepingItem::findOrFail($id);
                    $item->delete();
                    break;
                case 'amenities_consumables':
                    $item = AmenitiesConsumableItem::findOrFail($id);
                    $item->delete();
                    break;
                case 'inventory_items':
                    $item = InventoryItem::findOrFail($id);
                    $item->delete();
                    break;
                default:
                    return response()->json(['error' => 'Invalid item type'], 400);
            }

            return response()->json(['message' => 'Item deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete item: ' . $e->getMessage()], 500);
        }
    }
}
