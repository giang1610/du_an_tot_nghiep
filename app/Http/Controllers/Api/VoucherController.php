<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VoucherController extends Controller
{
   public function validateVoucher(Request $request)
{
    try {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
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

        return response()->json([
            'success' => true,
            'data' => [
                'voucher' => $voucher,
                'discount_value' => $this->calculateDiscountValue($voucher)
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server Error',
            'error' => $e->getMessage() // Chỉ để debug, không show ra production
        ], 500);
    }
}

    private function checkVoucherValidity($voucher, $user)
    {
        $now = Carbon::now();

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
            $userUsage = VoucherUser::where('voucher_id', $voucher->id) // Sửa từ Voucher thành VoucherUser
                          ->where('user_id', $user->id)
                          ->first();

            if ($userUsage && $userUsage->used >= $voucher->usage_limit) {
                return ['valid' => false, 'message' => 'Bạn đã sử dụng hết lượt cho voucher này'];
            }
        }

        return ['valid' => true];
    }

    private function calculateDiscountValue($voucher)
    {
        if ($voucher->discount_type === 'amount') {
            return $voucher->discount_amount;
        } else {
            return $voucher->discount_percent;
        }
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
}