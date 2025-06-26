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
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
   

public function size()
{
    return $this->belongsTo(Size::class);
}

public function color()
{
    return $this->belongsTo(Color::class);
}



}
