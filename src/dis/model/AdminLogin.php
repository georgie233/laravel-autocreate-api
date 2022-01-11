<?php

namespace App;

use App\utils\ResponseHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminLogin extends Model
{
    //
    protected $table = 'admin_login_token';
    protected $fillable = ['user_id', 'token'];
    protected static $day = 7;

    public static function modeLogin($model, $token = false)
    {
        $expireAt = date('Y-m-d H:i:s', strtotime("+ " . self::$day . " day"));
        if ($token === false) {
            $token = Crypt::encrypt(['user_id' => $model['id'], 'expireAt' => $expireAt]);
            self::create(['user_id' => $model['id'], 'token' => $token]);
        }
        $expireAt = Crypt::decrypt($token)['expireAt'];
        $data = [
            'permissions' => self::getWebPermissions($model),//获取所有角色
            'roles' => self::getWebRoles($model),//获取所有权限
            'user' => $model,
            'token' => $token,
            'expireAt' => $expireAt,
        ];
        return ResponseHelper::successData($data, '登录成功,' . self::getTimeWlcome());
    }

    public static function login($name, $pwd)
    {
        $user = User::where('name', $name)->first();
        if (!$user) return ResponseHelper::errorMsg('账号不存在', 1);
        if (!Hash::check($pwd, $user['password'])) return ResponseHelper::errorMsg('密码错误', 2);
        return self::modeLogin($user);
    }

    public static function logout($token)
    {
        $bool = AdminLogin::where('token', $token)->delete();
        return $bool;
    }


    protected static function getTimeWlcome()
    {
        $time = date('H');
        if ($time <= 4 || $time >= 23) return '深夜了 要注意身体哦!';
        if ($time >= 5 && $time < 8) return '早晨好 美好的一天开始了';
        if ($time >= 8 && $time < 12) return '上午好 工作愉快哦!';
        if ($time >= 12 && $time < 14) return '中午好 工作愉快哦!';
        if ($time >= 14 && $time < 18) return '下午好 工作愉快哦!';
        if ($time >= 18 && $time < 23) return '晚上好 该休息了!';
    }

    protected static function getWebPermissions($model)
    {
        $arr = [];
        $webmasterName = config('georgie_config.webmaster');
        //如果该用户是站长直接返回空数组  因为在角色权限那里已经列出所有
        if ($model->roles()->where('roles.name', $webmasterName)->count() > 0) return $arr;
        foreach ($model->getDirectPermissions() as $directPermission) {
            $arr[] = [
                'id' => $directPermission['name'],
                'operation' => [$directPermission['name']],
            ];
        }
        return $arr;
    }

    protected static function getWebRoles($model)
    {
        $arr = [];// 拥有角色列表
        $hasPermissions = [];//拥有权限列表 用于排除重复
        $webmasterName = config('georgie_config.webmaster');
        $roleWithPermissions = [];//通过角色获取全选

        if ($model->roles()->where('roles.name', $webmasterName)->count() > 0) {
            //如果是站长直接返回站长角色和所有权限
            $roleWithPermissions[] = [
                'id' => Role::where('name', $webmasterName)->first()['id'],
                'name' => $webmasterName,
                'permissions' => Permission::get(['id', 'name'])->toArray(),
            ];
        } else{
            //非站长则读取所有角色
            $roleWithPermissions = $model->roles()->with('permissions')->get()->toArray();
        }

        //循环格式化获取得到的权限
        foreach ($roleWithPermissions as $item) {
            $permissions = [];
            if (isset($item['permissions'])) {
                foreach ($item['permissions'] as $p) {
                    if (in_array($p['name'], $hasPermissions)) continue;//如果其他角色已经拥有该权限 则忽略此条权限
                    $permissions[] = $p['name'];
                    $hasPermissions[] = $p['name'];
                }
            }
            $arr[] = [
                'id' => $item['name'],
                'id_' => $item['id'],
                'operation' => $permissions,
            ];
        }
        return $arr;
    }
}
