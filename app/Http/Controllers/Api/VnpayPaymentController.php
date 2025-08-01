<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderCanceledDueToTimeout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use App\Mail\OrderPlaced;
use App\Models\OrderItem;
use Auth;

class VnpayPaymentController extends Controller
{
    /**
     * Khởi tạo thanh toán VNPay
     */
    public function processVnpayPayment(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // Validate đầu vào
            $validated = $request->validate([
                'shipping_address' => 'required|string',
                'billing_address' => 'nullable|string',
                'customer_phone' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            // Lấy giỏ hàng và chỉ lấy item selected = 1
            $cart = Cart::with(['items' => function ($q) {
                $q->where('selected', true);
            }, 'items.variant'])->where('user_id', $user->id)->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
            }

            // Tính tổng
            $subtotal = 0;
            foreach ($cart->items as $item) {
                $subtotal += ($item->variant->sale_price ?? $item->variant->price) * $item->quantity;
            }

            $shipping = 20000;
            $tax = $subtotal * 0.1;
            $total = $subtotal + $shipping + $tax;

            // Xử lý voucher
            $voucherData = null;
            $discountAmount = 0;

            if ($request->voucher_code) {
                $voucherResponse = $this->validateAndApplyVoucher(
                    $request->voucher_code,
                    $user,
                    $request->subtotal
                );

                if (!$voucherResponse['success']) {
                    return response()->json(['message' => $voucherResponse['message']], 400);
                }

                $voucherData = $voucherResponse['voucher'];
                $discountAmount = $voucherResponse['discount_amount'];
            }

            $order = $user->orders()->create([
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'voucher_code' => $request->voucher_code,
                'voucher_discount' => $discountAmount,
                'voucher_type' => $voucherData->type ?? null,
                'voucher_id' => $voucherData->id ?? null,
                'discount_amount' => $discountAmount,
                'total' => $request->$total - $discountAmount,
                'tax' => $tax,
                // 'total' => $total,
                'status' => 'pending',
                'payment_method' => 'vnpay',
                'payment_status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->variant->price,
                    'sale_price' => $item->variant->sale_price,
                    'color_id' => $item->variant->color_id,
                    'size_id' => $item->variant->size_id,
                ]);
            }

            // Gọi API VNPay
            $vnpResponse = $this->initiateVnpayPayment(order: $order);

            DB::commit();

            return response()->json([
                'message' => 'Đã khởi tạo thanh toán VNPay',
                'data' => [
                    'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                    'payment_url' => $vnpResponse['payment_url'],
                    'order_id' => $order->id,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khởi tạo VNPay: ' . $e->getMessage());

            return response()->json([
                'message' => 'Lỗi khởi tạo thanh toán VNPay',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Khởi tạo thanh toán VNPay
     */
    protected function initiateVnpayPayment($order)
    {
        try {
            $vnp_TmnCode = env('VNP_TMN_CODE'); // Mã website do VNPay cấp
            $vnp_HashSecret = env('VNP_HASH_SECRET'); // Chuỗi bí mật
            $vnp_Url = env('VNP_URL'); // URL VNPay
            $vnp_ReturnUrl = env('VNP_RETURN_URL'); // URL callback sau thanh toán


            // Tạo mã đơn hàng duy nhất
            // $vnp_TxnRef = $order->id . '_' . time();
            $vnp_TxnRef = $order->id;
            $vnp_OrderInfo = 'Thanh toan hoa don ' . $order->order_number;
            $vnp_OrderType = 'other';
            $vnp_Amount = $order->total * 100; // Nhân 100 theo yêu cầu VNPay
            $vnp_Locale = 'vn';
            $vnp_BankCode = 'VNBANK'; // Có thể để rỗng nếu không ép chọn ngân hàng
            $vnp_IpAddr = request()->ip(); // IP khách hàng

            // Danh sách các tham số gửi sang VNPay
            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => Carbon::now('Asia/Ho_Chi_Minh')->format('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_ReturnUrl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            // Optional fields
            if (!empty($vnp_BankCode)) {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            } else {
                // Bỏ qua mã ngân hàng và để VNPAY tự động chọn
                unset($inputData['vnp_BankCode']);
            }

            // Sort parameters by key
            ksort($inputData);

            // Build the query string and hashdata for signature
            $queryString = "";
            $hashdata = "";
            $i = 0;
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $queryString .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            // Remove trailing '&' from the query string
            $queryString = rtrim($queryString, '&');


            // Now calculate the secure hash using the secret key
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

            // Append the secure hash to the query string
            $vnp_Url .= "?" . $queryString . "&vnp_SecureHash=" . $vnpSecureHash;
            return [
                'payment_url' => $vnp_Url,
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi tạo link thanh toán VNPay: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }



    /**
     * Xử lý IPN từ VNPay
     */
    public function vnpayIpn(Request $request)
    {
        try {
            $inputData = $request->all();
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

            // Bỏ các tham số không dùng để tạo chữ ký
            unset($inputData['vnp_SecureHash']);
            unset($inputData['vnp_SecureHashType']);

            // Sắp xếp dữ liệu theo key
            ksort($inputData);

            // Tạo chuỗi hashdata giống như lúc gửi đi
            $hashData = '';
            $i = 0;
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }

            // So sánh chữ ký
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
            if ($secureHash !== $vnp_SecureHash) {
                return response()->json(['RspCode' => '97', 'Message' => 'Sai Checksum']);
            }

            // Tách ID đơn hàng từ vnp_TxnRef
            $orderParts = explode('_', $inputData['vnp_TxnRef'] ?? '');
            $orderId = $orderParts[0] ?? null;

            if (!$orderId || !is_numeric($orderId)) {
                return response()->json(['RspCode' => '01', 'Message' => 'Không tìm thấy đơn hàng']);
            }

            $order = Order::with(['items.productVariant.stock'])->find($orderId);
            if (!$order) {
                return response()->json(['RspCode' => '01', 'Message' => 'Không tìm thấy đơn hàng']);
            }

            // Kiểm tra trạng thái giao dịch
            if ($inputData['vnp_ResponseCode'] === '00') {
                DB::beginTransaction();
                try {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'transaction_id' => $inputData['vnp_TransactionNo'],
                    ]);

                    foreach ($order->items as $item) {
                        $item->productVariant->stock()->decrement('quantity', $item->quantity);
                    }

                    // Xóa item đã mua khỏi giỏ hàng
                    $cart = Cart::where('user_id', $order->user_id)->first();
                    if ($cart) {
                        foreach ($order->items as $item) {
                            CartItem::where('cart_id', $cart->id)
                                ->where('product_variant_id', $item->product_variant_id)
                                ->where('selected', true)
                                ->delete();
                        }
                    }

                    // Gửi mail xác nhận
                    Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));

                    DB::commit();
                    return response()->json(['RspCode' => '00', 'Message' => 'Thanh toán thành công']);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Lỗi xử lý IPN VNPay: ' . $e->getMessage(), ['exception' => $e]);
                    return response()->json(['RspCode' => '99', 'Message' => 'Lỗi xử lý giao dịch']);
                }
            }

            // Giao dịch không thành công
            return response()->json(['RspCode' => '02', 'Message' => 'Giao dịch không thành công']);
        } catch (\Exception $e) {
            Log::error('Lỗi hệ thống IPN VNPay: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['RspCode' => '99', 'Message' => 'Lỗi hệ thống']);
        }
    }

    /**
     * Xử lý trả về từ VNPay
     */
    public function vnpayReturn(Request $request)
    {
        try {
            $inputData = $request->all();
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

            // Bỏ các trường không dùng để tạo chữ ký
            unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

            // Sắp xếp các tham số theo thứ tự key
            ksort($inputData);

            // Tạo chuỗi hashData giống như khi gửi
            $hashData = '';
            foreach ($inputData as $key => $value) {
                $hashData .= urlencode($key) . "=" . urlencode($value) . '&';
            }
            $hashData = rtrim($hashData, '&');

            // Tính toán lại chữ ký
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            // Tách orderId từ vnp_TxnRef
            // $orderParts = explode('_', $inputData['vnp_TxnRef'] ?? '');
            // $orderId = $orderParts[0] ?? null;
            $orderId = $inputData['vnp_TxnRef'] ?? null;

            if (!$orderId || !is_numeric($orderId)) {
                return response()->json(['message' => 'Không tìm thấy đơn hàng'], 400);
            }

            $order = Order::with('items.productVariant.stock')->find($orderId);
            if (!$order) {
                return response()->json(['message' => 'Đơn hàng không tồn tại'], 400);
            }

            // Kiểm tra chữ ký và xử lý nếu hợp lệ
            if ($secureHash === $vnp_SecureHash) {
                if ($inputData['vnp_ResponseCode'] === '00') {
                    // Kiểm tra nếu chưa thanh toán thì mới cập nhật
                    if ($order->payment_status !== 'paid') {
                        DB::beginTransaction();
                        try {
                            $order->update([
                                'payment_status' => 'paid',
                                'status' => 'processing',
                                'transaction_id' => $inputData['vnp_TransactionNo'] ?? null,
                            ]);

                            // Giảm số lượng tồn kho
                            foreach ($order->items as $item) {
                                $item->productVariant->stock->decrement('quantity', $item->quantity);
                            }

                            // Xoá sản phẩm đã mua khỏi giỏ hàng
                            $cart = Cart::where('user_id', $order->user_id)->first();
                            if ($cart) {
                                foreach ($order->items as $item) {
                                    CartItem::where('cart_id', $cart->id)
                                        ->where('product_variant_id', $item->product_variant_id)
                                        ->where('selected', true)
                                        ->delete();
                                }
                            }

                            // Gửi mail
                            // Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Lỗi cập nhật đơn hàng sau thanh toán VNPay: ' . $e->getMessage());
                            return response()->json(['message' => 'Lỗi xử lý đơn hàng'], 500);
                        }
                    }


                    return redirect('http://localhost:3000/vnpay-return?' . http_build_query(data: [
                        'message' => 'Thanh toán thành công',
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'transaction_id' => $inputData['vnp_TransactionNo'] ?? null,
                    ]));
                } else {
                }
            } else {
                return response()->json(['message' => 'Sai checksum'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi xử lý return URL VNPay: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi hệ thống'], 500);
        }
    }
    /**
     * Cho phép người dùng tiếp tục thanh toán VNPay nếu đơn hàng chưa được thanh toán
     */
    public function retryVnpayPayment(Request $request)
    {
        $user = Auth::user();
        $orderId = $request->input('order_id');
        // Giới hạn thời gian có thể thanh toán lại là 20 phút
        $timeoutMinutes = 20;

        // Kiểm tra đơn hàng
        $order = Order::where('id', $orderId)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ($order->payment_method !== 'vnpay') {
            return response()->json(['message' => 'Đơn hàng không dùng cổng thanh toán VNPay'], 400);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Đơn hàng đã được thanh toán thành công'], 400);
        }

        // Kiểm tra thời gian quá hạn
        if ($order->created_at->diffInMinutes(now()) > $timeoutMinutes && $order->payment_status === 'pending' && $order->payment_method === 'vnpay') {
            DB::beginTransaction();
            try {
                if (method_exists($order, 'items')) {
                    $order->items()->delete();
                }

                $order->delete();

                // Gửi email sau khi xóa
                if ($order->customer_email) {
                    Mail::to($order->customer_email)->queue(new OrderCanceledDueToTimeout($order));
                }

                DB::commit();

                return response()->json([
                    'message' => 'Đơn hàng đã quá thời gian thanh toán lại (20 phút) và đã bị hủy.'
                ], 410);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Lỗi khi xóa đơn hàng VNPay quá hạn: ' . $e->getMessage());
                return response()->json(['message' => 'Không thể hủy đơn hàng. Vui lòng thử lại sau.'], 500);
            }
        }

        try {
            // Gọi lại hàm tạo link thanh toán VNPay
            $vnpResponse = $this->initiateVnpayPayment(order: $order);

            return response()->json([
                'message' => 'Tạo lại liên kết thanh toán thành công',
                'data' => [
                    'payment_url' => $vnpResponse['payment_url'],
                    'order_id' => $order->id,
                    'total' => $order->total
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo lại link VNPay: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi hệ thống khi tạo lại link thanh toán'], 500);
        }
    }

}
