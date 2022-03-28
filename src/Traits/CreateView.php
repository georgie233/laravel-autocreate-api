<?php

namespace Georgie\AutoAPi\Traits;

trait CreateView
{
    protected $page_root = '';

    protected function createIndexVue()
    {
        $this->setIndexVars();//设置index所需参数
        $this->createIndexJs();//创建index.js
        $this->createIndexSon();//创建index页面所需引入的子组件
        $content = $this->replaceVars(__DIR__ . '/../Build/views/List.vue');
        $file_path = $this->page_root . '/' . $this->vars['MODEL'] . 'List.vue';//列表组件
        file_put_contents($file_path, $content);

    }

    //创建index.js
    protected function createIndexJs()
    {
        $file_path = $this->page_root . '/index.js';
        $content = $this->replaceVars(__DIR__ . '/../Build/views/index.js');
        file_put_contents($file_path, $content);
    }

    protected function createIndexVueRoute()
    {
        $vue_router = base_path('vue-cli/src/router');//vue 路由页面路径
        if (!is_dir($vue_router)) return $this->error('vue-cli Router path non existent');//没有vue项目路由目录
        $config_file = $vue_router . '/' . 'config.js';
        $content = file_get_contents($config_file);

        $module_str = "module: '{$this->vars['SMODULE']}'";
        $model_str = "model: '{$this->vars['SMODEL']}'";
        if (stristr($content, $model_str)) return;//存在模型路由 退出
        if (stristr($content, $module_str)) {
            //已存在模块 插入子路由
            $index = stripos($content, $module_str);
            $index = stripos($content, 'children', $index);
            $index = stripos($content, '[', $index);
            $module_ = $this->vars['MODULE'];
            $model_ = $this->vars['MODEL'];
            $authority = 'Modules\\\\' . $module_ . '\\\\Http\\\\Controllers\\\\' . $model_ . 'Controller';
            $str = <<<str

                    {
                        path: '{$this->vars['SMODEL']}',
                        name: '{$this->title}列表',
                        model: '{$this->vars['SMODEL']}',
                        meta: {
                            authority: '{$authority}',
                        },
                        component: () => import('@/pages/{$this->vars['SMODULE']}/{$this->vars['SMODEL']}'),
                    },

str;
            $content = substr($content, 0, $index + 1) . $str . substr($content, $index + 1);
            file_put_contents($config_file, $content);
//            $this->info($content);
        } else {
            //不存在模块  插入模块路由
            $routeJson = $this->replaceVars(__DIR__ . '/../Build/router_config.tpl');
            $index = stripos($content, 'rootModules');
            $index = stripos($content, '[', $index);
            $content = substr($content, 0, $index + 1) . $routeJson . substr($content, $index + 1);
            // $this->info($content);
            $this->info('route module group insert complete');
            file_put_contents($config_file, $content);
            $this->createIndexVueRoute();//重新调用插入子路由

        }
    }


    /*设置Index.js 所需参数
     * SEARCH_ARR
     * COLUMNS_ARR
     */
    protected function setIndexVars()
    {
        $columns = $this->formatColumns();
        $arr = ['input', 'simditor'];//允许搜索的字段类型
        $select_str = "{title: '编号', dataIndex: 'search_id', value: '', condition: '=',shrink:false},";
        $columns_str = "{title: '编号', dataIndex: 'id'},";
        foreach ($columns as $column) {
            $check = $column;
            if ($check && count($column['options']) <= 2) {//排除关联字段
                if (in_array($column['options'][1], $arr)) {//属于允许搜索的字段
                    $select_str .= "\n\t{title: '{$column['options'][0]}', dataIndex: 'search_{$column['name']}', value: '', condition: 'like',shrink:true},";
                    $columns_str .= "\n\t{title: '{$column['options'][0]}', dataIndex: '{$column['name']}'},";
                }
            }
        }
        $columns_str .= "\n\t{title: '操作', scopedSlots: {customRender: 'action'}, hideLabel: true}";
        $this->setVar('SEARCH_ARR', $select_str);//设置搜索数组
        $this->setVar('COLUMNS_ARR', $columns_str);//设置显示字段数组
        $this->setVar('ADDFORMDATA','');//临时设置推荐表单的data
        $this->setVar('IMPORT','');//引入
        $this->setVar('COMPONENT','');//组件声明
        $this->setVar('TABLENAME',snake_case(str_plural($this->vars['SMODEL'])));
    }

