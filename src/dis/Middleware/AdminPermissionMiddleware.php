<?php

namespace App\Http\Middleware;

use App\User;
use App\utils\HttpCode;
use Closure;
use Illuminate\Support\Facades\DB;

class AdminPermissionMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @param string $guard 验证组
     * @param null $type 验证类型: null | resource | relation
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next, string $guard = 'web', $type = null)
    {
        $next = $next($request);
        $user = $request->attributes->get('user');
        if (!$user) throw new \Exception('请登录', HttpCode::$Unauthorized);
        if ($this->isWebMaster($user)) return $next;//站长无效验证
        $routePermission = $this->getRoutePermission($type);
        $hasPermission = $this->hasPermission($routePermission, $guard);
        if (!$hasPermission)return $next;
        if ($user->getAllPermissions()->where('name',$routePermission)->count() === 0)
            throw new \Exception('你没有访问权限', HttpCode::$Forbidden);

        return $next;
    }

    /**
     * @param $model
     * @return bool
     * @description 判断该用户是否为站长
     */
    protected function isWebMaster($model)
    {
        $relation = $model->roles();
        $has = $relation->where('roles.name', config('georgie_config.webmaster'))->first();
        return boolval($has);
    }

    /**
     * @param $type
     * @return mixed|string|string[]
     * @description 获取当前路由的权限标识
     */
    protected function getRoutePermission($type)
    {
        $route = \Route::getCurrentRoute();
        /**
         * $type 路由类型处理
         * store 和 relation 使用 create统一进行验证
         * update 使用 edit统一进行验证
         */
        if ($type === 'resource') {
            return str_replace(['@store', '@update'], ['@create', '@edit'], $route->action['controller']);
        } else if ($type === 'relation') {
            $url = $route->action['controller'];
            return substr($url, 0, strripos($url, '@')) . '@create';
        }
        return $route->action['controller'];
    }

    /**
     * @param string $routePermission
     * @param $guard
     * @description 判断数据库是否存在此权限
     */
    protected function hasPermission(string $routePermission, $guard)
    {
        $where = [
            ['name', '=', $routePermission],
            ['guard_name', '=', $guard],
        ];
        $has = DB::table('permissions')->where($where)->first();

        return boolval($has);
    }
}
