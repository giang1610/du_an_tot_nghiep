<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
class Voucher extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'discount_type',
        'discount_amount',
        'discount_percent',
        'start_date',
        'end_date',
        'quantity',
        'usage_limit'
    ];

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'voucher_user')
            ->withPivot('used')
            ->withTimestamps();
    }
}


