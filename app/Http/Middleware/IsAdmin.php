<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role == 1) {
    return $next($request);
}
abort(403, 'Bạn không có quyền truy cập trang này.');
    }
}
