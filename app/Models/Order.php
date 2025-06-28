<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'subtotal', 'tax', 'shipping', 'total',
        'status', 'payment_method', 'payment_status', 'shipping_address',
        'billing_address', 'customer_email', 'customer_phone', 'notes'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
