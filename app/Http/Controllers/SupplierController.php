<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('active_flag', 1)->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $suppliers
        );

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'profile' => 'image|mimes:jpeg,png,jpg|max:2048',
                'name' => 'required|string|max:20|min:3|unique:suppliers,name',
                'contact_person' => 'required|string|max:20|min:2',
                'phone' => 'required|string',
                'email' => 'required|email|max:255',
                'address' => 'required|string|max:255|min:3',
                'business_type' => 'required|string|max:20|min:3',
            ]);

            // Handle the profile image upload
            $profilePath = null;
            if ($request->hasFile('profile')) {
                $profile = $request->file('profile');
                $profileName = uniqid() . '_' . $profile->getClientOriginalName();
                $profilePath = $profile->storeAs('uploads', $profileName, 'public'); // Save in 'storage/app/public/uploads'
            }

            // Create the employee record
            $supplier = Supplier::create([
                'name' => $data['name'],
                'contact_person' => $data['contact_person'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => $data['address'],
                'business_type' => $data['business_type'],
                'profile' => $profilePath ? asset('storage/' . $profilePath) : null, // Save public URL path
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

    public function update(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'id' => 'required|exists:suppliers,id',
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

            $supplier = Supplier::findOrFail($data['id']);
            if ($supplier->name !== $data['name'] && Supplier::where('name', $data['name'])->count() > 0) {
                $response = new ResponseModel(
                    'Supplier Name Already Exist',
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
                $supplier->update([
                    'name' => $data['name'],
                    'contact_person' => $data['contact_person'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'business_type' => $data['business_type'],
                    'profile' => $profilePath ? asset('storage/' . $profilePath) : $supplier->profile, // Save public URL path
                    'createby' => auth()->id(),
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
            $supplier = Supplier::findOrFail($id);
            $supplier->active_flag = 0;
            $supplier->updateby = auth()->id();
            $supplier->update();

            return response()->json([
                'status' => 0,
                'message' => 'Supplier deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete supplier: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
