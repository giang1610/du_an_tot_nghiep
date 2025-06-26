<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Cart;
use App\Mail\OrderPlaced;

class MomoPaymentController extends Controller
{
    public function payViaMomo(Request $request)
    {
        $user = Auth::user();

        $cart = Cart::with('items.variant.product')->where('user_id', $user->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng rỗng'], 400);
        }

        foreach ($cart->items as $item) {
            if ($item->variant->stock < $item->quantity) {
                return response()->json([
                    'message' => 'Không đủ tồn kho cho sản phẩm: ' . $item->variant->product->name,
                ], 400);
            }
        }

        // Tính tiền
        $subtotal = $cart->items->sum(fn($item) => ($item->variant->sale_price ?? $item->variant->price) * $item->quantity);
        $shipping = 15000;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $shipping + $tax;

        // Tạo đơn hàng trạng thái pending
        DB::beginTransaction();

        $order = $user->orders()->create([
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'status' => 'pending',
            'payment_method' => 'momo',
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address ?? $request->shipping_address,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $user->email,
        ]);

        foreach ($cart->items as $item) {
            $order->items()->create([
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->variant->price,
                'sale_price' => $item->variant->sale_price,
                'color_id' => $item->variant->color_id,
                'size_id' => $item->variant->size_id,
            ]);
        }

        DB::commit();

        // Gọi MOMO API
        $url = env('MOMO_API_URL', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $redirectUrl = env('MOMO_REDIRECT_URL');
        $ipnUrl = env('MOMO_IPN_URL');

        if (!$partnerCode || !$accessKey || !$secretKey || !$redirectUrl || !$ipnUrl) {
            return response()->json(['message' => 'Cấu hình MOMO thiếu thông tin'], 500);
        }

        $orderInfo = "Thanh toán MoMo đơn hàng #" . $order->id;
        $amount = (string) $total;
        $orderId = $order->id . '-' . time();
        $requestId = Str::uuid();
        $requestType = "captureWallet";
        $extraData = "";

        $orderType = "momo_atm";

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'orderType' => $orderType,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
            'lang' => 'vi'
        ];

        // $response = Http::post($url, $data)->json();

        // Log::info('MOMO request data', compact('url', 'data'));
        try {
            $response = Http::post($url, $data)->json();
        } catch (\Exception $e) {
            Log::error('MOMO API error', ['error' => $e->getMessage()]);
            // Log::info('MOMO config', compact('partnerCode', 'accessKey', 'secretKey'));
            return response()->json(['message' => 'Không kết nối được MOMO'], 500);
        }

        
        return response()->json([
            'payUrl' => $response['payUrl'] ?? null,
            'message' => $response['message'] ?? 'Yêu cầu thanh toán thất bại',
        ]);
    }

    public function momoNotify(Request $request)
    {
        $data = $request->all();
        $secretKey = env('MOMO_SECRET_KEY');

        $rawHash = "accessKey={$data['accessKey']}&amount={$data['amount']}&extraData={$data['extraData']}&ipnUrl={$data['ipnUrl']}&orderId={$data['orderId']}&orderInfo={$data['orderInfo']}&orderType={$data['orderType']}&partnerCode={$data['partnerCode']}&requestId={$data['requestId']}&responseTime={$data['responseTime']}&resultCode={$data['resultCode']}&message={$data['message']}&payType={$data['payType']}&requestType={$data['requestType']}";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($signature !== $data['signature']) {
            return response()->json(['message' => 'Sai chữ ký'], 403);
        }

        [$orderId] = explode('-', $data['orderId']);
        $order = Order::with(['items.variant'])->find($orderId);

        if (!$order || $order->status !== 'pending') {
            return response()->json(['message' => 'Đơn hàng không hợp lệ'], 404);
        }

        if ((int)$data['resultCode'] === 0) {
            $order->update(['status' => 'paid']);

            foreach ($order->items as $item) {
                $item->variant->decrement('stock', $item->quantity);
            }

            // Clear cart
            Cart::where('user_id', $order->user_id)->first()?->items()->delete();

            // Send email
            Mail::to($order->customer_email)->send(new OrderPlaced($order));
        } else {
            $order->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'MOMO thông báo thành công']);
    }

    public function momoReturn(Request $request)
    {
        return redirect('/thank-you'); // Hoặc frontend page
    }
}
