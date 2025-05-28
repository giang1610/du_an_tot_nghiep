<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use App\Models\Category;
use App\Models\Comment;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\ProductVariantOption;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function variantOptions()
    {
        return $this->hasMany(ProductVariantOption::class);
    }

=======

class Product extends Model
{
    protected $fillable = ['name', 'price', 'img'];
>>>>>>> b0ca560353e2869b0feee85a75af71ae0bb9b699
}
