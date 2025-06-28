<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Size;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data' => $sizes
        ]);
    }
}
