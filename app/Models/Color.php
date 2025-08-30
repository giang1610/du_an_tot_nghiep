<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $productVariants
 * @property-read int|null $product_variants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $variants
 * @property-read int|null $variants_count
 * @method static \Illuminate\Database\Eloquent\Builder|Color newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Color newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Color query()
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        use HasFactory;

    protected $table = 'colors';  // khai báo rõ tên bảng nếu khác với tên model số nhiều

    // Quan hệ 1 màu có thể có nhiều biến thể sản phẩm
   public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
