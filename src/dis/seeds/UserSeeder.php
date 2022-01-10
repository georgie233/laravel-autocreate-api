<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    protected $userData = [
        'name' => 'admin',
        'nick_name' => '超级管理员',
        'password' => '123456',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function __construct()
    {
        $this->userData['password'] = bcrypt($this->userData['password']);
    }

    public function run()
    {
        if (\Spatie\Permission\Models\Role::where('name', 'webmaster')->count() === 0)
            $role = \Spatie\Permission\Models\Role::create(['name' => 'webmaster', 'nick_name' => '站长']);
        if (\App\User::where('name', $this->userData['name'])->count() === 0){
            $user = \App\User::create($this->userData);
            $user->assignRole('webmaster');
        }
    }
}
