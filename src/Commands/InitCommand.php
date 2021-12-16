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
        $this->handleVue();
        $this->handleComposer();
    }

    protected function handleVue()
    {
//        try {
//            //初始化vue main.js cs
//            $u = __DIR__ . '/../resources/vue_cli/src/main.ts';
//            $url = base_path('vue-cli/src/main.ts');
//            $content = file_get_contents($u);
//            file_put_contents($url, $content);
//
//        } catch (\Exception $exception) {
//            $this->error($exception->getMessage());
//        }
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
}