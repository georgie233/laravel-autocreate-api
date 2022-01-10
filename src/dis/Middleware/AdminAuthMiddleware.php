<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\AdminLoginController;

class AdminAuthMiddleware
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
        $controllerHelper = new AdminLoginController();
        $model = $controllerHelper->getUserModel($request);
        $request->attributes->set('user', $model);
        return $next($request);
    }
}
