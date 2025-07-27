<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VoucherUser extends Pivot
{
    protected $table = 'voucher_user';
    protected $fillable = [
        'voucher_id',
        'user_id',
        'used'
    ];
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
