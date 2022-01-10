<?php

namespace App\Http\Controllers;

use App\AdminLogin;
use App\User;
use App\utils\HttpCode;
use App\utils\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AdminLoginController extends Controller
{
    //
    protected $openRegister = true;

    public function login(Request $request)
    {
        $data = ResponseHelper::validate($request, ['name', 'password']);
        $response = AdminLogin::login($data['name'], $data['password']);
        return $response;
    }

    public function register(Request $request)
    {
        $data = ResponseHelper::validate($request, ['nick_name', 'name' => 'unique:users', 'password']);
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        if (isset($request->login) && $request->login === true) {
            $response = AdminLogin::modeLogin($user);
            return $response;
        }
        return ResponseHelper::successData($user, '注册成功');
    }

    public function info(Request $request)
    {
        $model = $this->getTokenModel($request);
        $user = User::find($model['user_id']);
        if (!$user) return ResponseHelper::errorMsg('用户不存在', HttpCode::$Unauthorized);
        return AdminLogin::modeLogin($user, $model['token']);
    }

    public function checkToken(Request $request)
    {
        $model = $this->getTokenModel($request);
        $user = User::find($model['user_id']);
        if (!$user) return ResponseHelper::errorMsg('用户不存在', HttpCode::$Unauthorized);
        return ResponseHelper::successData(['check' => true]);
    }

    public function getToken($request)
    {
        $h = $request->header('Authorization');
        if (!$h) throw new \Exception('令牌不能为空', HttpCode::$Unauthorized);
        return str_replace("Bearer ", "", $h);
    }

    public function getTokenModel($request)
    {
        $token = $this->getToken($request);
        $json = Crypt::decrypt($token);
        if (strtotime($json['expireAt']) < time()) throw new \Exception('登录信息已过期', HttpCode::$Unauthorized);
        $model = AdminLogin::where('token', $token)->first();
        if (!$model) throw new \Exception('登录信息无效', HttpCode::$Unauthorized);
        return $model;
    }

    public function getUserModel($request){
        $model = $this->getTokenModel($request);
        $user = User::where('id',$model['id'])->first();
        if (!$user) throw new \Exception('用户不存在', HttpCode::$Unauthorized);
        return $user;
    }
}
