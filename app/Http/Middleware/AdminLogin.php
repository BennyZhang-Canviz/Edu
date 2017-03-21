<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\UserRolesServices;
use Illuminate\Support\Facades\Auth;

class AdminLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        if (!Auth::check()) {
//            return redirect("/login");
//        }
        $user = Auth::user();
       $isAdmin = (new UserRolesServices)->IsUserAdmin($user->o365UserId);
       if(!$isAdmin)
           return redirect("/login");
        return $next($request);
    }
}
