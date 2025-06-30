<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'product_id',
        'product_variant_id',
        'is_default',
    ];

    protected $appends = ['url'];

    /** Relationships */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** Accessors */

    public function getUrlAttribute()
    {
        return $this->image
            ? url('storage/' . ltrim($this->image, '/'))
            : null;
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 4242ec0 (hoàn thiện)
