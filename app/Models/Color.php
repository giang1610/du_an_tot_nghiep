<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Color extends Model
{
    protected $fillable = ['name', 'image'];

    use HasFactory;

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
     public function products()
    {
        return $this->belongsToMany(Product::class, 'color_product', 'color_id', 'product_id');
    }
}
