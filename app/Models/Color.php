<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = ['name', 'image'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
