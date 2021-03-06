<?php

namespace Georgie\AutoAPi\Commands;

use Georgie\AutoAPi\Traits\CreateView;
use Georgie\AutoAPi\Traits\Db;
use Georgie\AutoAPi\Traits\BuildVars;
use Georgie\AutoAPi\Traits\PermissionHelper;
use Illuminate\Console\Command;
use Artisan;
use Storage;

class AutoApiCommand extends Command
{
    use BuildVars, Db, CreateView, PermissionHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:api {model} {module} {title}
        {--permission : Auto insert permission to table}
        {--p : Auto insert permission to table}';

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
//        $this->carry = ucfirst($this->argument('carry'));
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
        $this->setVar('MODEL_TITLE', $this->title);//设置标题
        $this->setVars();//设置默认参数
        $this->setModelInstance();
        $this->setModelFillable();//设置fillable √
        $this->setModelRelation();//设置模型关联 √
        $this->createController();//创建控制器 代办(图片等)
        $this->createRequest();//设置验证器 √
        $this->createRoute(); //路由植入 √
        $this->createForeEndApiJs();//创建前端接口js √
        $this->createViews();//创建视图  待完善
        $this->fillMenu();//填充菜单
        $this->fillPermission();//填充权限
        //自动更新权限表
        if ($this->option('permission') || $this->option('p')) {
            $this->insertPermission();
        }

        $this->info('success');
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
//        $this->info('model relation complete');
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

    //创建前端js
    protected function createForeEndApiJs()
    {
        $vue_service_path = base_path('vue-cli/src/services');//vue页面路径
        if (!is_dir($vue_service_path)) return $this->info('vue-cli Service path non existent');//没有vue项目service目录

        //为api.js插入链接变量
        $api_path_val = strtoupper($this->vars['MODULE'] . '_' . $this->vars['MODEL']) . '_PATH';
        $api_relation_val = strtoupper($this->vars['MODULE'] . '_' . $this->vars['MODEL']) . '_RELATION_DATA';
        $arr[$api_path_val] = "`\${BASE_URL}/" . $this->vars['SMODULE'] . '/' . $this->vars['SMODEL'] . '`';
        $arr[$api_relation_val] = "`\${BASE_URL}/" . $this->vars['SMODULE'] . '/' . $this->vars['SMODEL'] . '_relation_data`';
        $api_url = base_path('vue-cli/src/services/api.js');
        $content = file_get_contents($api_url);
        $keys = '';
        foreach ($arr as $key => $item) {
            $keys .= $key . ',';
            if (!stristr($content, $key)) {
                $index = strrpos($content, '}');
                $content = substr($content, 0, $index);
                $content .= <<<str
    {$key}: $item,

str;
                $content .= "}";
            }
        }
        $keys = substr($keys, 0, strrpos($keys, ','));
        file_put_contents($api_url, $content);

        //插入js文件(模型)
        $url = base_path('vue-cli/src/services/' . $this->vars['SMODULE'] . '/' . $this->vars['SMODEL'] . '.js');
        if (!is_dir($url)) {//不存在js才生成插入
            $temp_url = base_path('vue-cli/src/services/' . $this->vars['SMODULE']);
            is_dir($temp_url) or mkdir($temp_url);
            touch($url, 0755, true);
            $this->setVar('IMPORT', $keys);
            $this->setVar('API_PATH', $api_path_val);
            $this->setVar('API_RELATION', $api_relation_val);
            $content = $this->replaceVars(__DIR__ . '/../Build/JavaScript/model_js.tpl');
            file_put_contents($url, $content);
        }
    }

    //创建视图
    protected function createViews()
    {
        $vue_page_path = base_path('vue-cli/src/pages');//vue页面路径
        $vue_page_module = base_path('vue-cli/src/pages/' . $this->vars['SMODULE']);//vue页面模块文件夹路径
        if (!is_dir($vue_page_path)) return $this->error('vue-cli Page path non existent');//没有vue项目页面目录
        is_dir($vue_page_module) or mkdir($vue_page_module, 0755, true);
        $vue_page_module_model = $vue_page_module . '/' . $this->vars['SMODEL'];//vue页面模型路径
        is_dir($vue_page_module_model) or mkdir($vue_page_module_model, 0755, true);
        $this->page_root = $vue_page_module_model;

        $this->createIndexVue();//创建前端列表页面
        $this->createIndexVueRoute();//创建前端列表页面的路由
    }

