<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Http\Requests\VoucherRequest;
class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::all();
        
        return view('admin.voucher.index', compact('vouchers'));
    }

    public function create()
    {
        return view('admin.voucher.create');
    }

    public function store(VoucherRequest $request)
    {
        $data = $request->all();
        // Kiểm tra discount_type chắc chắn là 'amount' hoặc 'percent'
        if (!in_array($data['discount_type'], ['amount', 'percent'])) {
            return back()->withErrors(['discount_type' => 'Loại giảm giá không hợp lệ'])->withInput();
        }

        // Chuyển đổi datetime-local về định dạng phù hợp cho MySQL
        $data['start_date'] = $request->start_date ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null;
        $data['end_date'] = $request->end_date ? date('Y-m-d H:i:s', strtotime($request->end_date)) : null;
        // Xử lý discount_amount và discount_percent
        if ($data['discount_type'] === 'amount') {  
            $data['discount_percent'] = null;
        } else {
            $data['discount_amount'] = null;
        }
 
        Voucher::create($data);
        return redirect()->route('vouchers.index')->with('success', 'Voucher thêm thành công.');
    }
    public function edit(Voucher $voucher)
    {
        $vouchers = Voucher::all();
        return view('admin.voucher.edit', compact('voucher'));
    }
    public function update(VoucherRequest $request, Voucher $voucher)
    {
        $data = $request->all();
        // Kiểm tra discount_type chắc chắn là 'amount' hoặc 'percent'
        if (!in_array($data['discount_type'], ['amount', 'percent'])) {
            return back()->withErrors(['discount_type' => 'Loại giảm giá không hợp lệ'])->withInput();
        }

        // Chuyển đổi datetime-local về định dạng phù hợp cho MySQL
        $data['start_date'] = $request->start_date ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null;
        $data['end_date'] = $request->end_date ? date('Y-m-d H:i:s', strtotime($request->end_date)) : null;
        // Xử lý discount_amount và discount_percent
        if ($data['discount_type'] === 'amount') {  
            $data['discount_percent'] = null;
        } else {
            $data['discount_amount'] = null;
        }

        $voucher->update($data);
        return redirect()->route('vouchers.index')->with('success', 'Voucher cập nhật thành công.');
    }
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('vouchers.index')->with('success', 'Voucher xóa thành công.');
    }
}