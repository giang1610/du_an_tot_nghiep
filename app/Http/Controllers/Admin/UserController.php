<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');

    $query = User::query();

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%');
        });
    }

    $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString(); // giữ lại search khi phân trang
    $totalUsers = $query->count();

    return view('admin.users.index', compact('users', 'search', 'totalUsers'));
}
}
