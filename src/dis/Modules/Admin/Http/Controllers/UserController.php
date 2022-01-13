<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\BaseApiController;
use App\User;
use App\utils\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\UserRequest;

class UserController extends BaseApiController
{
    protected function excludeID($request)
    {
        //返回排除的用户id集：站长账号，当前登录账号
        $ids = User::role(config('georgie_config.webmaster'))->pluck('id')->toArray();
        $ids[] = $request->attributes->get('user')['id'];
        return $ids;
    }

    //显示列表 GET: /admin/user
    public function index(Request $request, User $user)
    {
        $res = $request->all();
        $res['search_id'] = '!=,' . implode('&!=,', $this->excludeID($request));
        $data = $this->handIndex($res, $user);
        //进行排除站长 和 登录用户本身

        return ResponseHelper::successData($data);
    }

    //保存数据 POST: /admin/user
    public function store(UserRequest $request, User $user)
    {
        $data = $this->getHandData($request);
        $data['password'] = bcrypt($data['password']);//密码加密
        $user->fill($data);
        $user->save();
        return ResponseHelper::successMsg('保存成功');
    }

    //显示记录 GET: /admin/user/id
    public function show(User $user)
    {
        return ResponseHelper::successData($user);
    }


    //更新数据 PUT: /admin/user/id
    public function update(UserRequest $request, User $user)
    {
        $data = $request->all();
        $ids = $this->excludeID($request);//禁止删除的
        if (in_array($user['id'], $ids)) throw new \Exception('该操作已被禁止', 403);
        if (isset($data['password'])) $data['password'] = bcrypt($data['password']);//加密密码
        $user->update($data);
        return ResponseHelper::successMsg('修改成功');
    }

    //删除模型 DELETE: /admin/user/id
    public function destroy($user, Request $request)
    {
        $user = explode(',', $user);//获取所有要删除的
        $ids = $this->excludeID($request);//禁止删除的
        foreach ($user as $u) {
            if (in_array($u, $ids))
                throw new \Exception('该操作已被禁止', 403);
        }
        User::whereIn('id', $user)->delete();
        return ResponseHelper::successMsg('删除成功');
    }

    //获取相关关联数据 /admin/user_relation_data
    public function relationData(Request $request, User $user)
    {
        $fun = $request->relation . 'Class';
        switch ($request->relation) {

            default:
                $arr = ['*'];
        }

        if (!method_exists($user, $fun)) return ResponseHelper::errorMsg('参数错误', 400);
        return ResponseHelper::successData($user->$fun()->get($arr)->toArray());
    }

    //作废添加页面 GET: /admin/user/create
    public function create()
    {
        return ResponseHelper::errorMsg('', 404);
    }

    //作废编辑页面 GET: /admin/user/id/edit
    public function edit()
    {
        return ResponseHelper::errorMsg('', 404);
    }
}
