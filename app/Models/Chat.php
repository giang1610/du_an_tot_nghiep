<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Chat extends Model
{
    protected $fillable = ['user_id', 'sender', 'message'];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function latestMessage()
{
    return $this->hasOne(Chat::class, 'user_id', 'user_id')->latestOfMany();
}

}
