<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\EmployeeInfo;
use App\Models\InventoryItem;
use App\Models\Menu;
use App\Models\Supplier;
use App\Models\Table;
use Illuminate\Http\Request;

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
            'customers'       => Table::query()->where('active_flag', 1), // Assuming customers are tracked in Table model
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
            $queries['customers']->where('status', 'available'); // Business logic: kept same as active for customers
        }
        // For 'all', no additional filter is applied

        // Execute count queries
        $data = [];
        foreach ($queries as $key => $query) {
            $data[$key] = $query->count();
        }

        // Create response model (assuming ResponseModel is defined in your project)
        $response = new ResponseModel('success', 0, $data);

        return response()->json($response, 200);
    }
}
