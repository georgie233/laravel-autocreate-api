<?php
//Admin Module
Route::group(['middleware' => ['api', 'admin_auth'], 'prefix' => 'api/admin', 'namespace' => "Modules\Admin\Http\Controllers"],
    function () {
        Route::resource('user', 'UserController')->middleware('permission:web,resource');
        Route::get('user_relation_data', 'UserController@relationData')->middleware('permission:web,relation');
    });
