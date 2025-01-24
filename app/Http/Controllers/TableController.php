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
}
