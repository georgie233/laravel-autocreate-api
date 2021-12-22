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
    //显示列表 GET: /{SMODEL}
    public function index(Request $request,{MODEL} ${SMODEL})
    {
        $data = $this->handIndex($request->all(), ${SMODEL});
        return ResponseHelper::successData($data);
    }

    //保存数据 POST: /{SMODEL}
    public function store({MODEL}Request $request,{MODEL} ${SMODEL})
    {
        $data = $request->all();
        {STOREINSERT}
        ${SMODEL}->fill($data);
        ${SMODEL}->save();
        return ResponseHelper::successMsg('保存成功');
    }

    //显示记录 GET: /{SMODEL}/id
    public function show({MODEL} $field)
    {
        if ($this->isAjax) return $this->responseSuccess($field);

        return view('{VIEW_NAME}.show', compact('field'));
    }


    //更新数据 PUT: /{SMODEL}/id
    public function update({MODEL}Request $request, {MODEL} ${SMODEL})
    {
        $data = $request->all();
        {UPDATEINSERT}
        ${SMODEL}->update($data);

        if ($this->isAjax) return $this->responseSuccess(${SMODEL});
        return redirect('/{ROUTE_ROOT}')->with('success','更新成功');
    }

    //删除模型 DELETE: /{SMODEL}/id
    public function destroy({MODEL} ${SMODEL})
    {
        {DELETEINSERT}
        ${SMODEL}->delete();

        if ($this->isAjax) return $this->responseSuccess([]);
        return redirect('{ROUTE_ROOT}')->with('success','删除成功');
    }
}
