<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = ['user_id']; // Chỉ giữ user_id

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Thêm quan hệ với cart_items
    public function cartItems()
    {
        return $this->hasMany(CartItem::class); // Giả sử có mô hình CartItem
    }
}
