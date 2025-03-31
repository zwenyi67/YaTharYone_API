<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => 'string',
        ]);

        $orders = Order::where('status', $data['status'])->with(['orderDetails.menu', 'table:id,table_no', 'waiter:id,username'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $orders
        );

        return response()->json($response, 200);
    }

    public function getOrderById(Request $request)
    {
        $validated = $request->validate([
            'orderId' => 'required|integer|exists:orders,id', // Adjust 'item_categories' to your actual table name
        ]);

        $orderId = $validated['orderId'];
        $tables = Order::where('id', $orderId)->with(['orderDetails.menu:id,name,price,status', 'table:id,table_no', 'payment'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $tables
        );

        return response()->json($response, 200);
    }

    public function readyOrderList()
    {
        $orders = Order::where('waiter_id', auth()->id())
            ->whereHas('orderDetails', function ($query) {
                $query->where('status', '!=', 'served');
            })
            ->with(['orderDetails.menu'])
            ->latest()
            ->get();

        $response = new ResponseModel(
            'success',
            0,
            $orders
        );

        return response()->json($response, 200);
    }



    public function currentOrderList()
    {
        $orders = Order::where('status', '!=', 'completed')->where('status', '!=', 'served')->with('orderDetails.menu', 'table:id,table_no')->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $orders
        );

        return response()->json($response, 200);
    }

    public function serveOrder(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'orderDetail_id' => 'required|integer|exists:order_details,id',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($data['order_id']);
        $orderDetail = OrderDetail::findOrFail($data['orderDetail_id']);

        if ($data['status'] === 'ready') {

            // Mark order detail as 'ready'
            $orderDetail->update(['status' => 'served']);

            // Step 1: Get the related menu item
            $menu = $orderDetail->menu;

            if ($menu) {
                // Step 2: Get inventory items linked to this menu via pivot table
                $inventoryItems = $menu->inventoryItems;

                foreach ($inventoryItems as $inventoryItem) {
                    // Step 3: Reduce stock based on pivot quantity
                    $pivotData = $inventoryItem->pivot;

                    if ($pivotData) {
                        $inventoryItem->update([
                            'current_stock' => max(0, $inventoryItem->current_stock - ($pivotData->quantity * $orderDetail->quantity))
                        ]);
                    }
                }
            }

            // Step 4: Check if all order details are 'ready'
            $allReady = $order->orderDetails()->where('status', '!=', 'served')->count() === 0;

            if ($allReady) {
                $order->update(['status' => 'served']);
            }
        }

        return response()->json(new ResponseModel('success', 0, null), 200);
    }

    public function startPreparing(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'orderDetail_id' => 'required|integer|exists:order_details,id',
            'quantity' => 'integer',
            'status' => 'required',
        ]);

        $order = Order::findOrFail($data['order_id']);
        $orderDetail = OrderDetail::findOrFail($data['orderDetail_id']);

        if ($data['status'] == 'pending') {
            $order->update([
                'status' => 'preparing'
            ]);

            $orderDetail->update([
                'status' => 'preparing'
            ]);
        }

        $response = new ResponseModel(
            'success',
            0,
            null
        );
        return response()->json($response, 200);
    }

    public function markAsReady(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'orderDetail_id' => 'required|integer|exists:order_details,id',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($data['order_id']);
        $orderDetail = OrderDetail::findOrFail($data['orderDetail_id']);

        if ($data['status'] === 'preparing') {

            // Mark order detail as 'ready'
            $orderDetail->update(['status' => 'ready']);

            // Step 1: Get the related menu item
            $menu = $orderDetail->menu;

            if ($menu) {
                // Step 2: Get inventory items linked to this menu via pivot table
                $inventoryItems = $menu->inventoryItems;

                foreach ($inventoryItems as $inventoryItem) {
                    // Step 3: Reduce stock based on pivot quantity
                    $pivotData = $inventoryItem->pivot;

                    if ($pivotData) {
                        $inventoryItem->update([
                            'current_stock' => max(0, $inventoryItem->current_stock - ($pivotData->quantity * $orderDetail->quantity))
                        ]);
                    }
                }
            }

            // Step 4: Check statuses of all order details
            $allOrderDetails = $order->orderDetails()->pluck('status')->toArray();

            if (in_array('pending', $allOrderDetails) || in_array('preparing', $allOrderDetails)) {
                // If any item is still pending or preparing, do not change order status
            } elseif (count(array_unique($allOrderDetails)) === 1 && $allOrderDetails[0] === 'served') {
                // If all items are served, update order status to 'served'
                $order->update(['status' => 'served']);
            } else {
                // If all items are at least 'ready' (but not all 'served'), set order status to 'ready'
                $order->update(['status' => 'ready']);
            }
        }

        return response()->json(new ResponseModel('success', 0, null), 200);
    }


    public function proceedOrder(Request $request)
    {
        $data = $request->validate([
            'order_list' => 'required|array',
            'order_list.*.id' => 'required|integer|exists:menus,id',
            'order_list.*.note' => 'nullable|string',
            'order_list.*.quantity' => 'required|integer|min:1',
            'table_id' => 'required|integer|exists:tables,id',
            'waiter_id' => 'required|integer',
            'status' => 'required',
            'order_id' => 'required',
        ]);

        if ($data['order_id'] === 'null') {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'status' => $data['status'],
                'table_id' => $data['table_id'],
                'waiter_id' => auth()->id(),
                'createby' => auth()->id(),
            ]);
        } else {
            $order = Order::findOrFail($data['order_id']);
            $order->update([
                'status' => 'preparing'
            ]);
        }


        foreach ($data['order_list'] as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'menu_id' => $item['id'],
                'quantity' => $item['quantity'],
                'note' => $item['note'] ?? null,
                'createby' => auth()->id(),
            ]);
        }

        $table = Table::findOrFail($data['table_id']);
        $table->update([
            'status' => 'occupied',
        ]);

        $response = new ResponseModel(
            'success',
            0,
            null
        );
        return response()->json($response, 200);
    }
}
