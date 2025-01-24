<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (!Auth::attempt($data)) {
            $response = new ResponseModel(
                'Invalid username or password',
                1,
                null
            );
            
            return response()->json($response, 401);
        }

        $user = Auth::user();

        // Create a token for the authenticated user

        $token = $user->createToken('main')->plainTextToken;
        //$token = '3930933';
        $role = '';
        if($user->role_id === 1) {
            $role = 'waiter';
        } elseif($user->role_id === 2) {
            $role = 'chef';
        } elseif($user->role_id === 3) {
            $role = 'cashier';
        } elseif($user->role_id === 4) {
            $role = 'supervisor';
        } 

        $response = new ResponseModel(
            'success',
            0,
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->fullname,
                ],
                'token' => $token,
                'role' => $role,
            ]
            );
        return response()->json($response, 200);
    }

    public function logout(Request $request) {
        
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return response('',204);
    }
}
