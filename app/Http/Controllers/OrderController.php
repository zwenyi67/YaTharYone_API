<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index()
    {
        $tables = Order::with(['orderDetails.menu','table:id,table_no','waiter:id,username'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $tables
        );

        return response()->json($response, 200);
    }

    public function getOrderById(Request $request)
    {
        $validated = $request->validate([
            'orderId' => 'required|integer|exists:orders,id', // Adjust 'item_categories' to your actual table name
        ]);

        $orderId = $validated['orderId'];
        $tables = Order::where('id', $orderId)->with(['orderDetails.menu:id,name,price,status'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $tables
        );

        return response()->json($response, 200);
    } 

    public function currentOrderList()
    {
        $orders = Order::where('status', '!=' ,'completed')->with('orderDetails.menu','table:id,table_no')->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $orders
        );

        return response()->json($response, 200);
    }

    public function proceedOrder(Request $request) {
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

        if($data['order_id'] === 'null') {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'status' => $data['status'],
                'table_id' => $data['table_id'],
                'waiter_id' => auth()->id(),
                'createby' => auth()->id(),
            ]);
        } else {
            $order = Order::findOrFail($data['order_id']);
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
