<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => 'string',
        ]);

        $payments = Payment::where('payment_status', $data['status'])->with(['order:id,order_number', 'waiter:id,username', 'cashier:id,username'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $payments
        );

        return response()->json($response, 200);
    }

    public function requestBill(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        Payment::create([
            'payment_number' => 'PAY-' . strtoupper(uniqid()),
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'waiter_id' => auth()->id(),
            'order_id' => $data['order_id'],
            'cashier_id' => 3,
            'createby' =>  auth()->id(),
        ]);

        $response = new ResponseModel(
            'success',
            0,
            null
        );
        return response()->json($response, 200);
    }

    public function paymentOrder()
    {
        $orders = Order::with(['orderDetails.menu:id,name,price', 'table:id,table_no']) // Load order details and menu information
            ->whereHas('payment') // Ensure order has a related payment
            ->join('payments', 'orders.id', '=', 'payments.order_id') // Join with payments table
            ->orderByRaw("CASE 
            WHEN payments.payment_status = 'pending' THEN 1 
            WHEN payments.payment_status = 'completed' THEN 2 
            ELSE 3 
        END")
            ->orderBy('payments.updated_at', 'asc') // Sort by updated_at within each status
            ->select('orders.*', 'payments.id as payment_id', 'payments.payment_status', 'payments.updated_at', 'payments.payment_number')
            ->get();

        // Wrap response in a structured format
        $response = new ResponseModel(
            'success',
            0,
            $orders,
        );

        return response()->json($response, 200);
    }

    public function processPayment(Request $request)
    {
        $data = $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
            'payment_method' => 'required',
            'payment_status' => 'required',
            'order_id' => 'required|integer|exists:orders,id',
            'table_id' => 'required|integer|exists:tables,id',
        ]);

        $payment = Payment::findOrFail($data['payment_id']);
        $order = Order::findOrFail($data['order_id']);
        $table = Table::findOrFail($data['table_id']);

        if ($data['payment_status'] == 'pending') {
            $payment->update([
                'payment_status' => 'completed',
                'payment_method' => $data['payment_method']
            ]);

            $order->update([
                'status' => 'completed',
                'updateby' => auth()->id(),
            ]);

            $table->update([
                'status' => 'available',
            ]);
        }

        $response = new ResponseModel(
            'success',
            0,
            null
        );
        return response()->json($response, 200);
    }
}
