<?php
/**
 *  Copyright (c) Microsoft Corporation. All rights reserved. Licensed under the MIT license.
 *  See LICENSE in the project root for license information.
 */

namespace App\Http\Middleware;

use Closure;
use App\Services\UserRolesService;
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
        $user = Auth::user();
       $isAdmin = (new UserRolesService)->IsUserAdmin($user->o365UserId);
       if(!$isAdmin)
           return redirect("/login");
        return $next($request);
    }
}
