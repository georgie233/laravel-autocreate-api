## laravel自动化构建

```
laravel低代码开发包 (完善中，慎用)
自动生成 vue前端 以及 api接口  后续会加入自动生成api文档
大致完成进度：70% (已经完成基础字段的增删改生成)
```
```
测试环境：
node: 14.15.1
npm 6.14.8
php 7.2.33
composer 2.1.11
```

 **温馨提示：如在开发过程出现了一些问题，请第一时间前往 "其他" 里寻找是否有相关的问题解决方案** 

### 关于

#### 环境要求

* PHP >= 7.1
* PDO PHP Extension

### 链接网站

* [Georgie/laravel-api-autocreate - gitee](https://gitee.com/georgie233/laravel-autocreate-api)
* [Georgie/vue-admin - gitee](https://gitee.com/georgie233/vue-admin)
* [Georgie/laravel-module - Packagist](https://packagist.org/packages/georgie/laravel-module)
* [Georgie/laravel-api-autocreate- Packagist](https://packagist.org/packages/georgie/laravel-api-autocreate)

#### 技术框架

* laravel5.7
* vue-cli3.0

### 使用流程

#### 安装laravel5.7框架

```
composer create-project laravel/laravel=5.7.* --prefer-dist {projectName}
```


打开项目目录 创建vue项目

```
cd {projectName}
```
```
git clone -b master https://gitee.com/georgie233/vue-admin.git vue-cli
```

#### 安装依赖

使用laravel5 请使用：
```
composer require georgie/laravel-module 1.0
```
```
composer require georgie/laravel-api-autocreate
```
```
//vue依赖
cd vue-cli
npm i
```

### 配置

```
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider"
```
```
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"
```
```
php artisan vendor:publish --provider="Georgie\Module\LaravelServiceProvider"
```

### 初始化


```
php artisan auto:init
```
```
php artisan g:module Admin
```

```
composer dumpautoload -o
```

```
php artisan auto:init
```

```
php artisan auto:auth
```
```
composer dump-autoloa
```
```
//记得配置 .env 文件  （配置数据库链接地址）
//如果mysql版本低于5.7 请前往配置AppServiceProvider文件  （参考 " 其他 > mysql版本太低执行迁移报错 "）

 php artisan migrate:fresh --seed
```

#### 运行环境

> 记得自行解决运行跨域问题 可以参考 “其他 > 跨域”

```
php -S 0.0.0.0:6363 -t ./public

cd vue-cli
npm run serve -- --port 8080
```

> 默认站长账号密码是 admin  123456
> 在userSeeder文件中修改

### 正式开发演示

#### 创建模块

> 创建书本模块

```
php artisan g:module Book
```

#### 创建模型

> 创建书籍模型

```
php artisan g:model Book Book
```

> 创建书籍分类模型

```
php artisan g:model Category Book
```

> 书写迁移文件 例：

```
// Modules/Book/Database/Migrations/yyyy_mm_dd_books_categories_table.php
Schema::create('books', function (Blueprint $table) {
    $table->increments('id');
    $table->timestamps();
    $table->string('title')->comment('标题|input');
    $table->text('content')->comment('内容|simditor');
    $table->string('thumb')->comment('缩略图|image');
    $table->integer('click')->comment('查看次数|input');
    $table->tinyInteger('iscommend')->default(1)->comment('推荐|radio|1:是,2:否');
    $table->integer('user_id')->comment('作者|user|&/user');
    $table->integer('categories_id')->comment('分类|select|&book/category');
});


// Modules/Book/Database/Migrations/yyyy_mm_dd_create_categories_table.php
Schema::create('categories', function (Blueprint $table) {
    $table->increments('id');
    $table->timestamps();
    $table->string('name')->comment('标题|input');
});
```

#### 执行自动化构建

> 参考 " 其他 > 目录结构 "  请确保vue-cli路径存在
> 温馨提示：一定要在生成相关表后再执行自动化构建
> 如果已经在生成表之前执行了自动化构建 之后出现问题 请参考 " 其他 > 自动化构建后页面没有字段数据 "

```
php artisan migrate
```

```
php artisan auto:api Category Book 分类
```

```
php artisan auto:api Book Book 书籍
```

> 注意：如果使用到了多表关联，需给后端配置查询字段
> 参考 " 其他 >  前端下拉框选项显示空白"

### 命令说明

##### auto:init

```
auto:init
```

> 初始化各种配置   
>
> 无参数

主要功能：

* 为composer.json 配置模块的自动加载 (autoload)
* 复制中间件文件：AdminAuthMiddleware 并注册为路由中间件：admin_auth

##### g:module 

```
g:module {name}
```

> 创建模块
>
> name：模块名称，建议大写开头

主要功能：

* 创建子模块 于 /Modules 下

##### g:model

```
g:model {modelName} {moduleName}
```

> 创建模型
>
> modelName：模型名称，建议大写开头
>
> moduleName：模块名称，需为已存在的模块名

主要功能：

* 创建模型以及迁移于已存在的模块下：/Modules/{moduleName}/Entities      /Modules/{moduleName}/Database

##### auto:auth

```
auto:auth
```

> 自动生成登录相关的文件
>
> 无参数

主要功能：

* 自动生成登录相关的文件 (迁移文件，模型，seeder，控制器) 并注册路由
* 自动配置为中国语言包
* AdminLoginController.php   控制器（业务逻辑）
* BaseApiController.php     控制器继承的基础控制器（封装一些常规功能）
* UserSeeder.php   用户数据库填充文件（生成站长账号）
* ResponseHelper.php   api返回组手函数
* AdminLogin.php   api_token模型文件（包括登录，注销等业务功能）
* User.php   用户模型文件

##### auto:api

```
auto:api {model} {module} {title} {--permission?} {--p?}
```

> 自动化构建
>
> model：已存在的模型名称 (要属于 module 模块以下)
>
> module：已存在的模块名称
>
> title：构建的默认 标题
>
> --permission：非必须参数  顺带执行  将权限配置文件插入权限表 
>
> --p：--permission的简写  效果同等

主要功能：

* 填充模型的illable
* 为模型插入模型关联方法
* 生成控制器 以及 模型验证器
* 插入路由
* 创建前端接口请求JS文件
* 创建前端页面
* 更新菜单配置文件
* 更新权限配置文件

### 前端相关说明

##### 文件配置
路由配置在：`/src/router/config.js`
页面生成在: `/src/pages/module/model`

##### 页面配置说明index.js

search 搜索显示数组参数说明
* title 显示的字段解释
* dataIndex 该字段在json数据中的字段名
* value 默认值
* condition 搜索条件 有：`=` `>` `>=` `<` `<=` `like`
* shrink 是否可被收缩

columns 列表显示数组参数说明
* title 显示的字段解释
* dataIndex 该字段在json数据中的字段名
* scopedSlots 注册为自定义插槽  `scopedSlots: {customRender: '插槽名称'}`  需自己实现插槽
* hideLabel 手机模式下是否隐藏标签

withArr with查询数组参数说明
* key 要关联查询到的参数key  如 with_store  则自动关联查询 store表
* get 要关联查询到的内容  如 `name,img` 查询两个字段

authorizeConfig 权限校验配置
用于校验权限

### 其他

##### mysql版本太低执行迁移报错

> app/Providers/AppServiceProvider.php

```
public function boot()
{
	\Illuminate\Support\Facades\Schema::defaultStringLength(191);
}
```

##### 执行Seeder报错

> 报错：Class * does not exis

```phpt
composer dump-autoloa
```

##### 跨域

> 创建中间件在 app/Http/Kernel.php 的$middleware 进行注册

```phpt
public function handle($request, Closure $next)
{
        $allow_origin = [
            'http://192.168.0.185:8080',
        ];
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (!empty($origin) && in_array($origin, $allow_origin))
            header('Access-Control-Allow-Origin: ' . $origin);
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type,Access-Token,Appid,Secret,Authorization,x-requested-with");
        header("Access-Control-Allow-Methods: GET,POST,OPTIONS,PUT,DELETE");
        header("Access-Control-Expose-Headers: *");
        return $next($request);
}
```
##### 前端刷新权限代码片段
```
info().then(res=>{
    const loginRes = res.data;
        if (loginRes.code === 200) {
        const {permissions} = loginRes.data
        this.setPermissions(permissions)
        this.$router.go(0);
    }
})
```

```
前端请避免用127.0.0.1 或者 localhost 打开（可能会导致跨域）
```

##### 前端下拉框选项显示空白
```
// 需在后端相关控制器的relationData 配置查询
case 'category': $arr = ['*']; break;
//更改为
case 'category': $arr = ['id','name','created_at']; break;
//数组第一个是 关联字段   第二个是前端下拉框选项显示的内容
```

##### 自动化构建后页面没有字段数据
```
清空 相关模型 $fillable 的值
删除 已经自动化构建的前端页面  ({model}List,{model}Add,{model}AddForm)
删除 vue-cli/src/router/config.js  相关页面注册
确保相关模型 的相关数据库迁移已经执行存在数据库
确保相关数据迁移的comment注释书写没有问题

重新执行自动化构建√
```

##### 目录结构

```
- app				laravel应用的核心代码
- config			laravel应用的配置文件
- database			laravel应用的应用的核心代码
- Modules			由laravel-module 提供的 子模块根目录
  - Api				子模块目录
- public			laravel应用的入口目录
- resources			laravel应用的视图文件以及未编译资源文件
- routes			laravel应用的路由器
- vue-cli			前端vue脚手架（自动化构建续保证路径以及名称存在）
```



