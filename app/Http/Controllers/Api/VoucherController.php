<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Log;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type'); // e.g., 'product'

        $vouchers = Voucher::query();

        if ($type) {
            $vouchers->where('type', $type);
        }

        return response()->json($vouchers->get());
    }

    /**
     * Kiểm tra tính hợp lệ của voucher
     */
    public function checkVoucherValidity($voucher, $user)
    {
        try {
            $now = now();

            if ($voucher->start_date && $now->lt($voucher->start_date)) {
                return ['valid' => false, 'message' => 'Voucher chưa có hiệu lực'];
            }

            if ($voucher->end_date && $now->gt($voucher->end_date)) {
                return ['valid' => false, 'message' => 'Voucher đã hết hạn'];
            }

            if ($voucher->quantity !== null && $voucher->quantity <= 0) {
                return ['valid' => false, 'message' => 'Voucher đã hết lượt sử dụng'];
            }

            if ($voucher->usage_limit) {
                $userUsage = VoucherUser::where('voucher_id', $voucher->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($userUsage && $userUsage->used >= $voucher->usage_limit) {
                    return ['valid' => false, 'message' => 'Bạn đã sử dụng hết lượt cho voucher này'];
                }
            }

            return ['valid' => true];
        } catch (\Exception $e) {
            Log::error('Lỗi kiểm tra voucher: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Lỗi hệ thống khi kiểm tra voucher'];
        }
    }

    public function validateAndApplyVoucher($voucherCode, $user, $subtotal)
    {
        $voucherController = new VoucherController();
        $voucher = Voucher::where('code', $voucherCode)->first();

        if (!$voucher) {
            return ['success' => false, 'message' => 'Voucher không tồn tại'];
        }

        // Kiểm tra điều kiện voucher
        $validityCheck = $voucherController->checkVoucherValidity($voucher, $user);
        if (!$validityCheck['valid']) {
            return ['success' => false, 'message' => $validityCheck['message']];
        }

        // Tính toán giá trị giảm giá
        $discountAmount = $voucherController->calculateVoucherDiscount($voucher, $subtotal);

        return [
            'success' => true,
            'voucher' => $voucher,
            'discount_amount' => $discountAmount
        ];
    }

    public function validateVoucher(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'subtotal' => 'required|numeric|min:0' // Thêm subtotal để tính toán
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để sử dụng voucher'
                ], 401);
            }

            $voucher = Voucher::where('code', $request->code)->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher không tồn tại'
                ], 404);
            }

            $validityCheck = $this->checkVoucherValidity($voucher, $user);
            if (!$validityCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validityCheck['message']
                ], 400);
            }

            // Tính toán giá trị giảm giá thực tế
            $discountValue = $this->calculateVoucherDiscount($voucher, $request->subtotal);

            return response()->json([
                'success' => true,
                'data' => [
                    'voucher' => [
                        'id' => $voucher->id,
                        'code' => $voucher->code,
                        'discount_type' => $voucher->discount_type,
                        'discount_amount' => $voucher->discount_amount,
                        'discount_percent' => $voucher->discount_percent,
                        'max_discount' => $voucher->max_discount,
                    ],
                    'discount_value' => $discountValue
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function calculateDiscountValue($voucher)  //lấy giá trị giảm giá từ voucher
    {
        if ($voucher->discount_type === 'amount') {
            return $voucher->discount_amount;
        } else {
            return $voucher->discount_percent;
        }
    }

    public function calculateVoucherDiscount($voucher, $subtotal) // Tính toán giá trị giảm giá dựa trên loại voucher
    {
        if ($voucher->discount_type === 'amount') {
            return min($voucher->discount_amount, $subtotal);
        } else {
            $discount = $subtotal * ($voucher->discount_percent / 100);

            if (isset($voucher->max_discount)) {
                return min($discount, $voucher->max_discount);
            }

            return $discount;
        }
    }

    public function updateVoucherUsage($voucher, $user)
    {
        // Giảm số lượng voucher
        if ($voucher->quantity !== null) {
            $voucher->decrement('quantity');
        }

        // Cập nhật số lần sử dụng của user
        $voucherUser = VoucherUser::firstOrNew([
            'voucher_id' => $voucher->id,
            'user_id' => $user->id
        ]);

        $voucherUser->used = ($voucherUser->used ?? 0) + 1;
        $voucherUser->save();
    }

    public function getUserVouchers()
    {
        $user = Auth::user();
        $now = Carbon::now();

        $vouchers = Voucher::where(function ($query) use ($now) {
            $query->whereNull('start_date')
                ->orWhere('start_date', '<=', $now);
        })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->where(function ($query) {
                $query->whereNull('quantity')
                    ->orWhere('quantity', '>', 0);
            })
            ->get();

        $validVouchers = $vouchers->filter(function ($voucher) use ($user) {
            if (!$voucher->usage_limit) return true;

            $userUsage = VoucherUser::where('voucher_id', $voucher->id) // Sửa từ Voucher thành VoucherUser
                ->where('user_id', $user->id)
                ->first();

            return !$userUsage || $userUsage->used < $voucher->usage_limit;
        });

        return response()->json([
            'success' => true,
            'data' => $validVouchers
        ]);
    }
    public function apply(Request $request)
    {
        $user = $request->user();
        $code = $request->input('code');
        $subtotal = $request->input('total');

        if (!$code || !$subtotal) {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin'], 422);
        }

        $result = $this->validateAndApplyVoucher($code, $user, $subtotal);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'code' => $result['voucher']->code,
                'type' => $result['voucher']->discount_type === 'percent' ? 'percent' : 'fixed',
                'value' => $result['voucher']->discount_type === 'percent'
                    ? $result['voucher']->discount_percent
                    : $result['voucher']->discount_amount,
                'discount_amount' => $result['discount_amount']
            ]);
        } else {
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        }
    }
    public function suggest(Request $request)
    {
        $total = $request->input('total');
        $userId = auth()->id();

        $now = now();

        $vouchers = DB::table('vouchers')
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->where('quantity', '>', 0)
            ->get()
            ->filter(function ($voucher) use ($userId) {
                // Đếm số lần user đã sử dụng
                $usedCount = DB::table('orders')
                    ->where('user_id', $userId)
                    ->where('voucher_code', $voucher->code)
                    ->count();

                return !$voucher->usage_limit || $usedCount < $voucher->usage_limit;
            })
            ->values();

        return response()->json($vouchers);
    }
}
