<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class EmailVerifiFotnController extends Controller
{
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->id);

        if (! hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Liên kết xác minh không hợp lệ.');
        }

        if ($user->hasVerifiedEmail()) {
        abort(404); 
        }

        $user->markEmailAsVerified();

         $redirectUrl = config('app.fotn_url') . 'login?message=email_verified&email=' . $user->email;
         return redirect($redirectUrl);
    }
}
