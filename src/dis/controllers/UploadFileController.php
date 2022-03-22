<?php

namespace App\Http\Controllers;

use App\TempFile;
use App\utils\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UploadFileController extends Controller
{
    public $upload_url = '/upload';
    protected $type = ['image/png', 'image/jpeg'];//允许上传的文件类型
    protected $key = 'upload_file_key';

    //上传文件
    public function uploadFile(Request $request): \Illuminate\Http\JsonResponse
    {
        //校验
        $table_name = $request->header('key');
        if (!$table_name) return ResponseHelper::errorMsg('缺少头参数key', 403);
        if (!Schema::hasTable($table_name)) return ResponseHelper::errorMsg('key错误', 403);


        //上传到的位置
        $route_url = $this->upload_url . '/' . $table_name;
        $upload_url = storage_path($route_url);

        foreach ($request->files as $key => $file) {
            $type = $file->getClientMimeType();//获取文件类型是否为可上传
            if (!in_array($type, $this->type)) return ResponseHelper::errorMsg('该文件类型为禁止上传类型', 403);

            $extend_name = $file->getClientOriginalExtension();//获取扩展名
            $filename = time() . str_random(10) . '.' . $extend_name;//文件名

            $file->move($upload_url, $filename);//保存图片
            $return_url = $this->lock_url($route_url . '/' . $filename);
            TempFile::insert($return_url);

            return ResponseHelper::successData(['url' => $return_url], '上传成功');
        }
    }

    //返回图片流
    public function getUploadFile(Request $request, $url)
    {
        $url = $this->unlock_url($url);
        return file_get_contents(storage_path($url));
    }

    //清理垃圾图片
    public function CleanUpGarbage()
    {
        $files = TempFile::get();
        foreach ($files as $file) {
            $url = $this->unlock_url($file['url']);
            if (file_exists(storage_path($url))) {
                unlink(storage_path($url));
                $file->delete();
            } else {
                $file->delete();
            }
        }
    }

    //加密函数
    public function lock_url($txt)
    {
        $key = $this->key;
        $txt = $txt . $key;
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
        $nh = rand(0, 64);
        $ch = $chars[$nh];
        $mdKey = md5($key . $ch);
        $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
        $txt = base64_encode($txt);
        $tmp = '';
        $i = 0;
        $j = 0;
        $k = 0;
        for ($i = 0, $iMax = strlen($txt); $i < $iMax; $i++) {
            $k = $k === strlen($mdKey) ? 0 : $k;
            $j = ($nh + strpos($chars, $txt[$i]) + ord($mdKey[$k++])) % 64;
            $tmp .= $chars[$j];
        }
        return urlencode(base64_encode($ch . $tmp));
    }

    //解密函数
    public function unlock_url($txt)
    {
        $key = $this->key;
        $txt = base64_decode(urldecode($txt));
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
        $ch = $txt[0] ?? false;
        if (!$ch) {
            return false;
        }
        $nh = strpos($chars, $ch);
        $mdKey = md5($key . $ch);
        $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
        $txt = substr($txt, 1);
        $tmp = '';
        $i = 0;
        $j = 0;
        $k = 0;
        for ($i = 0, $iMax = strlen($txt); $i < $iMax; $i++) {
            $k = $k === strlen($mdKey) ? 0 : $k;
            $j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
            while ($j < 0) {
                $j += 64;
            }
            $tmp .= $chars[$j];
        }
        return trim(base64_decode($tmp), $key);
    }
}
