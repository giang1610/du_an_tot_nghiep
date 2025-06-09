<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
     use HasFactory,SoftDeletes;
    public $timestamps = false;
    protected $table = 'categories';
    protected $fillable = [
        'name',
        'slug',
        'parent_id', // Khóa ngoại đến bảng categories
        'status',
    ];
    // hasMany là 1-n
 
    

    public function products()
    {
        return $this->hasMany(Product::class);
    }

};
