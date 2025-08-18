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

    public function staff(Request $request)
    {
        $search = $request->input('search');

        $query = User::query()
            ->whereNotIn('role', [1 , 0]); // Loại bỏ user có role = 1 và 0
            
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

        return view('admin.users.staff', compact('users', 'search', 'totalUsers'));
    }

    public function createStaff()
    {
        return view('admin.users.createStaff');
    }
    
    public function storeStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'img_thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'phone' => [
                'required',
                'regex:/^0\d{9}$/'
            ],
            'address' => 'required|string|max:255',
        ], [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email phải có dấu (.) và (@).',
            'email.unique' => 'Email đã tồn tại.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.',
            'address.max' => 'Địa chỉ không được quá 255 ký tự.',
            'address.required' => 'Địa chỉ không được để trống.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp, hoặc chưa nhập.',
            'img_thumbnail.image' => 'Ảnh đại diện phải là file hình ảnh.',
            'img_thumbnail.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg, gif, svg.',
            'img_thumbnail.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'img_thumbnail.required' => 'Ảnh đại diện không được bỏ trống.',
        ]);
        // Xử lý ảnh đại diện
        $imgPath = null;
        if ($request->hasFile('img_thumbnail')) {
            $imgPath = $request->file('img_thumbnail')->store('users', 'public');
        }

        $password = $request->input('password');
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($password),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'role' => 2,
            'img_thumbnail' => $imgPath,
        ]);

        // Gửi mail thông báo
        \Mail::to($user->email)->queue(new \App\Mail\StaffCreatedMail($user, $password));

        return redirect()->route('users.staff')->with('success', 'Thêm nhân viên thành công.');
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
