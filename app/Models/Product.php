<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 *
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property int $status
 * @property string|null $price_products
 * @property string|null $short_description
 * @property string|null $description
 * @property string|null $thumbnail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Color> $colors
 * @property-read int|null $colors_count
 * @property-read mixed $img
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Size> $sizes
 * @property-read int|null $sizes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductVariant> $variants
 * @property-read int|null $variants_count
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePriceProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'category_id',
        'name',
        'slug',
        'type',
        'status',
        'short_description',
        'description',
        'thumbnail',
        'price_products',
    ];

    /**
     * Quan hệ: 1 sản phẩm thuộc 1 danh mục
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Quan hệ: 1 sản phẩm có nhiều biến thể
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->withTrashed();
    }

    // Quan hệ nhiều-nhiều với Color
    public function colors()
    {
        return $this->belongsToMany(Color::class, 'color_product', 'product_id', 'color_id');
    }
    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_size', 'product_id', 'size_id');
    }

    /**
     * Quan hệ: 1 sản phẩm có nhiều ảnh
     */

    // public function images()
    // {
    //     return $this->hasMany(ProductImage::class);
    // }

    public function getImgAttribute()
    {
        return url('storage/' . $this->thumbnail);
    }
    protected $appends = ['img'];
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems()
    {
        return $this->hasManyThrough(OrderItem::class, ProductVariant::class, 'product_id', 'product_variant_id');
    }
}
