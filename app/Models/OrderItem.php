<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'quantity',
        'price',
        'sale_price',
        'color_id',
        'size_id',
    ];

    /**
     * Quan hệ đến đơn hàng.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Quan hệ đến biến thể sản phẩm.
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Lấy sản phẩm gốc thông qua biến thể.
     */
    public function product()
    {
        return $this->variant ? $this->variant->product() : null;
    }

    /**
     * Lấy màu sắc nếu có, thông qua variant.
     */
    public function color()
    {
        return $this->variant ? $this->variant->color() : null;
    }

    /**
     * Lấy kích cỡ nếu có, thông qua variant.
     */
    public function size()
    {
        return $this->variant ? $this->variant->size() : null;
    }
}
