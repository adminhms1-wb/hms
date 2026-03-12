<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Folio;
use App\Models\FolioItem;
use App\Models\Expense;
use App\Models\RoomServiceOrder;
use App\Models\RoomServiceItem;
use App\Models\ServiceBooking;
use App\Models\Amenity;
use App\Models\HousekeepingTask;
use App\Models\InventoryTransaction;
use App\Models\Attendance;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Occupancy Rate Report
     */
    public function occupancyRate(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $totalRooms = Room::count();
        $dailyOccupancy = [];
        $totalRoomNights = 0;
        $availableRoomNights = 0;

        $currentDate = Carbon::parse($from);
        $endDate = Carbon::parse($to);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            
            // Count occupied rooms (rooms with active reservations)
            $occupiedRooms = Reservation::where(function($q) use ($dateStr) {
                $q->where('check_in_date', '<=', $dateStr)
                  ->where('check_out_date', '>', $dateStr)
                  ->whereIn('status', ['confirmed', 'checked_in']);
            })->distinct('room_id')->count('room_id');

            $availableRooms = max(0, $totalRooms - $occupiedRooms);
            $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;

            $dailyOccupancy[] = [
                'date' => $dateStr,
                'occupied_rooms' => $occupiedRooms,
                'available_rooms' => $availableRooms,
                'occupancy_rate' => $occupancyRate
            ];

            $totalRoomNights += $occupiedRooms;
            $availableRoomNights += $availableRooms;

            $currentDate->addDay();
        }

        $averageOccupancyRate = count($dailyOccupancy) > 0 
            ? array_sum(array_column($dailyOccupancy, 'occupancy_rate')) / count($dailyOccupancy) 
            : 0;

        return response()->json([
            'report' => [
                'average_occupancy_rate' => $averageOccupancyRate,
                'total_room_nights' => $totalRoomNights,
                'available_room_nights' => $availableRoomNights,
                'daily_occupancy' => $dailyOccupancy
            ]
        ]);
    }

    /**
     * Daily/Monthly Revenue Report
     */
    public function dailyMonthlyRevenue(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        // Daily revenue
        $dailyRevenue = Folio::whereBetween('folio_date', [$from, $to])
            ->where('status', 'closed')
            ->selectRaw('DATE(folio_date) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->date,
                    'revenue' => (float) $item->revenue
                ];
            });

        // Monthly revenue
        $monthlyRevenue = Folio::whereBetween('folio_date', [$from, $to])
            ->where('status', 'closed')
            ->selectRaw('DATE_FORMAT(folio_date, "%Y-%m") as month, SUM(total) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'month' => $item->month,
                    'revenue' => (float) $item->revenue
                ];
            });

        $totalRevenue = $dailyRevenue->sum('revenue');
        $totalDays = $dailyRevenue->count();
        $averageDailyRevenue = $totalDays > 0 ? $totalRevenue / $totalDays : 0;

        return response()->json([
            'report' => [
                'total_revenue' => $totalRevenue,
                'average_daily_revenue' => $averageDailyRevenue,
                'total_days' => $totalDays,
                'daily_revenue' => $dailyRevenue->toArray(),
                'monthly_revenue' => $monthlyRevenue->toArray()
            ]
        ]);
    }

    /**
     * Room Type Performance Report
     */
    public function roomTypePerformance(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $roomTypes = Reservation::whereBetween('reservations.check_in_date', [$from, $to])
            ->whereIn('reservations.status', ['confirmed', 'checked_in', 'checked_out'])
            ->join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->selectRaw('room_types.name as room_type, 
                        COUNT(DISTINCT reservations.id) as bookings,
                        SUM(reservations.total_amount) as revenue,
                        AVG(reservations.total_amount) as average_rate')
            ->groupBy('room_types.name')
            ->get()
            ->map(function($item) use ($from, $to) {
                // Calculate occupancy rate for this room type
                $totalRooms = Room::whereHas('roomType', function($q) use ($item) {
                    $q->where('name', $item->room_type);
                })->count();

                $occupiedDays = Reservation::whereBetween('reservations.check_in_date', [$from, $to])
                    ->whereHas('room.roomType', function($q) use ($item) {
                        $q->where('name', $item->room_type);
                    })
                    ->whereIn('reservations.status', ['confirmed', 'checked_in', 'checked_out'])
                    ->selectRaw('DATEDIFF(LEAST(reservations.check_out_date, ?), GREATEST(reservations.check_in_date, ?)) as days', [$to, $from])
                    ->get()
                    ->sum('days');

                $totalDays = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
                $availableDays = $totalRooms * $totalDays;
                $occupancyRate = $availableDays > 0 ? ($occupiedDays / $availableDays) * 100 : 0;

                return [
                    'room_type' => $item->room_type,
                    'bookings' => (int) $item->bookings,
                    'revenue' => (float) $item->revenue,
                    'average_rate' => (float) $item->average_rate,
                    'occupancy_rate' => $occupancyRate
                ];
            });

        return response()->json([
            'report' => [
                'room_types' => $roomTypes->toArray()
            ]
        ]);
    }

    /**
     * Restaurant Sales Report
     */
    public function restaurantSales(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        // Get restaurant sales from folio items
        $restaurantItems = FolioItem::whereBetween('created_at', [$from, $to])
            ->where('module', 'restaurant')
            ->get();

        // Daily sales
        $dailySales = $restaurantItems->groupBy(function($item) {
            return Carbon::parse($item->created_at)->toDateString();
        })->map(function($items, $date) {
            return [
                'date' => $date,
                'sales' => $items->sum('amount'),
                'orders' => $items->count()
            ];
        })->values();

        // Top menu items - get from folio items descriptions
        $topItems = $restaurantItems->groupBy('description')
            ->map(function($items, $description) {
                return [
                    'item' => $description,
                    'quantity' => $items->count(),
                    'revenue' => $items->sum('amount')
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        $totalSales = $restaurantItems->sum('amount');
        $totalOrders = $restaurantItems->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return response()->json([
            'report' => [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'average_order_value' => $averageOrderValue,
                'daily_sales' => $dailySales->toArray(),
                'top_items' => $topItems->toArray()
            ]
        ]);
    }

    /**
     * Room Service Performance Report
     */
    public function roomServicePerformance(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $orders = RoomServiceOrder::whereBetween('order_time', [$from, $to])
            ->where('status', 'DELIVERED')
            ->get();

        // Daily performance
        $dailyPerformance = $orders->groupBy(function($order) {
            $orderDate = $order->order_time ?? $order->created_at;
            return Carbon::parse($orderDate)->toDateString();
        })->map(function($dayOrders, $date) {
            return [
                'date' => $date,
                'orders' => $dayOrders->count(),
                'revenue' => $dayOrders->sum('total_amount')
            ];
        })->values();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return response()->json([
            'report' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'average_order_value' => $averageOrderValue,
                'daily_performance' => $dailyPerformance->toArray()
            ]
        ]);
    }

    /**
     * Amenities Usage Report
     */
    public function amenitiesUsage(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $bookings = ServiceBooking::whereBetween('date', [$from, $to])
            ->where('status', 'completed')
            ->with('service')
            ->get();

        $amenities = $bookings->groupBy('service_id')->map(function($bookings, $serviceId) {
            $service = $bookings->first()->service;
            return [
                'amenity' => $service ? $service->name : 'Unknown',
                'usage_count' => $bookings->count(),
                'revenue' => $bookings->sum('total_amount')
            ];
        })->values();

        $totalUsage = $bookings->count();
        $totalRevenue = $bookings->sum('total_amount');
        $mostUsed = $amenities->sortByDesc('usage_count')->first();

        return response()->json([
            'report' => [
                'total_usage' => $totalUsage,
                'total_revenue' => $totalRevenue,
                'most_used_amenity' => $mostUsed ? $mostUsed['amenity'] : 'N/A',
                'amenities' => $amenities->toArray()
            ]
        ]);
    }

    /**
     * Housekeeping Efficiency Report
     */
    public function housekeepingEfficiency(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $tasks = HousekeepingTask::whereBetween('date', [$from, $to])
            ->get();

        // Daily efficiency
        $dailyEfficiency = $tasks->groupBy(function($task) {
            return Carbon::parse($task->date)->toDateString();
        })->map(function($dayTasks, $date) {
            $completed = $dayTasks->where('status', 'completed')->count();
            $total = $dayTasks->count();
            $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;

            return [
                'date' => $date,
                'tasks' => $total,
                'completed' => $completed,
                'completion_rate' => $completionRate
            ];
        })->values();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        return response()->json([
            'report' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $completionRate,
                'daily_efficiency' => $dailyEfficiency->toArray()
            ]
        ]);
    }

    /**
     * Inventory Consumption Report
     */
    public function inventoryConsumption(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $transactions = InventoryTransaction::whereBetween('date', [$from, $to])
            ->where('type', 'out')
            ->with('item')
            ->get();

        $items = $transactions->groupBy('item_id')->map(function($transactions, $itemId) {
            $item = $transactions->first()->item;
            $quantity = $transactions->sum('quantity');
            $unitCost = $item ? $item->unit_cost : 0;
            $totalValue = $quantity * $unitCost;

            return [
                'item' => $item ? $item->name : 'Unknown',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_value' => $totalValue
            ];
        })->values();

        $totalItems = $transactions->sum('quantity');
        $totalValue = $items->sum('total_value');
        $mostConsumed = $items->sortByDesc('quantity')->first();

        return response()->json([
            'report' => [
                'total_items' => $totalItems,
                'total_value' => $totalValue,
                'most_consumed_item' => $mostConsumed ? $mostConsumed['item'] : 'N/A',
                'items' => $items->toArray()
            ]
        ]);
    }

    /**
     * Expense vs Income Report
     */
    public function expenseVsIncome(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        // Income from folios
        $income = Folio::whereBetween('folio_date', [$from, $to])
            ->where('status', 'closed')
            ->selectRaw('DATE(folio_date) as date, SUM(total) as income')
            ->groupBy('date')
            ->pluck('income', 'date');

        // Expenses
        $expenses = Expense::whereBetween('date', [$from, $to])
            ->where('status', 'paid')
            ->selectRaw('DATE(date) as date, SUM(amount) as expenses')
            ->groupBy('date')
            ->pluck('expenses', 'date');

        // Get all dates in range
        $currentDate = Carbon::parse($from);
        $endDate = Carbon::parse($to);
        $dailyComparison = [];

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $dayIncome = $income->get($dateStr, 0);
            $dayExpenses = $expenses->get($dateStr, 0);
            $net = $dayIncome - $dayExpenses;

            $dailyComparison[] = [
                'date' => $dateStr,
                'income' => (float) $dayIncome,
                'expenses' => (float) $dayExpenses,
                'net' => (float) $net
            ];

            $currentDate->addDay();
        }

        $totalIncome = $income->sum();
        $totalExpenses = $expenses->sum();
        $netProfit = $totalIncome - $totalExpenses;

        return response()->json([
            'report' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'daily_comparison' => $dailyComparison
            ]
        ]);
    }

    /**
     * Staff Productivity Report
     */
    public function staffProductivity(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        // Get all staff with attendance and tasks
        $staffIds = Attendance::whereBetween('attendance_date', [$from, $to])
            ->distinct('staff_id')
            ->pluck('staff_id');

        $staffPerformance = $staffIds->map(function($staffId) use ($from, $to) {
            // Attendance rate
            $totalDays = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
            $attendedDays = Attendance::where('staff_id', $staffId)
                ->whereBetween('attendance_date', [$from, $to])
                ->where('status', 'present')
                ->count();
            $attendanceRate = $totalDays > 0 ? ($attendedDays / $totalDays) * 100 : 0;

            // Tasks completed
            $tasksCompleted = TaskAssignment::where('staff_id', $staffId)
                ->whereBetween('created_at', [$from, $to])
                ->where('status', 'completed')
                ->count();

            // Performance score (weighted: 50% attendance, 50% tasks)
            $performanceScore = ($attendanceRate * 0.5) + (min($tasksCompleted * 10, 100) * 0.5);

            $staff = \App\Models\User::find($staffId);

            return [
                'staff_id' => $staffId,
                'staff_name' => $staff ? $staff->name : 'Unknown',
                'attendance_rate' => $attendanceRate,
                'tasks_completed' => $tasksCompleted,
                'performance_score' => $performanceScore
            ];
        });

        $totalStaff = $staffPerformance->count();
        $averageAttendance = $staffPerformance->avg('attendance_rate');
        $tasksCompleted = $staffPerformance->sum('tasks_completed');

        return response()->json([
            'report' => [
                'total_staff' => $totalStaff,
                'average_attendance' => $averageAttendance,
                'tasks_completed' => $tasksCompleted,
                'staff_performance' => $staffPerformance->toArray()
            ]
        ]);
    }
}
