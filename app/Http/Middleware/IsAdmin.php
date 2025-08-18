<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
      
        if (Auth::check() && in_array(Auth::user()->role, [1,2])) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền truy cập');
    }
}
