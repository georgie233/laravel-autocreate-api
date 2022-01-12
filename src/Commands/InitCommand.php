<?php


namespace Georgie\AutoAPi\Commands;


use Psy\Util\Str;

class InitCommand extends Base
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:init';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化各种配置';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $this->handleComposer();//处理composer.json
        $this->handMiddleware();//处理中间件
        $this->handleModule();//处理Admin模块
        $this->handleVue();//处理前端
    }

    protected function handleModule()
    {
        $url = base_path('Modules/Admin');
        if (!is_dir($url)) return $this->warn('no admin module');

        $test = "Admin Module";
        $route_url = base_path('Modules/Admin/Http/routes.php');
        if (stristr(file_get_contents($route_url), $test)) return;
        $url1 = base_path('Modules/Admin');
        $url2 = __DIR__ . '/../dis/Modules/Admin';
        $this->xCopy($url2, $url1, 1);
        $this->info('info admin module success');
    }

    protected function handleVue()
    {
        $url = base_path('Modules/Admin');
        if (!is_dir($url)) return $this->warn('you can execute \'php artisan g:module Admin\' before executing');
        $url =base_path('vue-cli/src/pages');
        if(!is_dir($url)) return $this->warn('no vue-cli folder');
//        if (is_dir(base_path('vue-cli/src/pages/admin/user/index.js')))return ;//已经存在前端
        $url1 = base_path('vue-cli');
        $url2 = __DIR__.'/../dis/vue-cli';
        $this->xCopy($url2, $url1, 1);
        //插入路由
        $rout_url = base_path('vue-cli/src/services/api.js');
        $content = file_get_contents($rout_url);
        if (!stristr($content,'ADMIN_USER_PATH')){
            $index = strrpos($content, '}');
            $content = substr($content, 0, $index);
            $content .= <<<str
    ADMIN_USER_PATH: `\${BASE_URL}/admin/user`,
    ADMIN_USER_RELATION_DATA: `\${BASE_URL}/admin/user_relation_data`,
str;
            $content .= "}";
            file_put_contents($rout_url,$content);
        }
        $this->info('info admin web success');
    }

    protected function handleComposer()
    {
        $url = base_path('composer.json');
        $str = <<<str
"app/",
            "Modules\\
str;
        $str2 = <<<str
"app/",
            "Modules\\\\": "Modules/"
str;
        $content = file_get_contents($url);
        if (stristr($content, $str) === false) {
            $content = str_replace('"app/"', $str2, $content);
            file_put_contents($url, $content);
        }
    }

    protected function handMiddleware()
    {
        //复制文件
        $url1 = __DIR__ . '/../dis/Middleware';
        $url2 = app_path('Http/Middleware');
        $this->xCopy($url1, $url2);

        //注册路由中间件
        $url = app_path('Http/Kernel.php');
        $content = file_get_contents($url);
        if (stristr($content, 'admin_auth') === false) {
            $str = "'admin_auth' => \App\Http\Middleware\AdminAuthMiddleware::class,";
            $str .= "
                'permission'=>\App\Http\Middleware\AdminPermissionMiddleware::class,";
            $content = str_replace('$routeMiddleware = [', '$routeMiddleware = [
                ' . $str, $content);
            file_put_contents($url, $content);
        }
    }
}