    //废弃保留
    protected function createViewsBak()
    {
        $dir = $this->vars['VIEW_PATH'];
        is_dir($dir) or mkdir($dir, 0755, true);
        $this->createIndexBlade();//创建PC显示页面
        $this->createMobileBlade();//创建手机显示页面
        $this->createCreateAndEditBlade();//创建PC编辑和修改页面
    }

    //创建route（植入）
    protected function createRoute()
    {
        if ($this->module) $file = $this->getVar('MODULE_PATH') . '/Http/routes.php';
        else  $file = 'routes/web.php';
        $route = file_get_contents($file);
//        if (strstr($route, "{$this->vars['SMODEL']}-route")) return;//已存在路由跳过
        $header = $this->vars['MODULE'] . ' Module';
        if ($this->module) {//模块下
            if (stristr($route, $header)) {
                //已存在组
                $row = "Route::resource('{$this->vars['SMODEL']}', '{$this->vars['MODEL']}Controller');";
                $row .= "\nRoute::get('{$this->vars['SMODEL']}_relation_data', '{$this->vars['MODEL']}Controller@relationData');";
                if (stristr($route, $row)) return;
                else {
                    $index = strpos($route, $header);
                    $index = strpos($route, 'function', $index);
                    $index = strpos($route, '}', $index);
                    $row .= "\n}";
                    $route = substr_replace($route, $row, $index, 1);
                    file_put_contents($file, $route);
                    $this->info('route create successfully');
                }

            } else {
                //未存在组
                $route .= <<<str
\n
//{$this->vars['MODULE']} Module
Route::group(['middleware' => ['api','admin_auth'],'prefix'=>'api/{$this->vars['SMODULE']}','namespace'=>"{$this->vars['NAMESPACE_HTTP']}Controllers"],
function () {
    Route::resource('{$this->vars['SMODEL']}', '{$this->vars['MODEL']}Controller');
    Route::get('{$this->vars['SMODEL']}_relation_data', '{$this->vars['MODEL']}Controller@relationData');
});
str;
                file_put_contents($file, $route);
                $this->info('route create successfully');
            }
        } else {
            //web下
            $this->info('Not supported at the moment');
        }

    }

