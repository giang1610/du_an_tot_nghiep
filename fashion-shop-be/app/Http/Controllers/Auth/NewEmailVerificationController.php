<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class NewEmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->id);

       
        $user->forceFill([
            'email' => $request->new_email,
            'email_verified_at' => now(),
        ])->save();

        return redirect(config('app.fotn_url') .'profile');
    }
}
