<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Nếu bạn muốn, có thể thêm quan hệ products
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
