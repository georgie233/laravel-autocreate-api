<?php

namespace App\Http\Controllers;

use App\AdminLogin;
use App\User;
use App\utils\ResponseHelper;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    //
    protected $openRegister = true;

    public function login(Request $request)
    {
        $data = ResponseHelper::validate($request,['name','password']);
        $response = AdminLogin::login($data['name'],$data['password']);
        return $response;
    }

    public function register(Request $request)
    {
        $data = ResponseHelper::validate($request,['nick_name','name'=>'unique:users','password']);
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        if (isset($request->login) && $request->login===true){
            $response = AdminLogin::modeLogin($user);
            return $response;
        }
        return ResponseHelper::successData($user,'注册成功');
    }

    public function info(Request $request)
    {

    }

    public function checkToken(Request $request)
    {

    }
}
