<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\EmployeeInfo;
use App\Models\InventoryItem;
use App\Models\Menu;
use App\Models\Supplier;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function overallStaticData(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'status' => 'string',
        ]);
        $status = $validated['status'] ?? 'all';

        // Define base queries for each metric
        $queries = [
            'inventory_items' => InventoryItem::query()->where('active_flag', 1),
            'suppliers'       => Supplier::query()->where('active_flag', 1),
            'menu_items'      => Menu::query()->where('active_flag', 1),
            'employees'       => EmployeeInfo::query()->where('active_flag', 1),
            'tables'          => Table::query()->where('active_flag', 1),
            'customers'       => Table::query()->where('active_flag', 1),
        ];

        // Apply additional filters based on status
        if ($status === 'active') {
            $queries['inventory_items']->where('status', 'active');
            $queries['suppliers']->where('status', 1);
            $queries['menu_items']->where('status', 'available');
            $queries['employees']->where('status', 'active');
            $queries['tables']->where('status', 'available');
            $queries['customers']->where('status', 'available');
        } elseif ($status === 'inactive') {
            $queries['inventory_items']->where('status', 'inactive');
            $queries['suppliers']->where('status', 0);
            $queries['menu_items']->where('status', 'unavailable');
            $queries['employees']->where('status', 'inactive');
            $queries['tables']->where('status', '!=', 'available');
            $queries['customers']->where('status', 'available');
        }
        // For 'all', no additional filter is applied

        // Execute count queries
        $data = [];
        foreach ($queries as $key => $query) {
            $data[$key] = $query->count();
        }

        // Calculate this month's total purchase
        $currentMonth = now()->format('Y-m');
        $thisMonthTotal = DB::table('purchases')
            ->where('active_flag', 1)->where('status', 'completed')
            ->whereRaw("DATE_FORMAT(purchase_date, '%Y-%m') = ?", [$currentMonth])
            ->sum('total_amount');

        // Get latest purchase record

        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
        $lastMonthTotal = DB::table('purchases')
            ->where('active_flag', 1)->where('status', 'completed')
            ->whereRaw("DATE_FORMAT(purchase_date, '%Y-%m') = ?", [$lastMonth])
            ->sum('total_amount');

        $allMonthTotal = DB::table('purchases')
            ->where('active_flag', 1)->where('status', 'completed')
            ->sum('total_amount');


        // Monthly purchases for chart (Jan to Dec)
        $monthlyPurchasesRaw = DB::table('purchases')
            ->selectRaw('MONTH(purchase_date) as month, SUM(total_amount) as total')
            ->where('active_flag', 1)->where('status', 'completed')
            ->groupBy(DB::raw('MONTH(purchase_date)'))
            ->pluck('total', 'month');



        $monthNames = [
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec",
        ];

        $monthlyPurchaseData = [];
        foreach ($monthNames as $num => $name) {
            $monthlyPurchaseData[] = [
                'name' => $name,
                'value' => (float)($monthlyPurchasesRaw[$num] ?? 0),
            ];
        }

        // Include new purchase-related data
        $data['this_month_purchase_total'] = (float) $thisMonthTotal;
        $data['last_month_purhcase_total'] = (float) $lastMonthTotal;
        $data['all_month_purhcase_total'] = (float) $allMonthTotal;
        $data['monthly_purchase_chart'] = $monthlyPurchaseData;

        // Calculate this month's sales revenue (from completed payments)
        $currentMonth = now()->format('Y-m');
        $thisMonthRevenue = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('menus', 'order_details.menu_id', '=', 'menus.id')
            ->where('payments.payment_status', 'completed')
            ->where('orders.status', 'completed')
            ->whereRaw("DATE_FORMAT(payments.created_at, '%Y-%m') = ?", [$currentMonth])
            ->selectRaw('SUM(order_details.quantity * menus.price) as total')
            ->value('total') ?? 0;

        // Last month's revenue
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
        $lastMonthRevenue = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('menus', 'order_details.menu_id', '=', 'menus.id')
            ->where('payments.payment_status', 'completed')
            ->where('orders.status', 'completed')
            ->whereRaw("DATE_FORMAT(payments.created_at, '%Y-%m') = ?", [$lastMonth])
            ->selectRaw('SUM(order_details.quantity * menus.price) as total')
            ->value('total') ?? 0;

        // All-time revenue
        $allTimeRevenue = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('menus', 'order_details.menu_id', '=', 'menus.id')
            ->where('payments.payment_status', 'completed')
            ->where('orders.status', 'completed')
            ->selectRaw('SUM(order_details.quantity * menus.price) as total')
            ->value('total') ?? 0;

        // Monthly revenue for chart (Jan-Dec)
        $monthlyRevenueRaw = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('menus', 'order_details.menu_id', '=', 'menus.id')
            ->selectRaw('MONTH(payments.created_at) as month, SUM(order_details.quantity * menus.price) as total')
            ->where('payments.payment_status', 'completed')
            ->where('orders.status', 'completed')
            ->groupBy(DB::raw('MONTH(payments.created_at)'))
            ->pluck('total', 'month');

        $monthlyRevenueData = [];
        foreach ($monthNames as $num => $name) {
            $monthlyRevenueData[] = [
                'name' => $name,
                'value' => (float)($monthlyRevenueRaw[$num] ?? 0),
            ];
        }

        // Add to your data array
        $data['this_month_revenue'] = (float)$thisMonthRevenue;
        $data['last_month_revenue'] = (float)$lastMonthRevenue;
        $data['all_time_revenue'] = (float)$allTimeRevenue;
        $data['monthly_revenue_chart'] = $monthlyRevenueData;

        // Get top 5 selling menu items (all time)
        $topMenuItems = DB::table('order_details')
            ->join('menus', 'order_details.menu_id', '=', 'menus.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('payments', 'orders.id', '=', 'payments.order_id')
            ->select(
                'menus.id',
                'menus.name',
                'menus.profile as icon',
                DB::raw('SUM(order_details.quantity) as total_sold'),
                DB::raw('ROUND(SUM(order_details.quantity * menus.price), 2) as total_revenue')
            )
            ->where('payments.payment_status', 'completed')
            ->where('orders.status', 'completed')
            ->groupBy('menus.id', 'menus.name', 'menus.profile')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Calculate total sold items for percentage
        $totalSold = $topMenuItems->sum('total_sold');

        // Format data for dashboard
        $topMenuData = $topMenuItems->map(function ($item) use ($totalSold) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'icon' => $item->icon ?? 'default-food-icon.png', // fallback icon
                'sold' => $item->total_sold,
                'revenue' => $item->total_revenue,
                'percentage' => $totalSold > 0
                    ? round(($item->total_sold / $totalSold) * 100, 1)
                    : 0,
            ];
        });

        // Add to your dashboard data
        $data['top_menu_items'] = $topMenuData;

        // Create response model (assuming ResponseModel is defined in your project)
        $response = new ResponseModel('success', 0, $data);

        return response()->json($response, 200);
    }
}
