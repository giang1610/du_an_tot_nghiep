<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'content',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected $appends = [
        'created_at_diff',
    ];

    // -----------------------------
    // Quan hệ với người dùng
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // -----------------------------
    // Hiển thị thời gian bình luận theo kiểu "2 giờ trước"
    public function getCreatedAtDiffAttribute()
    {
        return $this->created_at ? $this->created_at->diffForHumans() : null;
    }
}
