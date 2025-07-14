<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Chat extends Model
{
    protected $fillable = ['user_id', 'sender', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
