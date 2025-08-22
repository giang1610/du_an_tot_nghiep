<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;

class AdminController extends Controller
{
    public function index()
    {
      
   
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return Auth::user()->role == 1
            ? redirect()->route('admin.dashboard')
            : redirect()->route('orders.index');
    }
    
}
