<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
<<<<<<< HEAD
    public function products() {
        return $this->hasMany(Product::class, 'category_id');
    }

=======

    // Nếu bạn muốn, có thể thêm quan hệ products
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
>>>>>>> b0ca560353e2869b0feee85a75af71ae0bb9b699
}
