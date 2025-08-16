<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Mail\OrderPlaced;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\stock;

// RealTime
use App\Events\ProductStockUpdated;
use App\Events\NewOrderCreated;
use App\Events\FailProduct;
use App\Events\newOder;
use App\Events\oderStatus;
use App\Mail\OrderCanceledDueToTimeout;
use App\Models\Voucher;
use App\Models\VoucherUser;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected $orderValidationRules = [
        'voucher_code' => 'nullable|string|exists:vouchers,code',
        'voucher_discount' => 'nullable|numeric|min:0',
        'discount_amount' => 'nullable|numeric|min:0',
        'shipping_address' => 'required|string|max:255',
        'billing_address' => 'nullable|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'notes' => 'nullable|string|max:500',
    ];

    public function store(Request $request)
    {
        // \Log::info('Order request data:', $request->all());
        $validator = Validator::make($request->all(), [
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,processing,completed,cancelled,failed',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
            'payment_method' => 'nullable|string|in:cod,momo,vnpay',
            'payment_status' => 'nullable|string|in:pending,paid,unpaid,failed',
            'shipping_address' => 'required|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Lỗi xác thực',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                if (!$variant || $variant->stock->quantity < $item['quantity']) {
                    throw new \Exception(message: "Không đủ tồn kho cho sản phẩm: {$item['product_variant_id']}");
                }
            }

            $voucherData = null;
            $discountAmount = 0;

            // Xử lý voucher nếu có
            if ($request->voucher_code) {
                $voucherResponse = $this->validateAndApplyVoucher(
                    $request->product_voucher_code,
                    $request->shipping_voucher_code,
                    auth()->user(),
                    $request->subtotal
                );

                if (!$voucherResponse['success']) {
                    return response()->json(['message' => $voucherResponse['message']], 400);
                }

                $voucherData = $voucherResponse['voucher'];
                $discountAmount = $voucherResponse['discount_amount'];
               
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'subtotal' => $request->subtotal,
                'tax' => $request->tax ?? 0,
                'shipping' => $request->shipping ?? 0,
                'voucher_code' => $request->voucher_code ?? null,
                'voucher_discount' => $discountAmount ?? null,
                'voucher_type' => $voucherData->type ?? null,
                'voucher_id' => $voucherData->id ?? null,
                'discount_amount' => $discountAmount ?? null,
                'total' => $request->total - $discountAmount,
                // 'total' => $request->total,
                'status' => $request->status ?? 'pending',
                'payment_method' => $request->payment_method ?? 'cod',
                'payment_status' => $request->payment_status ?? ($request->payment_method === 'cod' ? 'unpaid' : 'pending'),
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes ?? null,
            ]);

            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                ]);

                $variant->stock()->decrement('quantity', $item['quantity']);
            }

            Mail::to($request->customer_email)->queue(new OrderPlaced($order, $order->items()->with(['productVariant.product', 'productVariant.color', 'productVariant.size'])->get()));

            // Cập nhật số lần sử dụng voucher
            if ($request->voucher_code && isset($voucherData)) {
                $this->updateVoucherUsage($voucherData, auth()->user());
            }

            DB::commit();

            return response()->json([
                'message' => 'Tạo đơn hàng thành công',
                'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])

            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Lỗi tạo đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $status = $request->query('status');

        $query = Auth::user()->orders()
            ->with(['user', 'items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'message' => 'Lấy danh sách đơn hàng thành công',
            'data' => $orders
        ]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $order->load(['user', 'items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);

        return response()->json([
            'message' => 'Lấy thông tin đơn hàng thành công',
            'data' => $order
        ]);
    }

    public function updateAddress(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $request->validate([
            'shipping_address' => 'required|string|max:255',
        ]);

        $order->shipping_address = $request->input('shipping_address');
        $order->save();

        return response()->json([
            'message' => 'Cập nhật địa chỉ thành công',
            'data' => $order,
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.'], 400);
        }

        // $order->status = 'cancelled';
        // $order->save();

        // return response()->json(['message' => 'Hủy đơn hàng thành công']);
        DB::beginTransaction();

        try {
            // Hoàn lại tồn kho nếu đã trừ (COD hoặc thanh toán online thành công)
            if ($order->payment_status === 'paid' || $order->payment_method === 'cod') {
                foreach ($order->items as $item) {
                    $variant = $item->productVariant;
                    $variant->stock()->increment('quantity', $item->quantity);
                }
            }

            // Cập nhật trạng thái đơn hàng
            $order->update([
                'status' => 'cancelled',
                'payment_status' => ($order->payment_status === 'paid') ? 'refunded' : 'cancelled',
            ]);
            event(new FailProduct($order->order_number, $order->id));
            broadcast( new oderStatus($order->order_number,$order->id, $order->status ));


            DB::commit();
            return response()->json(['message' => 'Hủy đơn hàng thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi hủy đơn hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi hủy đơn hàng'], 500);
        }
    }


    /**
     * Xử lý thanh toán (COD hoặc MOMO)
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();

        // Nếu client gửi items (buy_now) -> dùng trực tiếp, không cần lấy từ cart
        $requestItems = $request->input('items', null);

        if ($requestItems && is_array($requestItems) && count($requestItems) > 0) {
            // Chuẩn hoá items nếu cần, đảm bảo key product_variant_id tồn tại
            $items = array_map(function ($it) {
                return [
                    'product_variant_id' => $it['product_variant_id'] ?? $it['variant_id'] ?? $it['id'] ?? null,
                    'quantity' => $it['quantity'] ?? 1,
                ];
            }, $requestItems);

            // Nếu không có product_variant_id hợp lệ thì trả lỗi
            foreach ($items as $it) {
                if (empty($it['product_variant_id'])) {
                    return response()->json(['message' => 'Dữ liệu sản phẩm không hợp lệ.'], 400);
                }
            }

            // Merge items vào request để validate tiếp như trước
            $request->merge(['items' => $items]);
        } else {
            // Không có items gửi lên -> lấy từ cart (selected = true)
            $cart = Cart::where('user_id', $user->id)->first();
            if (!$cart) {
                return response()->json(['message' => 'Không tìm thấy giỏ hàng.'], 404);
            }

            $cartItems = CartItem::with('productVariant')
                ->where('cart_id', $cart->id)
                ->where('selected', true)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
            }

            // Tạo array items từ cartItems như cũ
            $items = $cartItems->map(function ($item) {
                return [
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                ];
            })->toArray();

            $request->merge(['items' => $items]);
        }

        // Continue: validate dữ liệu (giữ nguyên validator cũ)
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:cod,vnpay,momo',
            'shipping_address' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'required|email',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
            'discount_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 400);
        }


        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                if (!$variant || !$variant->stock || $variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Không đủ tồn kho cho sản phẩm: {$item['product_variant_id']}");
                }
            }

            // Xử lý voucher
            $voucherData = null;
            $discountAmount = 0;

            if ($request->voucher_code) {
                $voucherResponse = $this->validateAndApplyVoucher(
                    $request->product_voucher_code,
                    $request->shipping_voucher_code,
                    $user,
                    $request->subtotal
                );

                if (!$voucherResponse['success']) {
                    return response()->json(['message' => $voucherResponse['message']], 400);
                }

                $voucherData = $voucherResponse['voucher'];
                $discountAmount = $voucherResponse['discount_amount'];
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'shipping' => $request->shipping,
                'voucher_code' => $request->voucher_code ?? null,
                'voucher_discount' => $discountAmount ?? null,
                'voucher_type' => $voucherData->type ?? null,
                'voucher_id' => $voucherData->id ?? null,
                'discount_amount' => $discountAmount ?? null,
                'total' => $request->total - $discountAmount,
                // 'total' => $request->total,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            broadcast(new newOder($order));
            // Tạo các order items

            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'price' => $variant->sale_price ?? $variant->price,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                ]);

                // Trừ kho nếu thanh toán COD
                if ($request->payment_method === 'cod') {
                    $variant->stock->decrement('quantity', $item['quantity']);

                    // Broadcast cập nhật tồn kho
                    broadcast(new ProductStockUpdated(
                        $variant->id,
                        $variant->fresh()->stock->quantity
                    ));
                }
            }
            if ($request->voucher_code && isset($voucherData)) {
                $this->updateVoucherUsage($voucherData, $user);
            }

            // Gửi event
            event(new NewOrderCreated($order->order_number, $order->id));

            DB::commit();

            // Xử lý theo phương thức thanh toán
            switch ($request->payment_method) {
                case 'momo':
                    $momoResponse = $this->initiateMomoPayment($order, $order->total);
                    return response()->json([
                        'message' => 'Đã khởi tạo thanh toán MOMO',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => $momoResponse['payUrl'],
                            'order' => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']),
                        ]
                    ]);

                case 'cod':
                    // Gửi email xác nhận đơn hàng COD
                    Mail::to($request->customer_email)->queue(new OrderPlaced($order, $user));
                    return response()->json([
                        'message' => 'Đặt hàng COD thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => null,
                            'order' => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']),
                        ]
                    ]);

                case 'vnpay':
                    $vnpResponse = $this->initiateVnpayPayment($order);
                    return response()->json([
                        'message' => 'Đã khởi tạo thanh toán VNPay',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => $vnpResponse['payment_url'],
                            'order' => $order->load(['items.variant.product', 'items.variant.color', 'items.variant.size']),
                        ]
                    ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage());

            return response()->json([
                'message' => 'Không thể tạo đơn hàng. Vì kho không đủ số lượng sản phẩm.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }




    /**
     * Xử lý thanh toán COD
     */
    protected function processCodPayment($user, $request, $cart, $totals)
    {
        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho
            foreach ($cart->items as $item) {
                if ($item->variant->stock->quantity < $item->quantity) {
                    throw new \Exception("Không đủ tồn kho cho sản phẩm: {$item->variant->product->name}");
                }
            }

            // Tạo đơn hàng
            $order = $user->orders()->create([
                'subtotal' => $totals['subtotal'],
                'shipping' => $totals['shipping'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'status' => 'processing',
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);

            // Tạo items đơn hàng và cập nhật tồn kho
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

                // Cập nhật tồn kho
                $item->variant->stock()->decrement('quantity', $item->quantity);
            }

            // Xóa các sản phẩm đã chọn khỏi giỏ hàng
            $cart->items()->where('selected', true)->delete();

            // Gửi email xác nhận
            Mail::to($user->email)->queue(new OrderPlaced($order, $user));

            DB::commit();

            // Nếu là MOMO, trả về link thanh toán
            switch ($request->payment_method) {
                case 'momo':
                    $momoResponse = $this->initiateMomoPayment($order, $order->total);
                    return response()->json([
                        'message' => 'Đã khởi tạo thanh toán MOMO',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => $momoResponse['payUrl'],
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);

                case 'cod':
                    // Gửi email xác nhận cho COD

                    Mail::to($request->customer_email)->queue(new OrderPlaced($order, $user));
                    return response()->json([
                        'message' => 'Đặt hàng COD thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'payment_url' => null,
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);
                default:
                    return response()->json([
                        'message' => 'Đặt hàng thành công',
                        'data' => [
                            'order_id' => $order->id,
                            'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                        ]
                    ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi tạo đơn hàng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Không thể tạo đơn hàng ',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Xử lý thanh toán VNPay
     */
   public function processVnpayPayment(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // Validate cơ bản và cho phép items nếu frontend gửi (buy-now)
            $validated = $request->validate([
                'shipping_address' => 'required|string',
                'billing_address' => 'nullable|string',
                'customer_phone' => 'required|string',
                'notes' => 'nullable|string',
                'items' => 'nullable|array',
                'items.*.product_variant_id' => 'required_with:items|integer',
                'items.*.quantity' => 'required_with:items|integer|min:1',
                'product_voucher_code' => 'nullable|string',
                'shipping_voucher_code' => 'nullable|string',
                'subtotal' => 'nullable|numeric',
            ]);
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    $variantId = $item['product_variant_id'] ?? null;
                    $reqQty    = $item['quantity'] ?? 0;

                    if (!$variantId || $reqQty <= 0) {
                        continue; // Bỏ qua nếu dữ liệu không hợp lệ
                    }

                    // Lấy tồn kho thực tế từ DB
                    $stock = Stock::where('product_variant_id', $variantId)->first();

                    if (!$stock) {
                        return response()->json([
                            'message' => " sảm phẩm không tồn tại trong kho hoặc đã xóa vui lòng mua sản phẩm khác."
                        ], 400);
                    }

                    if ($reqQty > $stock->quantity) {
                        return response()->json([
                            'message' => "Sản phẩm trong kho không đủ số lượng."
                        ], 400);
                    }
                }
            }
            

            // Nếu frontend gửi items => xử lý buy-now
            if ($request->has('items') && is_array($request->items) && count($request->items) > 0 ) {
                $itemsFromRequest = collect($request->items)->map(function ($it) {
                    return (object) [
                        'product_variant_id' => isset($it['product_variant_id']) ? (int)$it['product_variant_id'] : (isset($it['variant_id']) ? (int)$it['variant_id'] : null),
                        'quantity' => isset($it['quantity']) ? (int)$it['quantity'] : 1,
                    ];
                })->filter(function ($it) {
                    return !empty($it->product_variant_id);
                })->values();

                if ($itemsFromRequest->isEmpty()) {
                    return response()->json(['message' => 'Không có sản phẩm hợp lệ trong payload.'], 400);
                }

                // Lấy variants từ DB để tính subtotal/giá thực tế
                $variantIds = $itemsFromRequest->pluck('product_variant_id')->toArray();
                $variants = \App\Models\ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

                $subtotal = 0;
                foreach ($itemsFromRequest as $it) {
                    $variant = $variants->get($it->product_variant_id);
                    if (!$variant) {
                        return response()->json(['message' => "Biến thể (id={$it->product_variant_id}) không tồn tại."], 400);
                    }
                    $price = $variant->sale_price ?? $variant->price;
                    $subtotal += $price * $it->quantity;
                }

                $cartItems = $itemsFromRequest; // dùng để tạo OrderItem
            } else {
                // Fallback: lấy from Cart DB (chỉ các item selected = true)
                $cart = Cart::with(['items' => function ($q) {
                    $q->where('selected', true);
                }, 'items.variant'])->where('user_id', $user->id)->first();

                if (!$cart || $cart->items->isEmpty()) {
                    return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
                }

                $subtotal = 0;
                foreach ($cart->items as $item) {
                    if (!$item->variant) {
                        throw new \Exception("Sản phẩm không tồn tại hoặc bị lỗi biến thể.");
                    }
                    $subtotal += ($item->variant->sale_price ?? $item->variant->price) * $item->quantity;
                }

                $cartItems = $cart->items;
            }

            // Xử lý voucher dựa trên subtotal tính bởi server (bảo mật)
            $voucherData = null;
            $discountAmount = 0;
            $shipping = 20000;
            if (!empty($request->product_voucher_code) || !empty($request->shipping_voucher_code)) {
                $voucherResponse = $this->validateAndApplyVoucher(
                    $request->product_voucher_code,
                    $request->shipping_voucher_code,
                    $user,
                    $subtotal,
                    // $discount,
                );

                if (!$voucherResponse['success']) {
                    return response()->json(['message' => $voucherResponse['message']], 400);
                }
                 $shipping = 20000;

                // $voucherData = $voucherResponse['voucher'];
                $discountAmount = $voucherResponse['discount_amount'];
               $shippingDiscount = $voucherResponse['discount_amount_shipping'] ?? 0;
                $shipping = max(0, $shipping - $shippingDiscount);
                

            }

            $tax = $subtotal * 0.1;
            $totalBeforeDiscounts = $subtotal + $shipping + $tax;
            $total = $totalBeforeDiscounts - ($discountAmount );
            $total = max(0, $total);

            // Tạo Order
            $order = $user->orders()->create([
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'voucher_code' => $request->voucher_code,
                'voucher_discount' => $discountAmount,
                'voucher_type' => $voucherData->type ?? null,
                'voucher_id' => $voucherData->id ?? null,
                'discount_amount' => $discountAmount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending',
                'payment_method' => 'vnpay',
                'payment_status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);


            // Tạo OrderItem dựa trên $cartItems (hoạt động cho stdClass hoặc Eloquent)
            $variantIdsForOrder = [];
            foreach ($cartItems as $ci) {
                $variantIdsForOrder[] = $ci->product_variant_id ?? $ci->product_variant_id ?? null;
            }
            $variantIdsForOrder = array_filter($variantIdsForOrder);
            $variantsMap = \App\Models\ProductVariant::whereIn('id', $variantIdsForOrder)->get()->keyBy('id');

            foreach ($cartItems as $item) {
                $productVariantId = $item->product_variant_id ?? ($item->product_variant_id ?? null);
                if (!$productVariantId) continue;

                $variant = $variantsMap->get($productVariantId);
                if (!$variant) {
                    throw new \Exception("Biến thể (id={$productVariantId}) không tồn tại khi tạo đơn.");
                }

                $quantity = $item->quantity ?? 1;
                $price = $variant->price;
                $salePrice = $variant->sale_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $productVariantId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'color_id' => $variant->color_id ?? null,
                    'size_id' => $variant->size_id ?? null,
                ]);
            }

            // Gọi VNPay để khởi tạo link
            $vnpResponse = $this->initiateVnpayPayment($order);

            DB::commit();

            return response()->json([
                'message' => 'Đã khởi tạo thanh toán VNPay',
                'data' => [
                    'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                    'payment_url' => $vnpResponse['payment_url'] ?? null,
                    'order_id' => $order->id,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khởi tạo VNPay: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

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
            $vnp_TxnRef = $order->id . '_' . time();
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
     * Xử lý trả về từ VNPay
     */
    public function vnpayReturn(Request $request)
    {
        try {
            $inputData = $request->all();
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
            $vnp_traVe = env('VNP_TRA_VE');

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
            $orderParts = explode('_', $inputData['vnp_TxnRef'] ?? '');
            $orderId = $orderParts[0] ?? null;

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
                                'status' => 'pending',
                                'transaction_id' => $inputData['vnp_TransactionNo'] ?? null,
                            ]);
                            broadcast(new  newOder($order));
                            event(new NewOrderCreated($order->order_number, $order->id));

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
                            Mail::to($order->customer_email)->queue(new OrderPlaced($order, $order->user));

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Lỗi cập nhật đơn hàng sau thanh toán VNPay: ' . $e->getMessage());
                            return response()->json(['message' => 'Lỗi xử lý đơn hàng'], 500);
                        }
                    }


                    return redirect($vnp_traVe . '?' . http_build_query(data: [
                        'message' => 'Thanh toán thành công',
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'transaction_id' => $inputData['vnp_TransactionNo'] ?? null,
                    ]));
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

    public function checkReceivedProduct(Request $request)  // Kiểm tra xem người dùng đã nhận sản phẩm chưa
    {
        $productId = $request->query('product_id');
        $user = auth()->user();

        $hasReceived = OrderItem::where('product_variant_id', $productId)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'delivered');
            })->exists();

        return response()->json(['received' => $hasReceived]);
    }

    /**
     * Xử lý hoàn trả đơn hàng
     */
    public function returnOrder(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
            'return_items' => 'required|array|min:1',
            'return_items.*.order_item_id' => 'required|exists:order_items,id',
            'return_items.*.quantity' => 'required|integer|min:1',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra quyền (chỉ admin hoặc user sở hữu đơn hàng)
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        // Chỉ cho phép hoàn trả đơn hàng đã giao
        if ($order->status !== 'completed') {
            return response()->json(['message' => 'Chỉ có thể hoàn trả đơn hàng đã giao'], 400);
        }

        DB::beginTransaction();

        try {
            // Cập nhật số lượng hoàn trả và lý do
            foreach ($request->return_items as $returnItem) {
                $orderItem = OrderItem::find($returnItem['order_item_id']);

                // Kiểm tra số lượng hợp lệ
                if ($returnItem['quantity'] > $orderItem->quantity) {
                    throw new \Exception("Số lượng hoàn trả vượt quá số lượng đã mua");
                }

                // Hoàn lại tồn kho
                $orderItem->productVariant->stock()->increment('quantity', $returnItem['quantity']);

                // Đánh dấu sản phẩm đã hoàn trả
                $orderItem->update([
                    'returned_quantity' => $returnItem['quantity'],
                    'return_reason' => $request->reason,
                ]);
            }

            // Cập nhật trạng thái đơn hàng
            $order->update([
                'status' => 'returned',
                'refund_amount' => $request->refund_amount ?? $order->total,
            ]);

            // Nếu cần hoàn tiền (MOMO/VNPay)
            if ($order->payment_method !== 'cod' && $order->payment_status === 'paid') {
                $refundResponse = $this->refundPayment($order, $request->refund_amount);
                if (!$refundResponse['success']) {
                    throw new \Exception('Hoàn tiền thất bại: ' . $refundResponse['message']);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Yêu cầu hoàn trả thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi hoàn trả đơn hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xử lý hoàn trả'], 500);
        }
    }

    public function confirmReceived($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Chỉ cho phép xác nhận khi trạng thái là 'shipped'
        if ($order->status !== 'shipped') {
            return response()->json(['message' => 'Không thể xác nhận đơn hàng này'], 400);
        }

        $order->status = 'completed';
        $order->completed_at = now();

        // Nếu phương thức thanh toán là COD => khi nhận hàng => đã thanh toán
        if ($order->payment_method === 'cod') {
            $order->payment_status = 'paid';
        }

        $order->save();

        return response()->json(['message' => 'Đã xác nhận nhận hàng thành công']);
    }

    // Yêu cầu trả hàng
    public function requestReturn(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
            'media' => 'nullable', // Có thể là ảnh hoặc video
            'media.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:10240', // Tối đa 10MB
        ]);

        if (!in_array($order->status, ['shipped', 'completed'])) {
            return response()->json(['message' => 'Chỉ có thể yêu cầu hoàn hàng khi đơn đã giao hàng hoặc hoàn thành'], 400);
        }

        // Nếu là completed thì kiểm tra thời gian hoàn thành
        if ($order->status === 'completed') {
            $completedAt = $order->completed_at ?? $order->updated_at ?? $order->created_at;
            if (now()->diffInDays(\Carbon\Carbon::parse($completedAt)) > 7) {
                return response()->json(['message' => 'Chỉ có thể yêu cầu hoàn đơn trong vòng 7 ngày sau khi hoàn thành'], 400);
            }
        }

        $order->status = 'return_requested';
        // realTime Hoàn Hàng
        broadcast( new oderStatus($order->order_number,$order->id, $order->status ));
        $order->return_reason = $request->input('reason');
        $order->return_requested_at = now();

        // Xử lý nhiều file upload
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaPaths[] = $file->store('returns', 'public');
            }
            $order->return_media = json_encode($mediaPaths);
        }

        $order->save();

        return response()->json(['message' => 'Yêu cầu hoàn hàng đã được gửi!']);
    }

    /**
     * Kiểm tra và áp dụng voucher
     */
 protected function validateAndApplyVoucher($voucherCode, $voucher2, $user, $amountContext, $appliesTo = 'product')
{
    

    try {
        $voucher = Voucher::where('code', $voucherCode)->first();
        $voucherShipping = Voucher::where('code', $voucher2)->first();

        // Nếu cả 2 đều không tồn tại
        if (!$voucher && !$voucherShipping) {
            return ['success' => false, 'message' => 'Voucher không tồn tại'];
        }

        $now = now();

        // ==== Xử lý voucher sản phẩm ====
        $discountAmount = 0;
        if ($voucher) {
            if (isset($voucher->applies_to) && $voucher->applies_to !== 'all' && $voucher->applies_to !== $appliesTo) {
                return ['success' => false, 'message' => 'Voucher không áp dụng cho mục này'];
            }

            if ($voucher->start_date && $now->lt($voucher->start_date)) {
                return ['success' => false, 'message' => 'Voucher chưa có hiệu lực'];
            }

            if ($voucher->end_date && $now->gt($voucher->end_date)) {
                return ['success' => false, 'message' => 'Voucher đã hết hạn'];
            }

            if ($voucher->quantity !== null && $voucher->quantity <= 0) {
                return ['success' => false, 'message' => 'Voucher đã hết lượt sử dụng'];
            }
            $userId = is_object($user) ? $user->id : $user;


            if ($voucher->usage_limit) {
                $userUsage = VoucherUser::where('voucher_id', $voucher->id)
                    ->where('user_id', $userId)
                    ->first();

                if ($userUsage && $userUsage->used >= $voucher->usage_limit) {
                    return ['success' => false, 'message' => 'Bạn đã sử dụng hết lượt cho voucher này'];
                }
            }

            if ($voucher->discount_type === 'amount') {
                $discountAmount = min($voucher->discount_amount, $amountContext);
            } elseif ($voucher->discount_type === 'percent') {
                $discount = $amountContext * ($voucher->discount_percent / 100);
                $discountAmount = isset($voucher->max_discount) ? min($discount, $voucher->max_discount) : $discount;
            }
        }

        // ==== Xử lý voucher shipping ====
        $discountAmountShipping = 0;
        if ($voucherShipping) {
            if (isset($voucherShipping->applies_to) && $voucherShipping->applies_to !== 'all' && $voucherShipping->applies_to !== 'shipping') {
                return ['success' => false, 'message' => 'Voucher không áp dụng cho phí vận chuyển'];
            }

            if ($voucherShipping->start_date && $now->lt($voucherShipping->start_date)) {
                return ['success' => false, 'message' => 'Voucher phí vận chuyển chưa có hiệu lực'];
            }

            if ($voucherShipping->end_date && $now->gt($voucherShipping->end_date)) {
                return ['success' => false, 'message' => 'Voucher phí vận chuyển đã hết hạn'];
            }

            if ($voucherShipping->quantity !== null && $voucherShipping->quantity <= 0) {
                return ['success' => false, 'message' => 'Voucher phí vận chuyển đã hết lượt sử dụng'];
            }

            if ($voucherShipping->usage_limit) {
                $userUsage = VoucherUser::where('voucher_id', $voucherShipping->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($userUsage && $userUsage->used >= $voucherShipping->usage_limit) {
                    return ['success' => false, 'message' => 'Bạn đã sử dụng hết lượt cho voucher phí vận chuyển'];
                }
            }
           

            if ($voucherShipping->discount_type === 'amount') {
               
                $discountAmountShipping = min($voucherShipping->discount_amount, $amountContext);
            } elseif ($voucherShipping->discount_type === 'percent') {
                $discount = $amountContext * ($voucherShipping->discount_percent / 100);
                $discountAmountShipping = isset($voucherShipping->max_discount) ? min($discount, $voucherShipping->max_discount) : $discount;
            }
        }

        return [
            'success' => true,
            'voucher' => $voucher,
            'voucher_shipping' => $voucherShipping,
            'discount_amount' => $discountAmount,
            'discount_amount_shipping' => $discountAmountShipping
        ];

    } catch (\Exception $e) {
        Log::error('Voucher validation error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Lỗi khi kiểm tra voucher'];
    }
}

    /**
     * Tính toán giá trị giảm giá từ voucher
     */
    // protected function calculateVoucherDiscount($voucher, $subtotal)
    // {
    //     if ($voucher->discount_type === 'amount') {
    //         return min($voucher->discount_amount, $subtotal);
    //     } elseif ($voucher->discount_type === 'percent') {
    //         $discount = $subtotal * ($voucher->discount_percent / 100);
    //         return isset($voucher->max_discount) ? min($discount, $voucher->max_discount) : $discount;
    //     }
    //     return 0;
    // }


    /**
     * Cập nhật số lần sử dụng voucher
     */
    protected function updateVoucherUsage($voucher, $user)
    {
        DB::transaction(function () use ($voucher, $user) {
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
        });
    }
}