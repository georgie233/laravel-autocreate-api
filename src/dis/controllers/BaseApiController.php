<?php

namespace App\Http\Controllers;

use App\utils\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BaseApiController extends Controller
{
    //处理index请求
    public function handIndex($requestData, $model)
    {
        try {
            return $this->handIndexFun($requestData, $model);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage(), 500);
        }
    }

    protected function handIndexFun($requestData, $model)
    {
        $data = [];

        $model = $this->handWhereArr($model, $requestData);//拼接条件
        $model = $this->handWithArr($model, $requestData);//拼接携带with

        $count = $model->count();
        if (isset($requestData['start']) || isset($requestData['len']))
            $data = $model->offset($requestData['start'] ?? 0)->limit($requestData['len'] ?? 10)->get();
        else $data = $model->get();

        if (isset($requestData['only']))
            $data = $this->handOnly($data, $requestData);//处理只显示
        else if (isset($requestData['except']))
            $data = $this->handExcept($data, $requestData);//处理只排除

        return [
            'list' => $data,
            'count' => $count,
        ];
    }

    protected function handWhereArr($model, $requestData)
    {
        $m = $model;
        $arr = [];

        foreach ($requestData as $k => $i) {
            if (stristr($k, 'search_')) {
                $key = addslashes(str_replace('search_', '', $k));
                foreach (explode('&',$i) as $ii) {
                    $item = addslashes($ii);
                    $v = explode(',', $item);
                    if (count($v) > 1) {
                        if (strtolower($v[0]) == 'like' && stristr($v[1], '%') === false && !empty($v[1])) {
                            $m = $m->where($key, 'like', preg_replace('//u', '%', $v[1]));
                        } else $m = $m->where($key, $v[0], $v[1]);
                    } else $m = $m->where($key, $v[0]);
                }
            }
        }

        return $m;
    }

    protected function handWithArr($model, $requestData)
    {
        $m = $model;
        $arr = [];

        foreach ($requestData as $k => $i) {
            if (stristr($k, 'with_')) {
                $key = addslashes(str_replace('with_', '', $k));
                $item = addslashes($i);

                $m = $m->with([$key => function ($query) use ($item) {
                    $query->select(array_merge(['id'], explode(',', $item)));
                }]);
            }
        }

        return $m;
    }

    protected function handOnly($data, $requestData)
    {
        if (!isset($requestData['only']) || empty($requestData['only'])) return $data;
        if (count($data) === 0) return $data;
        $arr = [];
        $hideArr = [];
        foreach ($data[0]->toArray() as $key => $item) $hideArr[] = $key;
        foreach (explode(',', addslashes($requestData['only'])) as $key => $item) {
            $arr[] = $item;
        }
        return $data->makeHidden($hideArr)->makeVisible($arr);
    }

    protected function handExcept($data, $requestData)
    {
        if (!isset($requestData['except']) || empty($requestData['except'])) return $data;
        if (count($data) === 0) return $data;
        $arr = [];
        foreach (explode(',', addslashes($requestData['except'])) as $key => $item) {
            $arr[] = $item;
        }
        return $data->makeHidden($arr);
    }
}
