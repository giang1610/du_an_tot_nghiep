<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
      
        
        // if (Auth::check() && in_array(Auth::user()->role, [1,2])) {
        //     return $next($request);
        // }
         $user = Auth::user();
         $path = $request->path();

        
          // Nếu truy cập admin mà không phải role = 1
        if (
            preg_match('#^admin/(revenue|users|dashboard)#', $path)
            && $user->role != 1
        ) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Bạn không có quyền truy cập!'], 403);
            }

            // Trả alert tại trang hiện tại
            return response('<script>alert("Bạn là nhân viên không có quyền vào trang này");window.history.back();</script>');
        }

        return $next($request);

        
    } 
}
