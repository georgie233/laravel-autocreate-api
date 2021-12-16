<?php

namespace Georgie\AutoAPi\Commands;

use Georgie\AutoAPi\Traits\CreateView;
use Georgie\AutoAPi\Traits\Db;
use Georgie\AutoAPi\Traits\BuildVars;
use Illuminate\Console\Command;
use Artisan;
use Storage;

class AutoApiCommand extends Command
{
    use BuildVars, Db, CreateView;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:api {model} {module} {title} {carry=n}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create controller view request';
    protected $model;
    protected $modelClass;
    protected $modelInstance;
    protected $modelFile;
    protected $module;
    protected $title;
    protected $carry;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->carry = ucfirst($this->argument('carry'));
        $this->model = $this->argument('model');
        $this->module = $this->argument('module');
        $this->modelFile = config('modules.paths.modules') . '/' . $this->module . '/'
            . config('modules.paths.generator.model.path') . '/' . $this->argument('model') . '.php';
        $this->modelClass = config('modules.namespace') . '\\' . $this->module . '\\' . config('modules.paths.generator.model.path')
            . '\\' . $this->argument('model');
        if (!is_file($this->modelFile)) {
            $this->error("model file {$this->modelFile} no exists");

            return;
        }
        $this->title = $this->argument('title');
        $this->setVar('MODEL_TITLE', $this->title);
        $this->setModelInstance();
        $this->setModelFillable();//设置fillable √
        $this->setModelRelation();//设置模型关联 √

