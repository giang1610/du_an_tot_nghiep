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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function productVariant()
{
    return $this->belongsTo(ProductVariant::class, 'product_variant_id');
}


    public function product()
    {
        return $this->hasOneThrough(
            \App\Models\Product::class,
            \App\Models\ProductVariant::class,
            'id',                 // Foreign key on ProductVariant
            'id',                 // Foreign key on Product
            'product_variant_id', // Local key on OrderItem
            'product_id'          // Local key on ProductVariant
        );
    }

    public function color()
    {
        return $this->hasOneThrough(
            \App\Models\Color::class,
            \App\Models\ProductVariant::class,
            'id',
            'id',
            'product_variant_id',
            'color_id'
        );
    }

    public function size()
    {
        return $this->hasOneThrough(
            \App\Models\Size::class,
            \App\Models\ProductVariant::class,
            'id',
            'id',
            'product_variant_id',
            'size_id'
        );
    }
}