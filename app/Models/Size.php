<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
