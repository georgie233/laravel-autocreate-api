<?php


namespace Georgie\AutoAPi\Traits;


use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

trait PermissionHelper
{
    //自动更新权限
    protected function insertPermission(){
        app()['cache']->forget('spatie.permission.cache');
        //循环所有模块
        $num = 0;
        foreach ((array)$this->getModules() as $module) {
            $config = \GModule::config($module.'.permission');
            foreach ((array)$config as $group) {
                foreach ((array)$group['permissions'] as $permission) {
                    if ( ! $this->permissionIsExists($permission)) {
                        $num++;
                        Permission::create(['name' => $permission['name'], 'guard_name' => $permission['guard']]);
                    }
                }
            }
        }
        $this->info("permission install success: ".$num.' new');
    }


    protected function getModules(): array
    {
        return array_keys(\Module::getOrdered());
    }

    protected function permissionIsExists(array $permission): bool
    {
        $where = [
            ['name', '=', $permission['name']],
            ['guard_name', '=', $permission['guard']],
        ];
        return (bool)Permission::where($where)->first();
    }
}
