<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        
        'order_id',
        'product_variant_id',
        'quantity',
        'price',
        'size_id',
        'color_id',
        'sale_price',
        
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

<<<<<<< HEAD
    // ✅ Sửa tên để phù hợp với email blade
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
=======
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
     public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

public function size()
{
    return $this->belongsTo(Size::class);
}

public function color()
{
    return $this->belongsTo(Color::class);
}


>>>>>>> ebd52da (Tối ưu code,đổi giao diện,đủ luồng)

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
}
