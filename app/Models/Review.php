<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductVariant;
/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property int $product_variant_id
 * @property int $review_round
 * @property int $rating
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Order $order
 * @property-read ProductVariant $productVariant
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereProductVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereReviewRound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Review whereUserId($value)
 * @mixin \Eloquent
 */
class Review extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'product_variant_id',
        'review_round',
        'rating',
        'content',
        'media',
        'status',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }
   
    public function product()
    {
    // Lấy product thông qua productVariant
    return $this->hasOneThrough(
        \App\Models\Product::class,
        \App\Models\ProductVariant::class,
        'id', // Khóa chính của ProductVariant
        'id', // Khóa chính của Product
        'product_variant_id', // Khóa ngoại trên Review
        'product_id' // Khóa ngoại trên ProductVariant
    );
    }
  

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class)->with(['product', 'size', 'color']);
    }

}
