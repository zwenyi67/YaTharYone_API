<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index() 
    {
        $items = MenuCategory::where('active_flag', 1)->latest()->get();

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
                'name' => 'required|string|max:20|min:3|unique:menu_categories,name',
                'description' => 'nullable'
            ]);

            // Create the employee record
            $item = MenuCategory::create([
                'name' => $data['name'],
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
            return response()->json($response);
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'id' => 'required|exists:menu_categories,id',
                'name' => 'required|string|max:20|min:3',
                'description' => 'nullable'
            ]);

            $item = MenuCategory::findOrFail($data['id']);
            if ($item->name !== $data['name'] && MenuCategory::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Category Name Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {

                // Update the employee record
                $item->update([
                    'name' => $data['name'],
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
            return response()->json($response);
        }
    }

    public function delete($id)
    {
        try {
            $item = MenuCategory::findOrFail($id);
            $item->active_flag = 0;
            $item->update();

            return response()->json([
                'status' => 0,
                'message' => 'Menu Category deleted successfully.',
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
