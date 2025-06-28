<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Order

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
;

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    // Chỉ chủ sở hữu đơn hàng mới được theo dõi đơn hàng của mình
    $order = Order::find($orderId);

    return $order && $order->user_id === $user->id;
});

