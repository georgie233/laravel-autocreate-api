<?php

namespace App\utils;

use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ResponseHelper
{
    protected static $field = [
        'code' => 'code',
        'err_code' => 'err_code',
        'message' => 'message',
        'err_msg' => 'err_msg',
        'data' => 'data',
    ];

    public static function successMsg($msg): \Illuminate\Http\JsonResponse
    {
        return self::json([
            'message' => $msg,
            'code' => HttpCode::$OK,
        ]);
    }

    public static function errorMsg($msg, $code = null, $err_msg = false, $err_code = false): \Illuminate\Http\JsonResponse
    {
        $code = $code ?? HttpCode::$BadRequest;
        $err_msg = $err_msg === false ? $msg : $err_msg;
        $err_code = $err_code === false ? $code : $err_code;
        return self::json([
            'message' => $msg,
            'err_msg' => $err_msg ?? $msg,
            'code' => $code,
            'err_code' => $err_code,
        ]);
    }

    public static function successData($data, $msg = '成功', $code = null): \Illuminate\Http\JsonResponse
    {
        return self::json([
            'data' => $data,
            'message' => $msg,
            'code' => $code ?? HttpCode::$OK,
        ]);
    }

    public static function errorData($data, $msg = '失败', $code = null, $err_msg = false, $err_code = false): \Illuminate\Http\JsonResponse
    {
        $code = $code ?? HttpCode::$BadRequest;
        $err_msg = $err_msg === false ? $msg : $err_msg;
        $err_code = $err_code === false ? $code : $err_code;
        return self::json([
            'data' => $data,
            'message' => $msg,
            'err_msg' => $err_msg ?? $msg,
            'code' => $code,
            'err_code' => $err_code,
        ]);
    }

    public static function json($data, $isFormat = true): \Illuminate\Http\JsonResponse
    {
        $responseData = [];
        if ($isFormat) {
            //是否格式化字段
            $fields = self::$field;
            foreach ($data as $key => $item) {
                if (isset($fields[$key])) {
                    $responseData[$fields[$key]] = $item;
                } else
                    $responseData[$key] = $item;
            }
        } else {
            $responseData = $data;
        }
        return response()->json($responseData, 200);
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
