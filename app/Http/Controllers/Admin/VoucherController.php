<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Http\Requests\VoucherRequest;
class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::query();
        if($request->filled('search')){
            $query->where('name','like','%' . $request->search .'%');
        }
        $vouchers = $query->orderBy('id','desc')->paginate(5);
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
        $data = $request->validated();
        
        // Chuyển đổi datetime
        $data['start_date'] = date('Y-m-d H:i:s', strtotime($data['start_date']));
        $data['end_date'] = date('Y-m-d H:i:s', strtotime($data['end_date']));
        
        // Xử lý triệt để trường không sử dụng
        if ($data['discount_type'] === 'amount') {
            $data['discount_percent'] = null;
            if (empty($data['discount_amount'])) {
                return back()->withErrors(['discount_amount' => 'Số tiền giảm không được để trống'])->withInput();
            }
        } else {
            $data['discount_amount'] = null;
            if (empty($data['discount_percent'])) {
                return back()->withErrors(['discount_percent' => 'Phần trăm giảm không được để trống'])->withInput();
            }
        }

        $voucher->update($data);
        return redirect()->route('vouchers.index')->with('success', 'Cập nhật voucher thành công!');
    }
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('vouchers.index')->with('success', 'Voucher xóa thành công.');
    }
}