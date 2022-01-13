<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TempFile extends Model
{
    protected $fillable = ['url'];

    public static function insert($url){
        TempFile::create(['url'=>$url]);
    }
    public static function removeTmp($url){
        TempFile::where('url',$url)->delete();
    }
    public static function get($timeStr = null){
        $files =  TempFile::where('created_at','<',$timeStr??(date('Y-m-d H:i:s')))->get();
        return $files;
    }
}
