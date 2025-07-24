<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Mail\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\CartItem;
use App\Events\NewOrderCreated;
use App\Services\Payment\PaymentServiceFactory;
use Str;

class OrderController extends Controller
{
    // Validation rules
    protected $orderValidationRules = [
        'shipping_address' => 'required|string|max:255',
        'billing_address' => 'nullable|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'notes' => 'nullable|string|max:500',
    ];

    /**
     * Create a new order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,processing,completed,cancelled,failed',
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
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Validate stock
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                
                if (!$variant) {
                    throw new \Exception("Product variant not found: {$item['product_variant_id']}");
                }
                
                if (!$variant->stock || $variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$item['product_variant_id']}");
                }
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'subtotal' => $request->subtotal,
                'tax' => $request->tax ?? 0,
                'shipping' => $request->shipping ?? 0,
                'total' => $request->total,
                'status' => $request->status ?? 'pending',
                'payment_method' => $request->payment_method ?? 'cod',
                'payment_status' => $request->payment_status ?? ($request->payment_method === 'cod' ? 'unpaid' : 'pending'),
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes ?? null,
            ]);

            // Create order items and update stock
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                ]);

                $variant->stock()->decrement('quantity', $item['quantity']);
            }

            // Send confirmation email
            Mail::to($request->customer_email)->queue(new OrderPlaced($order, auth()->user()));

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

 
    public function checkout(Request $request)
    {
        $user = auth()->user();

         // Lấy cart của user
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Không tìm thấy giỏ hàng.'], 404);
        }

        // Lấy các sản phẩm được chọn để mua (selected = 1)
        $cartItems = CartItem::with('productVariant')
            ->where('cart_id', $cart->id)
            ->where('selected', true)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn để thanh toán.'], 400);
        }

        // Tạo mảng items cho đơn hàng từ cartItems
        $items = $cartItems->map(function($item) {
            return [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Gán lại vào $request để dùng chung validate và xử lý phía dưới
        $request->merge(['items' => $items]);

        // Validate request
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:cod,momo,vnpay',
            'shipping_address' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'required|email',
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
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Check stock availability
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('stock')->find($item['product_variant_id']);
                
                if (!$variant) {
                    throw new \Exception("Product variant not found: {$item['product_variant_id']}");
                }
                
                if (!$variant->stock) {
                    throw new \Exception("Stock information not available for variant: {$variant->id}");
                }
                
                if ($variant->stock->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$item['product_variant_id']}");
                }
            }

            // Create order
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
                'total' => $request->total, 
                'status' => 'pending',
            ]);

            // Create order items
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

                // For COD, reduce stock immediately
                if ($request->payment_method === 'cod') {
                    $variant->stock->decrement('quantity', $item['quantity']);
                }
            }

            event(new NewOrderCreated($order->order_number, $order->id));

            DB::commit();

            // Process payment
            $paymentService = PaymentServiceFactory::create($request->payment_method);
            $paymentResult = $paymentService->process($order, $order->total);

            return response()->json([
                'message' => 'Checkout successful',
                'data' => [
                    'order_id' => $order->id,
                    'payment_url' => $paymentResult['payment_url'] ?? null,
                    'order' => $order->load(['items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Checkout failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get list of orders
     */
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
            'message' => 'Orders retrieved successfully',
            'data' => $orders
        ]);
    }

    /**
     * Get order details
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->load(['user', 'items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);

        return response()->json([
            'message' => 'Order retrieved successfully',
            'data' => $order
        ]);
    }

    /**
     * Cancel an order
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['message' => 'Order cannot be cancelled in its current state'], 400);
        }

        DB::beginTransaction();

        try {
            // Restock items if payment was made or COD
            if ($order->payment_status === 'paid' || $order->payment_method === 'cod') {
                foreach ($order->items as $item) {
                    $variant = $item->productVariant;
                    if ($variant && $variant->stock) {
                        $variant->stock()->increment('quantity', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => 'cancelled',
                'payment_status' => ($order->payment_status === 'paid') ? 'refunded' : 'cancelled',
            ]);

            DB::commit();
            return response()->json(['message' => 'Order cancelled successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation error: ' . $e->getMessage());
            return response()->json(['message' => 'Order cancellation failed'], 500);
        }
    }

    /**
     * Confirm order received
     */
    public function confirmReceived($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status !== 'shipped') {
            return response()->json(['message' => 'Order cannot be confirmed in its current state'], 400);
        }

        $order->status = 'delivered';
        $order->delivered_at = now();

        if ($order->payment_method === 'cod') {
            $order->payment_status = 'paid';
        }

        $order->save();

        return response()->json(['message' => 'Order received confirmed']);
    }

    /**
     * Handle MOMO webhook
     */
    public function momoWebhook(Request $request)
    {
        $paymentService = PaymentServiceFactory::create('momo');
        $result = $paymentService->handleWebhook($request->all());

        $status = $result['success'] ? 200 : 400;
        return response()->json(['message' => $result['message']], $status);
    }

    /**
     * Handle VNPay IPN
     */
    public function vnpayIpn(Request $request)
    {
        $paymentService = PaymentServiceFactory::create('vnpay');
        $result = $paymentService->handleWebhook($request->all());

        $rspCode = $result['success'] ? '00' : '99';
        return response()->json(['RspCode' => $rspCode, 'Message' => $result['message']]);
    }
}