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
            'password' => 'required',
            'employee_id' => 'required',
        ]);

        if (!Auth::attempt([
            'username' => $data['username'],
        'password' => $data['password']
        ])) {
            $response = new ResponseModel(
                'Invalid username or password',
                1,
                null
            );
            return response()->json($response);
        }

        $user = Auth::user();

        $userInfo = $user->employeeInfo;

        if (!$userInfo || $userInfo->employee_id !== $data['employee_id']) {
            return response()->json(new ResponseModel(
                'Invalid employee ID',
                1,
                $user
            ));
        }

        // Create a token for the authenticated user
        $token = $user->createToken('user-token', ['user'])->plainTextToken;
        //$token = '3930933';
        $roles = [
            1 => 'waiter',
            2 => 'chef',
            3 => 'cashier',
            4 => 'supervisor'
        ];
        $role = $roles[$user->role_id] ?? 'unknown';

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
