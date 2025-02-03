<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::where('active_flag', 1)->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $tables
        );

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'table_no' => 'required|string|max:20|min:2|unique:tables,table_no',
                'capacity' => 'required',
                'status' => 'required'
            ]);

            // Create the employee record
            $item = Table::create([
                'table_no' => $data['table_no'],
                'capacity' => $data['capacity'],
                'status' => $data['status'],
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
                'id' => 'required|exists:tables,id',
                'table_no' => 'required|string|max:20|min:2',
                'capacity' => 'required',
                'status' => 'required'
            ]);

            $item = Table::findOrFail($data['id']);
            if ($item->table_no !== $data['table_no'] && Table::where('table_no', $data['table_no'])->count() > 0) {
                $response = new ResponseModel(
                    'Table Number Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {

                // Update the employee record
                $item->update([
                    'table_no' => $data['table_no'],
                    'capacity' => $data['capacity'],
                    'status' => $data['status'],
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
            $item = Table::findOrFail($id);
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
