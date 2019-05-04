<?php

namespace App\Http\Middleware;

use Closure;

class LoginCheck
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
        //检查登录信息
        $login = session('admin.login');
        if (!$login){
            return redirect('admin/login');
        }
        return $next($request);
    }
}
