<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_variant_id
 * @property int $quantity
 * @property int|null $color_id
 * @property int|null $size_id
 * @property string $price
 * @property string|null $sale_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Color|null $color
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\ProductVariant $productVariant
 * @property-read \App\Models\Size|null $size
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereColorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    public function productVariant() // dùng cho fontend
{
    return $this->belongsTo(ProductVariant::class, 'product_variant_id');
}

    public function variant() // dùng cho backend
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
