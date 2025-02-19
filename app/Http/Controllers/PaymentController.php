<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
}
