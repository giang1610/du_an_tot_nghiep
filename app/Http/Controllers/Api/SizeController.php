<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\Size;

class SizeController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true, 
            'data' => Size::all()
        ]);
    }
}
