<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;


class CartItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
        'selected',
        'note',
    ];
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

}
