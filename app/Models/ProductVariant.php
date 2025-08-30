<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property string $price
 * @property string|null $sale_price
 * @property string|null $sale_start_date
 * @property string|null $sale_end_date
 * @property string|null $image
 * @property int $color_id
 * @property int $size_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Color $color
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Size $size
 * @property-read \App\Models\Stock|null $stock
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereColorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereSaleEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereSaleStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $cartItems
 * @property-read int|null $cart_items_count
 * @property-read mixed $current_price
 * @property-read mixed $images_urls
 * @property-read mixed $img
 * @property-read mixed $thumbnail
 * @mixin \Eloquent
 */
class ProductVariant extends Model
{
   use HasFactory;
   use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'sale_start_date',
        'sale_end_date',
        'image',
        // 'stock',
        'color_id',
        'size_id'
    ];
    public function images()
    {
        return $this->hasMany(ProductImage::class,'product_variant_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'product_variant_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function getCurrentPriceAttribute()
    {
        if ($this->sale_price && now()->between($this->sale_start_date, $this->sale_end_date)) {
            return $this->sale_price;
        }
        return $this->price;
    }
    public function getImgAttribute()
{
    return $this->image
        ? url('storage/' . ltrim($this->image, '/'))
        : null;
}

    public function getImagesUrlsAttribute()
    {
        return $this->images->map(function ($image) {
            return url('storage/' . ltrim($image->image, '/'));
        });
    }
    public function getThumbnailAttribute()
{
    if ($this->image) {
        return url('storage/' . ltrim($this->image, '/'));
    }

    if ($this->images && $this->images->count() > 0) {
        return url('storage/' . ltrim($this->images->first()->image, '/'));
    }

    return null;
}


    protected $appends = ['img', 'images_urls','thumbnail'];
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }
}
