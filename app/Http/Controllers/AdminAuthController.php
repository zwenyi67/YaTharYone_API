<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Messages;
use App\Http\Helpers\ResponseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::guard('admin')->attempt($data)) {
            $response = new ResponseModel(
                'Invalid email or password',
                1,
                null
            );
            
            return response()->json($response);
        }

        $admin = Auth::guard('admin')->user();

        // Create a token for the authenticated admin

        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;
        //$token = '3930933';

        $response = new ResponseModel(
            'success',
            0,
            [
                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ],
                'token' => $token,
                'role' => 'admin',
            ]
            );
        return response()->json($response, 200);
    }

    public function logout(Request $request) {
        
        $admin = $request->user();

        $admin->currentAccessToken()->delete();

        return response('',204);
    }
}