    //创建控制器
    public function createController()
    {
        $file = $this->getVar('CONTROLLER_PATH') . $this->model . 'Controller.php';
        if (is_file($file)) {
            //控制器已存在
            $content = file_get_contents($file);
            // echo $file;
            if (!stristr($content, 'Display a listing of the resource')) {
                return false;
            }
        }

        $str = "";
        $this->setVar("STOREINSERT", $this->get_store_insert());//代办(图片上传)
        $this->setVar("UPDATEINSERT", $str);//代办
        $this->setVar("DELETEINSERT", $str);//代办
        $this->setVar('RELATIONINSERT', $this->relation_str());//关联数据查询


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
        $content = str_replace('{REQUEST_RULE_ATTRIBUTES}', var_export($this->getRequestRuleAttributes(), true), $content);
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
                //存在于模型的fill并且数据库要求是不为空
                $check2 = count($column['options']) <= 2 || count($column['options']) > 2 && $column['options'][1] !== 'user';
                if ($check2)//关联字段排除user填充
                {
                    $rules[$column['name']] = 'required';
                }
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
                $rules[$column['name'] . '.required'] = $column['title'] . ' 不能为空';
            }
        }

        return $rules;
    }

    public function getRequestRuleAttributes()
    {
        $columns = $this->formatColumns();
        $rules = [];
        foreach ($columns as $column) {
            $check = $column && in_array($column['name'], $this->modelInstance->getFillAble());
            if ($check && $column['nonull']) {
                $rules[$column['name']] = $column['title'];
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

    protected function relation_str()
    {
        $columns = $this->formatColumns();
        $str = "";
        foreach ($columns as $column) {
            if (isset($column['options']) && count($column['options']) > 2) {
                if ($column['options'][1] === 'select') {//选择框关联
                    $model = $column['options'][2]['select']['model'] . '';
                    $str .= "case '{$model}': \$arr = ['*'];break;";
                }
            }
        }
        return $str;
    }

    protected function get_store_insert()
    {
        $str = $this->relation_store_insert();
        return $str;
    }

    protected function relation_store_insert()
    {
        $columns = $this->formatColumns();
        $str = "";
        foreach ($columns as $column) {
            if (isset($column['options']) && count($column['options']) > 2) {
                if ($column['options'][1] === 'user') {//选择框关联
                    $model = $column['name'] . '';
                    $str .= <<<str
\$data['{$model}'] = \$request->attributes->get('user')['id'];\n
str;
                }
            }
        }
        return $str;
    }

    protected function fillMenu()
    {
        $url = base_path('Modules/' . ucfirst($this->module) . '/Config/menus.php');
        $content = file_get_contents($url);
        $content = str_replace('["title" => "网站配置", "permission" => "权限标识", "url" => "链接地址"],', '', $content);
        $page_path = '/pages/' . $this->vars['SMODULE'] . '/' . $this->vars['SMODEL'];
        $permission = "Modules\\" . $this->vars['MODULE'] . "\Http\Controllers\\" . $this->vars['MODEL'] . "Controller@index";
        $item = '
            ["title" => "' . $this->title . '列表", "permission" => "' . $permission . '", "url" => "' . $page_path . '"],';
        if (!stristr($content, $item)) {
            //没有记录插入
//            $this->info($item);
            $index = strpos($content, "return");
            $index = strpos($content, "menus", $index);
            $index = strpos($content, "[", $index);
            $content = substr($content, 0, $index + 1) . $item . substr($content, $index + 1);

            file_put_contents($url, $content);
        }
    }

    protected function fillPermission()
    {
        $url = base_path('Modules/' . ucfirst($this->module) . '/Config/permission.php');
        $content = file_get_contents($url);
        $content = str_replace("['title' => '添加栏目', 'name' => 'Modules\Admin\Http\Controllers\CategoryController@create', 'guard' => 'admin'],", '', $content);
        $content = str_replace('文章管理', $this->vars['MODULE'] . '管理', $content);
        $permission_root = "Modules\\" . $this->vars['MODULE'] . "\Http\Controllers\\" . $this->vars['MODEL'] . "Controller@";
        $title = $this->title;

        $item = "['title' => '" . $title . "列表', 'name' => '" . $permission_root . "index', 'guard' => 'web'],";

        if (!stristr($content, $item)) {
            //没有模型权限记录
            //插入换行符
            $item = '
            '.$item.'
            ';
            $item .= "
            ['title' => '添加" . $title . "', 'name' => '" . $permission_root . "create', 'guard' => 'web'],";
            $item .= "
            ['title' => '修改" . $title . "', 'name' => '" . $permission_root . "edit', 'guard' => 'web'],";
            $item .= "
            ['title' => '删除" . $title . "', 'name' => '" . $permission_root . "destory', 'guard' => 'web'],";

            $index = strpos($content, "return");
            $index = strpos($content, "permissions", $index);
            $index = strpos($content, "[", $index);
            $content = substr($content, 0, $index + 1) . $item . substr($content, $index + 1);
        }
        $module_permission ="['title' => '{$this->vars['MODULE']}模块访问权限', 'name' => 'Modules\\{$this->vars['MODULE']}', 'guard' => 'web'],";
        if (!stristr($content, $module_permission)) {
            //没有模块的权限记录
            $index = strpos($content, "return");
            $index = strpos($content, "permissions", $index);
            $index = strpos($content, "[", $index);
            //插入换行符
            $module_permission = '
            '.$module_permission.'
            ';
            $content = substr($content, 0, $index + 1) . $module_permission . substr($content, $index + 1);
        };
        file_put_contents($url, $content);
    }
}
