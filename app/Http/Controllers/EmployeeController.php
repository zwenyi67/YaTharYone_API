<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\EmployeeInfo;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'role_id' => 'exists:roles,id',
        ]);

        $employees = EmployeeInfo::where('active_flag', 1)->where('role_id', $data['role_id'])->latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $employees
        );

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $data = $request->validate([
                'employee_id' => 'required|string|max:20|min:4',
                'fullname' => 'required|string|max:50|min:4',
                'phone' => 'required|string',
                'email' => 'required|email|max:255|unique:employee_infos,email',
                'gender' => 'required|in:male,female,Other',
                'birth_date' => 'required|date',
                'date_hired' => 'required|date',
                'address' => 'required|string|max:255|min:3',
                'role_id' => 'required|numeric|exists:roles,id',
                'username' => 'required|string|max:20|min:3|unique:users,username',
                'password' => 'required|string|max:16|min:8',
                'profile' => 'image|mimes:jpeg,png,jpg|max:2048', // Validate image file
            ]);

            // Handle the profile image upload
            $profilePath = null;
            if ($request->hasFile('profile')) {
                $profile = $request->file('profile');
                $profileName = uniqid() . '_' . $profile->getClientOriginalName();
                $profilePath = $profile->storeAs('uploads', $profileName, 'public'); // Save in 'storage/app/public/uploads'
            }

            // Create the employee record
            $employee = EmployeeInfo::create([
                'employee_id' => $data['employee_id'],
                'fullname' => $data['fullname'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'date_hired' => $data['date_hired'],
                'address' => $data['address'],
                'role_id' => $data['role_id'],
                'profile' => $profilePath ? asset('storage/' . $profilePath) : null, // Save public URL path
                'createby' => auth()->id()
            ]);

            // Hash the password
            $data['password'] = bcrypt($data['password']);

            // Create the user record
            User::create([
                'username' => $data['username'],
                'password' => $data['password'],
                'role_id' => $data['role_id'],
                'employeeInfo_id' => $employee->id,
                'createby' => auth()->id()
            ]);

            // Prepare the response
            $response = new ResponseModel(
                'success',
                0,
                $employee
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
                'id' => 'required|exists:employee_infos,id',
                'employee_id' => 'required|string|max:20|min:4',
                'fullname' => 'required|string|max:50|min:4',
                'phone' => 'required|string',
                'email' => 'required|email|max:255|unique:employee_infos,email,' . $request->id,
                'gender' => 'required|in:male,female,Other',
                'birth_date' => 'required|date',
                'date_hired' => 'required|date',
                'address' => 'required|string|max:255|min:3',
                'role_id' => 'required|numeric|exists:roles,id',
                'username' => 'required|string|max:20|min:3|unique:users,username,' . $request->id . ',employeeInfo_id',
                'password' => 'nullable|string|max:16|min:8',
                'profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validate optional image file
            ]);

            // Find the employee record
            $employee = EmployeeInfo::findOrFail($data['id']);

            // Handle the profile image upload
            if ($request->hasFile('profile')) {
                $profile = $request->file('profile');
                $profileName = uniqid() . '_' . $profile->getClientOriginalName();
                $profilePath = $profile->storeAs('uploads', $profileName, 'public'); // Save in 'storage/app/public/uploads'
                $employee->profile = asset('storage/' . $profilePath); // Update the profile path
            }

            // Update employee information
            $employee->update([
                'employee_id' => $data['employee_id'],
                'fullname' => $data['fullname'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'date_hired' => $data['date_hired'],
                'address' => $data['address'],
                'role_id' => $data['role_id'],
                'updateby' => auth()->id()
            ]);

            // Find or create user record
            $user = User::where('employeeInfo_id', $employee->id)->firstOrNew();
            $user->username = $data['username'];
            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
            }
            $user->role_id = $data['role_id'];
            $user->employeeInfo_id = $employee->id;
            $user->updateby = auth()->id();
            $user->save();

            // Prepare the response
            $response = new ResponseModel(
                'success',
                0,
                $employee
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

    public function delete($id)
    {
        try {
            $employee = EmployeeInfo::findOrFail($id);
            $employee->active_flag = 0;
            //$user = User::where('employeeInfo_id', $id);
            //$user
            $employee->update();

            return response()->json([
                'status' => 0,
                'message' => 'Employee deleted successfully.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 1,
                'message' => 'Failed to delete employee: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
