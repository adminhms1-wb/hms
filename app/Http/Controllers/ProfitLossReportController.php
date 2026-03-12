<?php

namespace App\Http\Controllers;

use App\Models\Folio;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;

class ProfitLossReportController extends Controller
{
    public function index(Request $request)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'view_financial_reports');
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        // Revenue from folios
        $folioRevenue = Folio::whereBetween('folio_date', [$from, $to])
            ->where('status', 'closed')
            ->sum('total');

        // Revenue breakdown
        $revenueBreakdown = [
            'Room Revenue' => Folio::whereBetween('folio_date', [$from, $to])
                ->where('status', 'closed')
                ->whereHas('items', function($q) {
                    $q->where('module', 'room');
                })
                ->sum('total'),
            'Restaurant Revenue' => Folio::whereBetween('folio_date', [$from, $to])
                ->where('status', 'closed')
                ->whereHas('items', function($q) {
                    $q->where('module', 'restaurant');
                })
                ->sum('total'),
            'Services Revenue' => Folio::whereBetween('folio_date', [$from, $to])
                ->where('status', 'closed')
                ->whereHas('items', function($q) {
                    $q->where('module', 'service');
                })
                ->sum('total'),
        ];

        // Expenses
        $totalExpenses = Expense::whereBetween('date', [$from, $to])
            ->where('status', 'paid')
            ->sum('amount');

        // Expense breakdown by category
        $expenseBreakdown = Expense::whereBetween('date', [$from, $to])
            ->where('status', 'paid')
            ->selectRaw('COALESCE(category, "Uncategorized") as category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $netProfit = $folioRevenue - $totalExpenses;

        return response()->json([
            'report' => [
                'total_revenue' => $folioRevenue,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'revenue_breakdown' => $revenueBreakdown,
                'expense_breakdown' => $expenseBreakdown,
            ]
        ]);
    }
}