    protected function createIndexSon()
    {
        //添加部分
        $content = $this->replaceVars(__DIR__ . '/../Build/views/ModelAdd.vue');//子组件：添加的模态框
        $file_path = $this->page_root . '/' . $this->vars['MODEL'] . 'Add.vue';
        if (!is_dir($file_path)) {//没有这个文件才会生成
            file_put_contents($file_path, $content);
        }

        $this->setFormRow();//设置表单row
        $form_path = $this->page_root . '/' . "Form";
        is_dir($form_path) or mkdir($form_path);
        $file_path = $this->page_root . '/Form/' . $this->vars['MODEL'] . 'AddForm.vue';//子组件：添加的表单
        if (!is_dir($file_path)) {//没有这个文件才会植入
            $content = $this->replaceVars(__DIR__ . '/../Build/views/ModelAddForm.vue');//子组件：表单
            file_put_contents($file_path, $content);
        }

        //修改部分
        $content = $this->replaceVars(__DIR__ . '/../Build/views/ModelEdit.vue');//子组件：修改的模态框
        $file_path = $this->page_root . '/' . $this->vars['MODEL'] . 'Edit.vue';
        if (!is_dir($file_path)) {//没有这个文件才会生成
            file_put_contents($file_path, $content);
        }

        $form_path = $this->page_root . '/' . "Form";
        is_dir($form_path) or mkdir($form_path);
        $file_path = $this->page_root . '/Form/' . $this->vars['MODEL'] . 'EditForm.vue';//子组件：添加的表单
        if (!is_dir($file_path)) {//没有这个文件才会植入
            $content = $this->replaceVars(__DIR__ . '/../Build/views/ModelEditForm.vue');//子组件：表单
            file_put_contents($file_path, $content);
        }
    }

    protected function setFormRow()
    {
        $this->setVar("SELECTDATA", '');
        $this->setVar("INITDATA", '');
        $this->setVar("METHODS", '');

        $COLUMNS = '';
        $select_data_str = '';//下拉框数组初始值
        $init_data_str = '';//initData()里面
        $init_data_fun_str = '';//方法
        $add_form_data = '';//添加表单内的数据
        foreach ($this->formatColumns() as $column) {
            if (isset($column['options']) && count($column['options']) >= 2) {
                $this->setVar("COLUMN['nonull']", $column['nonull']?'true':'false');
                $this->setVar("COLUMN['title']", $column['options'][0]);
                $this->setVar("COLUMN['name']", $column['name']);
                $this->setVar("COLUMN['type']", $column['type']);
                $this->setVar("COLUMN['formatType']", $column['formatType']);

                if($column['options'][1] == 'image'){
                    $add_form_data .= $column['name']."_url: '',";
                }
            }
            if (isset($column['options']) && count($column['options']) === 2) {
                //添加字段
                $type = $column['options'][1];//字段类型
                $type_extend = '';
                if ($column['formatType'] == 'number') $type_extend = '_number';
                $url = __DIR__ . '/../Build/forms/' . $type . $type_extend . '.tpl';
                $content = $this->replaceVars($url);//读取模板
                $COLUMNS .= "\n" . $content;
            } else if (isset($column['options']) && count($column['options']) > 2) {//处理关联关系
                $type = $column['options'][1];//字段类型
                if ($type !== 'user') {
                    //用户关联无需表单
                    $url = __DIR__ . '/../Build/forms/' . $type . '.tpl';
                    if ($type == 'radio') {
                        //radio
                        $html = "";
                        foreach ($column['options'][2] as $key => $item) {
                            $html .= "<a-radio :value=\"{$key}\">{$item}</a-radio>";
                        }
                        $this->setVar('FORM_HTML', $html);
                    } else if ($type == 'select') {
                        if (isset($column['options'][3])){
                            //有默认值  读取带默认值的模板
                            $url = __DIR__ . '/../Build/forms/select_defalut.tpl';
                            $def_arr = explode(':',$column['options'][3]);
                            $this->setVar('SELECT_DEFAULT_VALUE',$def_arr[0]);
                            $this->setVar('SELECT_DEFAULT_TEXT',$def_arr[1]);
                        }
                        $model = $column['options'][2]['select']['model'];
                        $this->setVar('OBJMODEL', $model);
                        $select_data_str .= "{$model}:false,";//植入初始值
                        $init_data_str .= "this.init_{$model}();\n";//植入initData()里面
                        $init_data_fun_str .= <<<str

init_{$model}() {
    let that = this;
    this.selectData['{$model}'] = null;
    {$this->vars['SMODEL']}RelationData('{$model}').then(res => {
        const res_data = res.data
        if (res_data.code === 200) {
            that.selectData['{$model}'] = res_data.data;
        } else this.\$message.error(res_data.message);
    })
},
str;

//                        $this->info($init_data_fun_str);
                    }
                    $this->setVar("SELECTDATA", $select_data_str);
                    $this->setVar("INITDATA", $init_data_str);
                    $this->setVar("METHODS", $init_data_fun_str);
                    $content = $this->replaceVars($url);//读取模板
                    $COLUMNS .= "\n" . $content;

                }
            }
        }
        if ($add_form_data){
            $this->setVar('IMPORT','import UploadAvatar from "@/components/georgie/uploadAvatar"');//引入
            $this->setVar('COMPONENT','UploadAvatar,');//组件声明

        }
        $this->setVar("ADDFORMDATA",$add_form_data);
//        $this->info($COLUMNS);
        $this->setVar('FORMROWS', $COLUMNS);//表单
    }
}
