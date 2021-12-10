<?php

namespace App\utils;

use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ResponseHelper
{
    //
    public static function successMsg($msg)
    {
        return self::json(['message' => $msg, 'code' => 200]);
    }

    public static function errorMsg($msg, $code = 401, $err_msg = false, $err_code = false)
    {
        $err_msg = $err_msg === false ? $msg : $err_msg;
        $err_code = $err_code === false ? $code : $err_code;
        return self::json(['message' => $msg, 'err_msg' => $err_msg ?? $msg, 'code' => $code, 'err_code' => $err_code]);
    }

    public static function successData($data, $msg = '成功', $code = 200)
    {
        return self::json(['data' => $data, 'message' => $msg, 'code' => 200]);
    }

    public static function errorData($data, $msg = '失败', $code = 401, $err_msg = false, $err_code = false)
    {
        $err_msg = $err_msg === false ? $msg : $err_msg;
        $err_code = $err_code === false ? $code : $err_code;
        return self::json(['data' => $data, 'message' => $msg, 'err_msg' => $err_msg ?? $msg, 'code' => $code, 'err_code' => $err_code]);
    }

    public static function json($data)
    {
        return response()->json($data, 200);
    }

    //验证器
    public static function validate($request, $arr)
    {
        $arr = self::loadValidateArr($arr);
        $validator = Validator::make($request->all(), $arr);
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->all()[0], 0);
        }
        $request->validate($arr);
        return $request->all();
    }

    protected static function loadValidateArr($arr)
    {
        $validate_arr = [];
        foreach ($arr as $key => $item) {
            if (is_numeric($key)) {
                $validate_arr[$item] = 'required';
            } else {
                if (strstr('required', $item))
                    $validate_arr[$key] = $item;
                else $validate_arr[$key] = $item . '|' . 'required';
            }
        }
        return $validate_arr;
    }
}
