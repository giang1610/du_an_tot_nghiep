<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    
    public function selectedItems()
    {
        return $this->hasMany(CartItem::class)->where('selected', true);
    }

    public function getTotalAttribute()
    {
        return $this->selectedItems->sum(function ($item) {
            return $item->quantity * $item->variant->current_price;
        });
    }
}
