<?php

namespace Georgie\AutoAPi\Commands;

use App\utils\ResponseHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuthCommand extends Base
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make auth api';

    protected $module;
    protected $name;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->copyMigrate();//复制迁移文件
        $this->copyModel();//复制模型
        $this->copySeeds();//复制seeder
        $this->copyController();//复制控制器

        $this->init();//初始化
        $this->initLang();//语言包
    }

    public function init()
    {
        //替换DatabaseSeeder
        $url = base_path('database/seeds/DatabaseSeeder.php');
        $content = file_get_contents($url);
        $content = str_replace('// $this->call(UsersTableSeeder::class);', '$this->call(UserSeeder::class);', $content);
        file_put_contents($url, $content);

        //替换api.php
        $url = base_path("routes/api.php");
        $content = file_get_contents($url);
        $str = 'Route::middleware(\'auth:api\')->get(\'/user\', function (Request $request) {
    return $request->user();
});';
        $str2 = <<<str
Route::group(['prefix' => 'admin'], function () {
    Route::post('login', 'AdminLoginController@login');
    Route::post('register', 'AdminLoginController@register');
    Route::post('checkToken', 'AdminLoginController@checkToken');
});
str;
        $content = str_replace($str, $str2, $content);
        file_put_contents($url, $content);



        $this->info("init success");
    }

    public function copyModel()
    {
        $a = __DIR__ . '/../dis/model';
        $b = base_path("app");
        $this->xCopy($a, $b);
    }

    public function copySeeds()
    {

    }

    public function copyController()
    {
        $a = __DIR__ . '/../dis/controllers';
        $b = base_path("app/Http/Controllers");
        $this->xCopy($a, $b);
    }

    public function copyMigrate()
    {
        $a = __DIR__ . '/../dis/migrate';
        $b = base_path("database/migrations");
        $this->xCopy($a, $b);
    }
    public function initLang(){
        //copy lang
        $a = __DIR__.'/../dis/lang';
        $b = base_path('resources/lang');
        $this->xCopy($a,$b,1);

        $url = base_path('config/app.php');
        $content = file_get_contents($url);
        $content = str_replace("'timezone' => 'UTC'","'timezone' => 'PRC'",$content);
        $content = str_replace("'locale' => 'en'","'locale' => 'zh-CN'",$content);
        file_put_contents($url,$content);
    }
}
