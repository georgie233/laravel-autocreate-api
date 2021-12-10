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
            $table->string('name')->unique();
            $table->string('nick_name');
            $table->string('avatar')->comment('头像')->nullable();
            $table->string('address')->comment('地址')->nullable();
            $table->string('position')->comment('职位')->nullable();
            $table->string('phone', 11)->comment('手机号')->nullable();
            $table->string('phone_verification', 1)->default(0)->comment('手机号是否已验证');
            $table->string('password');
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
