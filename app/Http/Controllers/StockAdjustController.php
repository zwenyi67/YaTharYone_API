<?php


namespace App\Http\Controllers;
use App\Models\StockAdjustment;
use App\Http\Helpers\ResponseModel;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class StockAdjustController extends Controller
{
    public function index()
    {
        $stocks = StockAdjustment::where('active_flag', 1)->with(['item'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $stocks
        );

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'item_id' => 'required|exists:inventory_items,id',
                'quantity' => 'required',
                'adjustment_type' => 'required|string',
                'adjustment_date' => 'required',
                'reason' => 'required|string',
            ]);

            $item = InventoryItem::findOrFail($data['item_id']);

            if($item->min_stock_level < $data['quantity']) {
                $response = new ResponseModel(
                    "Invalid or Wrong Quantity for this item",
                    1,
                    null
                );
                return response()->json($response);
            }       
            
            $item->current_stock -= $data['quantity'];
            $item->update(); 

            $stock = StockAdjustment::create([
                'item_id' => $data['item_id'],
                'quantity' => $data['quantity'],
                'adjustment_type' => $data['adjustment_type'],
                'adjustment_date' => $data['adjustment_date'],
                'createby' => auth()->id(),
            ]);
            // Prepare the response
            $response = new ResponseModel(
                'success',
                0,
                null
            );

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response);
        }
    }
}
