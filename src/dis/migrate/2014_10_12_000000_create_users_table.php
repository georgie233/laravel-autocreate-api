<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('账户名|input')->unique();
            $table->string('nick_name')->comment('昵称|input');
            $table->string('avatar')->comment('头像')->nullable();
            $table->string('address')->comment('地址')->nullable();
            $table->string('position')->comment('职位')->nullable();
            $table->string('mobile', 11)->comment('手机号')->nullable();
            $table->string('password')->comment('密码|input');
            $table->string('email')->comment('邮箱|input');
            $table->integer('gender')->default(0)->comment('性别|radio|0:保密,1:女,2:男');
            $table->integer('status')->default(1)->comment('状态|radio|0:无效,1:正常');

            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('admin_login_token', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('token', 500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('admin_login_token');
    }
}
