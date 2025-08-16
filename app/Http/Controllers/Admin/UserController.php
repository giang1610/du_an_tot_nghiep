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

    $query = User::query()
        ->where('role', '!=', 1); // Loại bỏ user có role = 1

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%');
        });
    }

    $users = $query->orderBy('created_at', 'desc')
                   ->paginate(10)
                   ->withQueryString(); // giữ lại search khi phân trang

    $totalUsers = $query->count();

    return view('admin.users.index', compact('users', 'search', 'totalUsers'));
}

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

   
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => [
                        'required',
                        'regex:/^0\d{9}$/'
                    ],
            'address' => 'required|string|max:255',
        ],
            [
                'name.required' => 'Tên không được để trống.',
                'email.required' => 'Email không được để trống.',
                'email.email' => 'Email phải có dấu (.) và (@).',
                'email.unique' => 'Email đã tồn tại.',
                'phone.required' => 'Số điện thoại không được để trống.',
                'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.',
                'address.max' => 'Địa chỉ không được quá 255 ký tự.',
                'address.required' => 'Địa chỉ không được để trống.',
            ]
    );

        $user->update($request->only('name', 'email', 'phone', 'address'));

        return redirect()->route('users.index')->with('success', 'Cập nhập thông tin khách hàng thành công.');
    }
}
