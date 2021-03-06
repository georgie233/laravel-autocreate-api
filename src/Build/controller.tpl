<?php
namespace {NAMESPACE_CONTROLLER};
use App\Http\Controllers\BaseApiController;
use App\utils\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use {NAMESPACE_MODEL};
use {NAMESPACE_REQUEST};
class {CONTROLLE_NAME} extends BaseApiController
{
    //显示列表 GET: /{SMODULE}/{SMODEL}
    public function index(Request $request,{MODEL} ${SMODEL})
    {
        $data = $this->handIndex($request->all(), ${SMODEL});
        return ResponseHelper::successData($data);
    }

    //保存数据 POST: /{SMODULE}/{SMODEL}
    public function store({MODEL}Request $request,{MODEL} ${SMODEL})
    {
        $data = $this->getRequestData($request,true);
        {STOREINSERT}
        ${SMODEL}->fill($data);
        ${SMODEL}->save();
        return ResponseHelper::successMsg('保存成功');
    }

    //显示记录 GET: /{SMODULE}/{SMODEL}/id
    public function show({MODEL} ${SMODEL})
    {
        return ResponseHelper::successData(${SMODEL});
    }


    //更新数据 PUT: /{SMODULE}/{SMODEL}/id
    public function update({MODEL}Request $request, {MODEL} ${SMODEL})
    {
        $data = $this->getRequestData($request,true);
        {UPDATEINSERT}
        ${SMODEL}->update($data);
        return ResponseHelper::successMsg('修改成功');
    }

    //删除模型 DELETE: /{SMODULE}/{SMODEL}/id
    public function destroy(${SMODEL})
    {
        {DELETEINSERT}
        ${SMODEL} = explode(',',${SMODEL});
        {MODEL}::whereIn('id',${SMODEL})->delete();
        return ResponseHelper::successMsg('删除成功');
    }

    //获取相关关联数据 /{SMODULE}/{SMODEL}_relation_data
    public function relationData(Request $request, {MODEL} ${SMODEL})
    {
        $fun = $request->relation . 'Class';
        switch ($request->relation) {
            {RELATIONINSERT}
            default:  $arr = ['*'];
        }

        if (!method_exists(${SMODEL},$fun)) return ResponseHelper::errorMsg('参数错误', 400);
        return ResponseHelper::successData(${SMODEL}->$fun()->get($arr)->toArray());
    }

    //作废添加页面 GET: /{SMODULE}/{SMODEL}/create
    public function create(){return ResponseHelper::errorMsg('', 404);}

    //作废编辑页面 GET: /{SMODULE}/{SMODEL}/id/edit
    public function edit(){return ResponseHelper::errorMsg('', 404);}
}
