<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
class Voucher extends Model
{
    protected $table = 'vouchers';
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
        return $this->belongsToMany(User::class, 'voucher_user')
         ->using(VoucherUser::class)
            ->withPivot('used')
            ->withTimestamps();
    }
}


