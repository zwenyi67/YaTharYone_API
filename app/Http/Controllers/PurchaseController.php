<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request) 
    {
        $data = $request->validate([
            'status' => 'string',
        ]);

        $items = Purchase::where('active_flag', 1)->where('status', $data['status'])->with(['purchaseDetails.item:id,name,unit_of_measure', 'supplier:id,name,profile'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $items
        );

        return response()->json($response, 200);
    }

    public function itemListbyCategory(Request $request)
    {
        // Validate the categoryId parameter
        $validated = $request->validate([
            'categoryId' => 'required|integer|exists:inventory_item_categories,id',
        ]);

        $categoryId = $validated['categoryId'];

        // Fetch items belonging to the specified category
        $items = InventoryItem::where('active_flag', 1)->where('item_category_id', $categoryId)->get(); 

        // Return the items as a JSON response
        return response()->json([
            'status' => 0, // 0 for success (according to your convention)
            'data' => $items,
            'message' => 'Items retrieved successfully.',
        ]);
    }

    public function requestPurchase(Request $request)
    {
        $data = $request->validate([
            'purchase_items' => 'required|array',
            'purchase_items.*.item_id' => 'required|integer', // Validate each item
            'purchase_items.*.quantity' => 'required|integer|min:1', // Quantity must be at least 1
            'purchase_items.*.total_cost' => 'required|numeric|min:0',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'total_amount' => 'required|numeric|min:0',
            'purchase_note' => 'nullable|string'
        ]);

        $purchase = Purchase::create([
            'purchase_date' => now(),
            'total_amount' => $data['total_amount'],
            'purchase_note' => $data['purchase_note'] ?? null,
            'supplier_id' => $data['supplier_id'],
            'createby' => auth()->id(),
        ]);

        foreach ($data['purchase_items'] as $item) {
            PurchaseDetail::create([
                'purchase_id' => $purchase->id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'total_cost' => $item['total_cost'],
                'createby' => auth()->id(),
            ]);
        }

        $response = new ResponseModel(
            'success',
            0,
            null
        );
        return response()->json($response, 200);
    }

    public function confirm($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->status = "completed";
            $purchase->updateby = auth()->id();
            $purchase->update();

            foreach($purchase->purchaseDetails as $item) {
                $inventory = InventoryItem::findOrFail($item->item_id);
                $inventory->current_stock += $item->quantity;
                $inventory->updateby = auth()->id();
                $inventory->update();
            }

            return response()->json([
                'status' => 0,
                'message' => 'Purchase Order Confirmed successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete item: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    public function cancel($id)
    {
        try {
            $item = Purchase::findOrFail($id);
            $item->status = "cancelled";
            $item->update();

            return response()->json([
                'status' => 0,
                'message' => 'Purchase Order Cancelled successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete item: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
