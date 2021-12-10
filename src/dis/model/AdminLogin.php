<?php

namespace App;

use App\utils\ResponseHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

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
            'permissions' => [],
            'roles' => [],
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
}
