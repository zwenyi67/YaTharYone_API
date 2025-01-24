<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseModel;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index() {
        $categories = Role::latest()->get();

        $response = new ResponseModel(
            'success',
            0,
            $categories
        );

        return response()->json($response,200);
    }
}
