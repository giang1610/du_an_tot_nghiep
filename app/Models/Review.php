<?php

namespace App\Models;
    
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductVariant;
class Review extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'product_variant_id',
        'review_round',
        'rating',
        'content',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function productVariant()
{
    return $this->belongsTo(ProductVariant::class)->with(['product', 'size', 'color']);
}

}
