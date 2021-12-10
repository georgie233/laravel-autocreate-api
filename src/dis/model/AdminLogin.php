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
    protected $fillable = ['user_id','token'];
    protected static $day = 7;

    public static function modeLogin($model){
        $expireAt = date('Y-m-d H:i:s', strtotime("+ ".self::$day." day"));
        $token = Crypt::encrypt(['user_id'=>$model['id'],'expireAt'=>$expireAt]);
        self::create(['user_id'=>$model['id'],'token'=>$token]);
        $data = [
            'permissions'=>[],
            'roles'=>[],
            'user'=>$model,
            'token'=>$token,
            'expireAt'=>$expireAt
        ];
        return ResponseHelper::successData($data,'欢迎登陆');
    }
    public static function login($name,$pwd){
        $user = User::where('name',$name)->first();
        if (!$user)return ResponseHelper::errorMsg('账号不存在',1);
        if (!Hash::check($pwd, $user['password']))return ResponseHelper::errorMsg('密码错误',2);
        return self::modeLogin($user);
    }
}
