<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index()
    {
        $items = InventoryItem::where('active_flag', 1)->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $items
        );

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'name' => 'required|string|max:20|min:3|unique:inventory_items,name',
                'unit_of_measure' => 'required|string|max:20|min:1',
                'current_stock' => 'required',
                'reorder_level' => 'required',
                'min_stock_level' => 'required',
                'item_category_id' => 'required',
                'expiry_date' => 'required',
                'is_perishable' => 'nullable',
                'description' => 'nullable'
            ]);

            // Create the employee record
            $item = InventoryItem::create([
                'name' => $data['name'],
                'unit_of_measure' => $data['unit_of_measure'],
                'current_stock' => $data['current_stock'],
                'reorder_level' => $data['reorder_level'],
                'min_stock_level' => $data['min_stock_level'],
                'item_category_id' => $data['item_category_id'],
                'expiry_date' => $data['expiry_date'],
                'is_perishable' => $data['is_perishable'] ?? 0,
                'description' => $data['description'],
                'createby' => 1
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
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'id' => 'required|exists:inventory_items,id',
                'name' => 'required|string|max:20|min:3',
                'unit_of_measure' => 'required|string|max:20|min:1',
                'current_stock' => 'required',
                'reorder_level' => 'required',
                'min_stock_level' => 'required',
                'item_category_id' => 'required',
                'expiry_date' => 'required',
                'is_perishable' => 'nullable',
                'description' => 'nullable'
            ]);

            $item = InventoryItem::findOrFail($data['id']);
            if ($item->name !== $data['name'] && InventoryItem::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Inventory Name Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {

                // Update the employee record
                $item->update([
                    'name' => $data['name'],
                    'unit_of_measure' => $data['unit_of_measure'],
                    'current_stock' => $data['current_stock'],
                    'reorder_level' => $data['reorder_level'],
                    'min_stock_level' => $data['min_stock_level'],
                    'item_category_id' => $data['item_category_id'],
                    'expiry_date' => $data['expiry_date'],
                    'is_perishable' => $data['is_perishable'] ?? 0,
                    'description' => $data['description'],
                    'updateby' => 1
                ]);

                // Prepare the response
                $response = new ResponseModel(
                    'success',
                    0,
                    null
                );

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = new ResponseModel(
                $e->getMessage(),
                2,
                null
            );
            return response()->json($response, 500);
        }
    }

    public function delete($id)
    {
        try {
            $item = InventoryItem::findOrFail($id);
            $item->active_flag = 0;
            $item->update();

            return response()->json([
                'status' => 0,
                'message' => 'Inventory Item deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete item: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