        //---
//        $this->createController();
//        $this->createRequest();
//        $this->createRoute();
//        $this->createViews();
    }

    protected function setModelRelation()
    {
        $arr = ['user', 'select'];
        $columns = $this->formatColumns();
        foreach ($columns as $column) {
            if (isset($column['options']) && count($column['options']) >= 2) {
                if (in_array($column['options'][1], $arr)) {//符合关联关系处理
                    $action = 'relation_' . $column['options'][1];
                    $this->$action($column['options'][2], $column['name']);//传递关联数据和字段名称
                }
            }
        }
        $this->info('model relation complete');
    }

    protected function setModelInstance()
    {
        $this->modelInstance = new $this->modelClass;
    }


    protected function setModelFillable()
    {
        $columns = array_keys($this->getColumnData());
        $columns = implode("','", $columns);
        $content = file_get_contents($this->modelFile);
        $regp = '@(protected\s+\$fillable\s*\=\s*\[)\s*\];@im';
        if (preg_match($regp, $content)) {
            $content = preg_replace($regp, '${1}' . "'" . $columns . '\'];', $content);
            file_put_contents($this->modelFile, $content);
            $this->info('update model fillable attribute');
        }
    }

    protected function createViews()
    {
        $dir = $this->vars['VIEW_PATH'];
        is_dir($dir) or mkdir($dir, 0755, true);
        $this->createIndexBlade();//创建PC显示页面
        $this->createMobileBlade();//创建手机显示页面
        $this->createCreateAndEditBlade();//创建PC编辑和修改页面
    }

    protected function createRoute()
    {
        if ($this->module) {
            $file = $this->getVar('MODULE_PATH') . '/Http/routes.php';
        } else {
            $file = 'routes/web.php';
        }
        $route = file_get_contents($file);
        //检测路由
        if (strstr($route, "{$this->vars['SMODEL']}-route")) {
            return;
        }
        if ($this->module) {
            $route .= <<<str
\n
//{$this->vars['SMODEL']}-route
Route::group(['middleware' => ['web'],'prefix'=>'{$this->vars['SMODULE']}','namespace'=>"{$this->vars['NAMESPACE_HTTP']}\Controllers"],
function () {
    Route::resource('{$this->vars['SMODEL']}', '{$this->vars['MODEL']}Controller');
});
str;
            $route .= <<<str
\n \n
//{$this->vars['SMODEL']}-route-api
Route::group(['middleware' => ['api'],'prefix'=>'api/{$this->vars['SMODULE']}','namespace'=>"{$this->vars['NAMESPACE_HTTP']}\Controllers"],
function () {
    Route::resource('{$this->vars['SMODEL']}', '{$this->vars['MODEL']}Controller');
});
str;
        } else {
            $route .= <<<str
\n
//{$this->vars['SMODEL']}-route
Route::resource('{$this->vars['SMODEL']}', '{$this->vars['MODEL']}Controller');
str;
        }
        file_put_contents($file, $route);
        $this->info('route create successfully');
    }

    //创建控制器
    public function createController()
    {
        $file = $this->getVar('CONTROLLER_PATH') . $this->model . 'Controller.php';

        if (is_file($file)) {
            return false;
        }

//        $str = "";
//        $this->setVar("STOREINSERT", $str);


        $content = $this->replaceVars(__DIR__ . '/../Build/controller.tpl');
        file_put_contents($file, $content);
        $this->info('controller create successflly');
    }

    public function createRequest()
    {
        $file = $this->getVar('REQUEST_PATH') . $this->model . 'Request.php';
        if (is_file($file)) {
            return false;
        }
        $content = $this->replaceVars(__DIR__ . '/../Build/request.tpl');
        $content = str_replace('{REQUEST_RULE}', var_export($this->getRequestRule(), true), $content);
        $content = str_replace('{REQUEST_RULE_MESSAGE}', var_export($this->getRequestRuleMessage(), true), $content);
        file_put_contents($file, $content);
        $this->info('request create successflly');
    }

    protected function getRequestRule()
    {
        $columns = $this->formatColumns();
        $rules = [];
        foreach ($columns as $column) {
            $check = $column && in_array($column['name'], $this->modelInstance->getFillAble());
            if ($check && $column['nonull']) {
                $rules[$column['name']] = 'required';
            }
        }

        return $rules;
    }

    protected function getRequestRuleMessage()
    {
        $columns = $this->formatColumns();
        $rules = [];
        foreach ($columns as $column) {
            $check = $column && in_array($column['name'], $this->modelInstance->getFillAble());
            if ($check && $column['nonull']) {
                $rules[$column['name'] . '.required'] = "请设置 " . $column['title'];
            }
        }

        return $rules;
    }

    protected function relation_user($data, $field)
    {
        $this->_relation_fun($data['user'], $field, $data['user']['relation'] == '&');
    }

    protected function relation_select($data, $field)
    {
        $this->_relation_fun($data['select'], $field, $data['select']['relation'] == '&');
    }

    //处理一对多关系
    protected function _relation_fun($data, $field, $isMany = false)
    {
        if ($data['module'] == "") {//app下的模型
            $modelFileUrl = app_path(ucfirst($data['model']) . '.php');
            $name = '\App\\' . ucfirst($data['model']);
        } else {
            $modelFileUrl = config('modules.paths.modules') . '/' . ucfirst($data['module']) . '/'
                . config('modules.paths.generator.model.path') . '/' . ucfirst($data['model']) . '.php';
            $name = '\Modules\\' . ucfirst($data['module']) . '\\Entities\\' . ucfirst($data['model']);
        }
        $myFunName = lcfirst($data['model']);//我的方法
        $otherFunName = lcfirst($this->model);//对方模型里方法

        //为本模型植入关联
        $content = file_get_contents($this->modelFile);
        if (strpos($content, 'function ' . $myFunName) === false) {
            $content = substr($content, 0, strrpos($content, '}'));
            $content .= <<<str

    public function {$myFunName}Class(){
        return new {$name};
    }
    public function {$myFunName}(){
        return \$this->belongsTo(\$this->{$myFunName}Class(),'{$field}');
    }

}
str;
            file_put_contents($this->modelFile, $content);
        }

        //为对方模型植入关联
        $t = $isMany ? 'hasMany' : 'hasOne';
        $content = file_get_contents($modelFileUrl);
        if (strpos($content, 'function ' . $otherFunName) === false) {
            $content = substr($content, 0, strrpos($content, '}'));
            $content .= <<<str

    public function {$otherFunName}Class(){
        return new \\{$this->modelClass};
    }
    public function {$otherFunName}(){
        return \$this->{$t}(\$this->{$otherFunName}Class(),'{$field}');
    }

}
str;
            file_put_contents($modelFileUrl, $content);
        }
    }
}