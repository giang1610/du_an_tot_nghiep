<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property-read \App\Models\ProductVariant|null $productVariant
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantOption query()
 * @mixin \Eloquent
 */
class ProductVariantOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_variant_id',
        'attribute_name',
        'attribute_value',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
