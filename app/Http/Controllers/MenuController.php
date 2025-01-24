<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Menu;
use App\Models\Recipe;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index() 
    {
        $items = Menu::where('active_flag', 1)->with(['category:id,name', ])->latest()->get();

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
                'profile' => 'image|mimes:jpeg,png,jpg|max:2048',
                'name' => 'required|string|max:20|min:3|unique:menus,name',
                'category_id' => 'required',
                'price' => 'required',
                'description' => 'nullable',
                'ingredients' => 'required|string',
            ]);

            $ingredients = json_decode($request->input('ingredients'), true);
            // Handle the profile image upload
            $profilePath = null;
            if ($request->hasFile('profile')) {
                $profile = $request->file('profile');
                $profileName = uniqid() . '_' . $profile->getClientOriginalName();
                $profilePath = $profile->storeAs('uploads', $profileName, 'public'); // Save in 'storage/app/public/uploads'
            }

            // Create the employee record
            $menu = Menu::create([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'price' => $data['price'],
                'description' => $data['description'],
                'profile' => $profilePath ? asset('storage/' . $profilePath) : null, // Save public URL path
                'createby' => 1
            ]);

            if (!is_array($ingredients)) {
                return response()->json(['error' => 'Invalid ingredients format'], 400);
            }
        
            // Example: Iterate over ingredients and process them
            foreach ($ingredients as $ingredient) {
                Recipe::create([
                    'menu_id' => $menu->id,
                    'item_id' => $ingredient['item_id'],
                    'quantity' => $ingredient['quantity'],
                    'createby' => 1
                ]);
            }

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
                'id' => 'required|exists:menus,id',
                'profile' => $request->has('profile') && is_string($request->input('profile'))
                    ? 'required|string|min:3'
                    : 'required|image|mimes:jpeg,png,jpg|max:2048',
                'name' => 'required|string|max:20|min:3',
                'contact_person' => 'required|string|max:20|min:2',
                'phone' => 'required|string',
                'email' => 'required|email|max:255',
                'address' => 'required|string|max:255|min:3',
                'business_type' => 'required|string|max:20|min:3',
            ]);

            $menu = Menu::findOrFail($data['id']);
            if ($menu->name !== $data['name'] && Menu::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Menu Name Already Exist',
                    1,
                    null
                );

                return response()->json($response, 200);
            } else {
                // Handle the profile image upload
                $profilePath = null;
                if ($request->hasFile('profile')) {
                    $profile = $request->file('profile');
                    $profileName = uniqid() . '_' . $profile->getClientOriginalName();
                    $profilePath = $profile->storeAs('uploads', $profileName, 'public'); // Save in 'storage/app/public/uploads'
                }

                // Update the employee record
                $menu->update([
                    'name' => $data['name'],
                    'contact_person' => $data['contact_person'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'business_type' => $data['business_type'],
                    'profile' => $profilePath ? asset('storage/' . $profilePath) : $menu->profile, // Save public URL path
                    'createby' => 1
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
            $menu = Menu::findOrFail($id);
            $menu->active_flag = 0;
            $menu->update();

            return response()->json([
                'status' => 0,
                'message' => 'Menu deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete Menu: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
