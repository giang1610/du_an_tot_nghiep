<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string',
            'email'   => 'required|email',
            'address' => 'required|string',
            'cart'    => 'required|array',
        ]);

        $total = collect($data['cart'])->sum(fn ($item) => $item['price'] * $item['quantity']);

        $order = Order::create([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'address' => $data['address'],
            'total'   => $total,
        ]);

        foreach ($data['cart'] as $item) {
            $order->items()->create([
                'product_name' => $item['name'],
                'quantity'     => $item['quantity'],
                'price'        => $item['price'],
            ]);
        }

        Mail::to($order->email)->send(new OrderConfirmation($order));

        return response()->json(['message' => 'Đặt hàng thành công'], 201);
    }
}
