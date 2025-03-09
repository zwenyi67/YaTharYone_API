<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Menu;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index()
    {
        $items = Menu::where('active_flag', 1)
            ->with([
                'category:id,name',
                'inventoryItems:id,name,unit_of_measure',
                'addonItems:id,name,unit_of_measure' // Include addonItems relationship
            ])
            ->latest()
            ->get()
            ->map(function ($item) {
                // Process inventory items
                $item->inventory_items = $item->inventoryItems->map(function ($inventoryItem) {
                    $inventoryItem->quantity = $inventoryItem->pivot->quantity; // Add pivot quantity
                    unset($inventoryItem->pivot); // Remove pivot object
                    return $inventoryItem;
                });

                // Process addon items
                $item->addon_items = $item->addonItems->map(function ($addonItem) {
                    $addonItem->quantity = $addonItem->pivot->quantity; // Add pivot quantity
                    $addonItem->additional_price = $addonItem->pivot->additional_price; // Add pivot additional_price
                    unset($addonItem->pivot); // Remove pivot object
                    return $addonItem;
                });

                // Remove the original inventoryItems and addonItems keys
                unset($item->inventoryItems);
                unset($item->addonItems);

                return $item;
            });

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
                'category_id' => 'required|exists:menu_categories,id',
                'price' => 'required',
                'description' => 'nullable',
                'ingredients' => 'required|json',
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

            // To add in pivot table 
            foreach ($ingredients as $ingredient) {
                // Validate each ingredient's data
                if (
                    !isset($ingredient['item_id']) ||
                    !isset($ingredient['quantity']) ||
                    !is_numeric($ingredient['quantity']) ||
                    $ingredient['quantity'] <= 0
                ) {
                    return response()->json(['error' => 'Invalid ingredient data provided'], 400);
                }

                // Use the `attach` method to add data to the pivot table
                $menu->inventoryItems()->attach($ingredient['item_id'], [
                    'quantity' => $ingredient['quantity'],
                    'createby' => 1,
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
            return response()->json($response);
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
            return response()->json($response);
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
            ]);
        }
    }

    public function addonItem(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'menu_id' => 'required|integer|exists:menus,id',
                'addon_items' => 'required|array|min:1',
                'addon_items.*.id' => 'required|integer|exists:inventory_items,id',
                'addon_items.*.quantity' => 'required|numeric|min:1',
                'addon_items.*.additional_price' => 'required|numeric|min:0',
                'createby' => 'required|integer',
            ]);

            // Find the menu
            $menu = Menu::findOrFail($data['menu_id']);

            // Use a database transaction to ensure atomicity
            DB::transaction(function () use ($menu, $data) {

                $menu->addonItems()->detach();

                $addonItemsData = [];

                foreach ($data['addon_items'] as $item) {
                    $addonItemsData[$item['id']] = [
                        'quantity' => $item['quantity'],
                        'additional_price' => $item['additional_price'],
                        'created_at' => now(),
                        'createby' => 1,
                    ];
                }

                // Attach multiple items to the pivot table
                $menu->addonItems()->syncWithoutDetaching($addonItemsData);
            });

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
